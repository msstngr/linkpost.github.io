<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Date;
use Altum\Middlewares\Authentication;
use Altum\Middlewares\Csrf;
use Altum\Response;
use Altum\Routing\Router;

class LinkAjax extends Controller {

    public function index() {

        /* Mail subscriber form submission check check */
        if($_POST['request_type'] == 'mail') {
            $this->mail();
        } else {
            Authentication::guard();
        }

        if(!empty($_POST) && (Csrf::check('token') || Csrf::check('global_token')) && isset($_POST['request_type'])) {

            switch($_POST['request_type']) {

                /* Status toggle */
                case 'is_enabled_toggle': $this->is_enabled_toggle(); break;

                /* Duplicate link */
                case 'duplicate': $this->duplicate(); break;

                /* Order links */
                case 'order': $this->order(); break;

                /* Create */
                case 'create': $this->create(); break;

                /* Update */
                case 'update': $this->update(); break;

                /* Delete */
                case 'delete': $this->delete(); break;

            }

        }

        die($_POST['request_type']);
    }

    private function is_enabled_toggle() {
        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Get the current status */
        $link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'is_enabled']);

        if($link) {
            $new_is_enabled = (int) !$link->is_enabled;

            db()->where('link_id', $link->link_id)->update('links', ['is_enabled' => $new_is_enabled]);

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
            \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $_POST['link_id']);

            Response::json('', 'success');
        }
    }

    private function duplicate() {
        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Get the link data */
        $link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->where('type', 'biolink')->where('subtype', 'link')->getOne('links');

        if($link) {
            $link->settings = json_decode($link->settings);

            $url = string_generate(10);
            $settings = json_encode([
                'name' => $link->settings->name,
                'image' => $link->settings->image,
                'text_color' => $link->settings->text_color,
                'background_color' => $link->settings->background_color,
                'outline' => $link->settings->outline,
                'border_radius' => $link->settings->border_radius,
                'animation' => $link->settings->animation,
                'icon' => $link->settings->icon
            ]);

            /* Generate random url if not specified */
            while(db()->where('url', $url)->getValue('links', 'link_id')) {
                $url = string_generate(10);
            }

            $stmt = database()->prepare("INSERT INTO `links` (`project_id`, `biolink_id`, `user_id`, `type`, `subtype`, `url`, `location_url`, `settings`, `start_date`, `end_date`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssssss', $link->project_id, $link->biolink_id, $this->user->user_id, $link->type, $link->subtype, $url, $link->location_url, $settings, $link->start_date, $link->end_date, \Altum\Date::$date);
            $stmt->execute();
            $stmt->close();

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

            Response::json('', 'success', ['url' => url('link/' . $link->biolink_id . '?tab=links')]);

        }
    }

    private function order() {

        if(isset($_POST['links']) && is_array($_POST['links'])) {
            foreach($_POST['links'] as $link) {
                $link['link_id'] = (int) $link['link_id'];
                $link['order'] = (int) $link['order'];

                /* Update the link order */
                db()->where('link_id', $link['link_id'])->where('user_id', $this->user->user_id)->update('links', ['order' => $link['order']]);

            }
        }

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success');
    }

    private function create() {
        $_POST['type'] = trim(Database::clean_string($_POST['type']));

        /* Check for possible errors */
        if(!in_array($_POST['type'], ['link', 'biolink'])) {
            die();
        }

        switch($_POST['type']) {
            case 'link':

                $this->create_link();

                break;

            case 'biolink':

                $biolink_blocks = require APP_PATH . 'includes/biolink_blocks.php';

                /* Check for subtype */
                if(isset($_POST['subtype']) && in_array($_POST['subtype'], $biolink_blocks)) {
                    $_POST['subtype'] = trim(Database::clean_string($_POST['subtype']));


                    if(in_array($_POST['subtype'], ['link', 'mail', 'rss_feed', 'custom_html', 'vcard', 'text', 'image', 'image_grid', 'divider'])) {
                        $this->{'create_biolink_' . $_POST['subtype']}();
                    } else {
                        $this->create_biolink_other($_POST['subtype']);
                    }

                } else {
                    /* Base biolink */
                    $this->create_biolink();
                }

                break;
        }

        die();
    }

    private function create_link() {
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url']), '-', false) : false;
        $_POST['sensitive_content'] = (bool) isset($_POST['sensitive_content']);

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Middlewares\Authentication::is_admin()) {
            die();
        }

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id'] ?? false);

        if(empty($_POST['location_url'])) {
            Response::json(language()->global->error_message->empty_fields, 'error');
        }

        $this->check_url($_POST['url']);

        $this->check_location_url($_POST['location_url']);

        /* Make sure that the user didn't exceed the limit */
        $user_total_links = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'link'")->fetch_object()->total;
        if($this->user->plan_settings->links_limit != -1 && $user_total_links >= $this->user->plan_settings->links_limit) {
            Response::json(language()->create_link_modal->error_message->links_limit, 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(language()->create_link_modal->error_message->url_exists, 'error');
            }

        }

        if(empty($errors)) {
            $url = $_POST['url'] ? $_POST['url'] : string_generate(10);
            $type = 'link';
            $subtype = '';
            $settings = json_encode([
                'password' => null,
                'sensitive_content' => false,
            ]);

            /* Generate random url if not specified */
            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = string_generate(10);
            }

            /* Insert to database */
            $link_id = db()->insert('links', [
                'user_id' => $this->user->user_id,
                'domain_id' => $domain_id,
                'type' => $type,
                'subtype' => $subtype,
                'url' => $url,
                'location_url' => $_POST['location_url'],
                'settings' => $settings,
                'date' => \Altum\Date::$date,
            ]);

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

            Response::json('', 'success', ['url' => url('link/' . $link_id)]);
        }
    }

    private function create_biolink() {
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url']), '-', false) : false;

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Middlewares\Authentication::is_admin()) {
            die();
        }

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id'] ?? false);

        /* Make sure that the user didn't exceed the limit */
        $user_total_biolinks = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'biolink' AND `subtype` = 'base'")->fetch_object()->total;
        if($this->user->plan_settings->biolinks_limit != -1 && $user_total_biolinks >= $this->user->plan_settings->biolinks_limit) {
            Response::json(language()->create_biolink_modal->error_message->biolinks_limit, 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(language()->create_biolink_modal->error_message->url_exists, 'error');
            }
        }

        /* Start the creation process */
        $url = $_POST['url'] ? $_POST['url'] : string_generate(10);
        $type = 'biolink';
        $subtype = 'base';
        $settings = json_encode([
            'title' => $_POST['url'],
            'description' => null,
            'display_verified' => false,
            'image' => '',
            'background_type' => 'preset',
            'background' => 'one',
            'text_color' => 'white',
            'socials_color' => 'white',
            'google_analytics' => '',
            'facebook_pixel' => '',
            'display_branding' => true,
            'branding' => [
                'url' => '',
                'name' => ''
            ],
            'seo' => [
                'block' => false,
                'title' => '',
                'meta_description' => '',
                'image' => '',
            ],
            'utm' => [
                'medium' => '',
                'source' => '',
            ],
            'socials' => [],
            'font' => null,
            'password' => null,
            'sensitive_content' => false,
            'leap_link' => null
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
            $url = string_generate(10);
        }

        $this->check_url($_POST['url']);

        /* Insert to database */
        $link_id = db()->insert('links', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Insert a first biolink link */
        $url = string_generate(10);
        $location_url = url();
        $type = 'biolink';
        $subtype = 'link';
        $settings = json_encode([
            'name' => $this->user->name,
            'text_color' => 'black',
            'background_color' => 'white',
            'outline' => false,
            'border_radius' => 'rounded',
            'animation' => false,
            'animation_runs' => 'repeat-1',
            'icon' => '',
            'image' => ''
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->getValue('links', 'link_id')) {
            $url = string_generate(10);
        }

        /* Insert */
        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $link_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $link_id)]);
    }

    private function create_biolink_link() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        $_POST['name'] = trim(Database::clean_string($_POST['name']));

        $this->check_location_url($_POST['location_url']);

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $url = string_generate(10);
        $type = 'biolink';
        $subtype = 'link';
        $settings = json_encode([
            'name' => $_POST['name'],
            'text_color' => 'black',
            'background_color' => 'white',
            'outline' => false,
            'border_radius' => 'rounded',
            'animation' => false,
            'animation_runs' => 'repeat-1',
            'icon' => '',
            'image' => '',
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->getValue('links', 'link_id')) {
            $url = string_generate(10);
        }

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_other($subtype) {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $this->check_location_url($_POST['location_url']);

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $url = '';
        $type = 'biolink';
        $settings = json_encode([]);

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_mail() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $url = $location_url = '';
        $type = 'biolink';
        $subtype = 'mail';
        $settings = json_encode([
            'name' => $_POST['name'],
            'image' => '',
            'text_color' => 'black',
            'background_color' => 'white',
            'outline' => false,
            'border_radius' => 'rounded',
            'animation' => false,
            'animation_runs' => 'repeat-1',
            'icon' => '',

            'email_placeholder' => language()->link->biolink->mail->email_placeholder_default,
            'button_text' => language()->link->biolink->mail->button_text_default,
            'success_text' => language()->link->biolink->mail->success_text_default,
            'show_agreement' => false,
            'agreement_url' => '',
            'agreement_text' => '',
            'mailchimp_api' => '',
            'mailchimp_api_list' => '',
            'webhook_url' => ''
        ]);

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_rss_feed() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $this->check_location_url($_POST['location_url']);

        $url = '';
        $type = 'biolink';
        $subtype = 'rss_feed';
        $settings = json_encode([
            'amount' => 5,
            'text_color' => 'black',
            'background_color' => 'white',
            'outline' => false,
            'border_radius' => 'rounded',
            'animation' => false,
            'animation_runs' => 'repeat-1',
        ]);

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_custom_html() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['html'] = trim($_POST['html']);

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $url = $location_url = '';
        $type = 'biolink';
        $subtype = 'custom_html';
        $settings = json_encode([
            'html' => $_POST['html']
        ]);

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_vcard() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $url = string_generate(10);
        $location_url = '';
        $type = 'biolink';
        $subtype = 'vcard';
        $settings = [
            'name' => $_POST['name'],
            'image' => '',
            'first_name' => '',
            'last_name' => '',
            'text_color' => 'black',
            'background_color' => 'white',
            'outline' => false,
            'border_radius' => 'rounded',
            'animation' => false,
            'animation_runs' => 'repeat-1',
            'icon' => '',
        ];
        foreach(['first_name', 'last_name', 'phone', 'street', 'city', 'zip', 'region', 'country', 'email', 'website', 'note'] as $key) {
            $settings[$key] = '';
        }
        $settings = json_encode($settings);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->getValue('links', 'link_id')) {
            $url = string_generate(10);
        }

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_text() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['title'] = trim(Database::clean_string($_POST['title']));
        $_POST['description'] = trim(Database::clean_string($_POST['description']));

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $url = $location_url = '';
        $type = 'biolink';
        $subtype = 'text';
        $settings = json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'title_text_color' => 'white',
            'description_text_color' => 'white',
        ]);

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_image() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $this->check_location_url($_POST['location_url'], true);

        /* Image upload */
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']);

        if(!$image) {
            Response::json(language()->global->error_message->empty_fields, 'error');
        }

        $image_file_extension = explode('.', $_FILES['image']['name']);
        $image_file_extension = strtolower(end($image_file_extension));
        $image_file_temp = $_FILES['image']['tmp_name'];

        if(!is_writable(UPLOADS_PATH . 'block_images/')) {
            Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_images/'), 'error');
        }

        if($_FILES['image']['error']) {
            Response::json(language()->global->error_message->file_upload, 'error');
        }

        if(!in_array($image_file_extension, $image_allowed_extensions)) {
            Response::json(language()->global->error_message->invalid_file_type, 'error');
        }

        if($_FILES['image']['size'] > settings()->links->image_size_limit * 1000000) {
            Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->image_size_limit), 'error');
        }

        /* Generate new name for the image */
        $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

        /* Upload the original */
        move_uploaded_file($image_file_temp, UPLOADS_PATH . 'block_images/' . $image_new_name);

        $url = string_generate(10);
        $type = 'biolink';
        $subtype = 'image';
        $settings = json_encode([
            'image' => $image_new_name,
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->getValue('links', 'link_id')) {
            $url = string_generate(10);
        }

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_image_grid() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $this->check_location_url($_POST['location_url'], true);

        /* Image upload */
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']);

        if(!$image) {
            Response::json(language()->global->error_message->empty_fields, 'error');
        }

        $image_file_extension = explode('.', $_FILES['image']['name']);
        $image_file_extension = strtolower(end($image_file_extension));
        $image_file_temp = $_FILES['image']['tmp_name'];

        if(!is_writable(UPLOADS_PATH . 'block_images/')) {
            Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_images/'), 'error');
        }

        if($_FILES['image']['error']) {
            Response::json(language()->global->error_message->file_upload, 'error');
        }

        if(!in_array($image_file_extension, $image_allowed_extensions)) {
            Response::json(language()->global->error_message->invalid_file_type, 'error');
        }

        if($_FILES['image']['size'] > settings()->links->image_size_limit * 1000000) {
            Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->image_size_limit), 'error');
        }

        /* Generate new name for the image */
        $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

        /* Upload the original */
        move_uploaded_file($image_file_temp, UPLOADS_PATH . 'block_images/' . $image_new_name);

        $url = string_generate(10);
        $type = 'biolink';
        $subtype = 'image_grid';
        $settings = json_encode([
            'name' => $_POST['name'],
            'image' => $image_new_name,
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->getValue('links', 'link_id')) {
            $url = string_generate(10);
        }

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function create_biolink_divider() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['margin_top'] = $_POST['margin_top'] > 7 || $_POST['margin_top'] < 0 ? 3 : (int) $_POST['margin_top'];
        $_POST['margin_bottom'] = $_POST['margin_bottom'] > 7 || $_POST['margin_bottom'] < 0 ? 3 : (int) $_POST['margin_bottom'];

        $project_id = db()->where('user_id', $this->user->user_id)->where('link_id', $_POST['link_id'])->getValue('links', 'project_id');

        $url = $location_url = '';
        $type = 'biolink';
        $subtype = 'divider';
        $settings = json_encode([
            'margin_top' => $_POST['margin_top'],
            'margin_bottom' => $_POST['margin_bottom'],
            'background_color' => 'white',
            'icon' => 'fa fa-infinity'
        ]);

        db()->insert('links', [
            'user_id' => $this->user->user_id,
            'biolink_id' => $_POST['link_id'],
            'project_id' => $project_id,
            'type' => $type,
            'subtype' => $subtype,
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings,
            'date' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);

        Response::json('', 'success', ['url' => url('link/' . $_POST['link_id'] . '?tab=links')]);
    }

    private function update() {

        if(!empty($_POST)) {
            $_POST['type'] = trim(Database::clean_string($_POST['type']));

            /* Check for possible errors */
            if(!in_array($_POST['type'], ['link', 'biolink'])) {
                die();
            }
            if(!Csrf::check()) {
                Response::json(language()->global->error_message->invalid_csrf_token, 'error');
            }

            switch($_POST['type']) {
                case 'link':

                    $this->update_link();

                    break;

                case 'biolink':

                    $biolink_blocks = require APP_PATH . 'includes/biolink_blocks.php';

                    /* Check for subtype */
                    if(isset($_POST['subtype']) && in_array($_POST['subtype'], $biolink_blocks)) {
                        $_POST['subtype'] = trim(Database::clean_string($_POST['subtype']));

                        if(in_array($_POST['subtype'], ['link', 'mail', 'rss_feed', 'custom_html', 'vcard', 'text', 'image', 'image_grid', 'divider'])) {
                            $this->{'update_biolink_' . $_POST['subtype']}();
                        } else {
                            $this->update_biolink_other($_POST['subtype']);
                        }


                    } else {
                        /* Base biolink */
                        $this->update_biolink();
                    }

                    break;
            }

        }

        die();
    }

    private function update_biolink() {
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']);
        $image_delete = isset($_POST['image_delete']) && $_POST['image_delete'] == 'true';
        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['title'] = Database::clean_string($_POST['title']);
        $_POST['description'] = Database::clean_string($_POST['description']);
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url']), '-', false) : false;

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Middlewares\Authentication::is_admin()) {
            die();
        }

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id'] ?? false);

        /* Check for any errors */
        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        if($_POST['project_id'] && !$project = db()->where('project_id', $_POST['project_id'])->where('user_id', $this->user->user_id)->getOne('projects', ['project_id'])) {
            die();
        }


        $link->settings = json_decode($link->settings);

        /* Check for any errors on the image */
        if($image) {
            $image_file_extension = explode('.', $_FILES['image']['name']);
            $image_file_extension = strtolower(end($image_file_extension));
            $image_file_temp = $_FILES['image']['tmp_name'];

            if($_FILES['image']['error']) {
                Response::json(language()->global->error_message->file_upload, 'error');
            }

            if(!in_array($image_file_extension, $image_allowed_extensions)) {
                Response::json(language()->global->error_message->invalid_file_type, 'error');
            }

            if($_FILES['image']['size'] > settings()->links->avatar_size_limit * 1000000) {
                Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->avatar_size_limit), 'error');
            }
        }

        if($_POST['url'] == $link->url) {
            $url = $link->url;

            if($link->domain_id != $domain_id) {
                if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                    Response::json(language()->create_biolink_modal->error_message->url_exists, 'error');
                }
            }

        } else {
            $url = $_POST['url'] ? $_POST['url'] : string_generate(10);

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(language()->create_biolink_modal->error_message->url_exists, 'error');
            }

            /* Generate random url if not specified */
            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = string_generate(10);
            }

            $this->check_url($_POST['url']);
        }

        /* Update the avatar of the profile if needed */
        if($image && !$image_delete) {

            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'avatars/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'avatars/' . $link->settings->image);
            }

            /* Generate new name for logo */
            $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

            /* Upload the original */
            move_uploaded_file($image_file_temp, UPLOADS_PATH . 'avatars/' . $image_new_name);

        }

        /* Delete avatar */
        if($image_delete) {
            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'avatars/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'avatars/' . $link->settings->image);
            }
        }

        /* Image upload */
        $seo_image_allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $seo_image = (bool) !empty($_FILES['seo_image']['name']) && !isset($_POST['seo_image_remove']);
        $db_seo_image = $link->settings->seo->image;

        if($seo_image) {
            $seo_image_file_extension = explode('.', $_FILES['seo_image']['name']);
            $seo_image_file_extension = strtolower(end($seo_image_file_extension));
            $seo_image_file_temp = $_FILES['seo_image']['tmp_name'];

            if(!is_writable(UPLOADS_PATH . 'block_images/')) {
                Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_images/'), 'error');
            }

            if($_FILES['seo_image']['error']) {
                Response::json(language()->global->error_message->file_upload, 'error');
            }

            if(!in_array($seo_image_file_extension, $seo_image_allowed_extensions)) {
                Response::json(language()->global->error_message->invalid_file_type, 'error');
            }

            if($_FILES['seo_image']['size'] > settings()->links->image_size_limit * 1000000) {
                Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->image_size_limit), 'error');
            }

            /* Delete current image */
            if(!empty($link->settings->seo->image) && file_exists(UPLOADS_PATH . 'block_images/' . $link->settings->seo->image)) {
                unlink(UPLOADS_PATH . 'block_images/' . $link->settings->seo->image);
            }

            /* Generate new name for the image */
            $seo_image_new_name = md5(time() . rand()) . '.' . $seo_image_file_extension;

            /* Upload the original */
            move_uploaded_file($seo_image_file_temp, UPLOADS_PATH . 'block_images/' . $seo_image_new_name);

            $db_seo_image = $seo_image_new_name;
        }

        /* Check for the removal of the already uploaded file */
        if(isset($_POST['seo_image_remove'])) {
            /* Delete current file */
            if(!empty($link->settings->seo->image) && file_exists(UPLOADS_PATH . 'block_images/' . $link->settings->seo->image)) {
                unlink(UPLOADS_PATH . 'block_images/' . $link->settings->seo->image);
            }
            $db_seo_image = null;
        }

        $seo_image_url = $db_seo_image ? SITE_URL . UPLOADS_URL_PATH . 'block_images/' . $db_seo_image : null;

        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#fff' : $_POST['text_color'];
        $_POST['socials_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['socials_color']) ? '#fff' : $_POST['socials_color'];
        $biolink_backgrounds = require APP_PATH . 'includes/biolink_backgrounds.php';
        $_POST['background_type'] = array_key_exists($_POST['background_type'], $biolink_backgrounds) ? $_POST['background_type'] : 'preset';
        $background = 'one';

        switch($_POST['background_type']) {
            case 'preset':
                $background = in_array($_POST['background'], $biolink_backgrounds['preset']) ? $_POST['background'] : 'one';
                break;

            case 'color':

                $background = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background']) ? '#000' : $_POST['background'];

                break;

            case 'gradient':

                $color_one = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background'][0]) ? '#000' : $_POST['background'][0];
                $color_two = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background'][1]) ? '#000' : $_POST['background'][1];

                $background = [
                    'color_one' => $color_one,
                    'color_two' => $color_two
                ];

                break;

            case 'image':

                $background = (bool) !empty($_FILES['background']['name']);

                /* Check for any errors on the logo image */
                if($background) {
                    $background_file_extension = explode('.', $_FILES['background']['name']);
                    $background_file_extension = strtolower(end($background_file_extension));
                    $background_file_temp = $_FILES['background']['tmp_name'];

                    if($_FILES['background']['error']) {
                        Response::json(language()->global->error_message->file_upload, 'error');
                    }

                    if(!in_array($background_file_extension, $image_allowed_extensions)) {
                        Response::json(language()->global->error_message->invalid_file_type, 'error');
                    }

                    if($_FILES['background']['size'] > settings()->links->background_size_limit * 1000000) {
                        Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->background_size_limit), 'error');
                    }

                    /* Delete current image */
                    if(is_string($link->settings->background) && !empty($link->settings->background) && file_exists(UPLOADS_PATH . 'backgrounds/' . $link->settings->background)) {
                        unlink(UPLOADS_PATH . 'backgrounds/' . $link->settings->background);
                    }

                    /* Generate new name for logo */
                    $background_new_name = md5(time() . rand()) . '.' . $background_file_extension;

                    /* Upload the original */
                    move_uploaded_file($background_file_temp, UPLOADS_PATH . 'backgrounds/' . $background_new_name);

                    $background = $background_new_name;
                }

                break;
        }

        $_POST['display_branding'] = (bool) isset($_POST['display_branding']);
        $_POST['display_verified'] = (bool) isset($_POST['display_verified']);
        $_POST['branding_name'] = trim(Database::clean_string($_POST['branding_name']));
        $_POST['branding_url'] = trim(Database::clean_string($_POST['branding_url']));
        $_POST['google_analytics'] = trim(Database::clean_string($_POST['google_analytics']));
        $_POST['facebook_pixel'] = trim(Database::clean_string($_POST['facebook_pixel']));
        $_POST['seo_block'] = (bool) isset($_POST['seo_block']);
        $_POST['seo_title'] = trim(Database::clean_string(mb_substr($_POST['seo_title'], 0, 70)));
        $_POST['seo_meta_description'] = trim(Database::clean_string(mb_substr($_POST['seo_meta_description'], 0, 160)));
        $_POST['utm_medium'] = trim(Database::clean_string($_POST['utm_medium']));
        $_POST['utm_source'] = trim(Database::clean_string($_POST['utm_source']));
        $_POST['password'] = !empty($_POST['qweasdzxc']) ?
            ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;
        $_POST['sensitive_content'] = (bool) isset($_POST['sensitive_content']);
        $_POST['leap_link'] = trim(Database::clean_string($_POST['leap_link'] ?? null));
        $this->check_location_url($_POST['leap_link'], true);

        /* Make sure the socials sent are proper */
        $biolink_socials = require APP_PATH . 'includes/biolink_socials.php';

        foreach($_POST['socials'] as $key => $value) {

            if(!array_key_exists($key, $biolink_socials)) {
                unset($_POST['socials'][$key]);
            } else {
                $_POST['socials'][$key] = Database::clean_string($_POST['socials'][$key]);
            }

        }

        /* Make sure the font is ok */
        $biolink_fonts = require APP_PATH . 'includes/biolink_fonts.php';
        $_POST['font'] = !array_key_exists($_POST['font'], $biolink_fonts) ? false : Database::clean_string($_POST['font']);

        /* Set the new settings variable */
        $settings = json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'display_verified' => $_POST['display_verified'],
            'image' => $image_delete ? '' : ($image ? $image_new_name : $link->settings->image),
            'background_type' => $_POST['background_type'],
            'background' => $background ? $background : $link->settings->background,
            'text_color' => $_POST['text_color'],
            'socials_color' => $_POST['socials_color'],
            'google_analytics' => $_POST['google_analytics'],
            'facebook_pixel' => $_POST['facebook_pixel'],
            'display_branding' => $_POST['display_branding'],
            'branding' => [
                'name' => $_POST['branding_name'],
                'url' => $_POST['branding_url'],
            ],
            'seo' => [
                'block' => $_POST['seo_block'],
                'title' => $_POST['seo_title'],
                'meta_description' => $_POST['seo_meta_description'],
                'image' => $db_seo_image,
            ],
            'utm' => [
                'medium' => $_POST['utm_medium'],
                'source' => $_POST['utm_source'],
            ],
            'socials' => $_POST['socials'],
            'font' => $_POST['font'],
            'password' => $_POST['password'],
            'sensitive_content' => $_POST['sensitive_content'],
            'leap_link' => $_POST['leap_link'],
        ]);

        /* Update the record */
        db()->where('link_id', $link->link_id)->update('links', [
            'project_id' => $_POST['project_id'],
            'domain_id' => $domain_id,
            'url' => $url,
            'settings' => $settings,
        ]);

        /* Update the biolink page blocks if needed */
        if($link->project_id != $_POST['project_id']) {
            db()->where('biolink_id', $link->link_id)->update('links', ['project_id' => $_POST['project_id']]);
        }

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success', ['image_prop' => true, 'seo_image_url' => $seo_image_url]);

    }

    private function update_biolink_link() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['outline'] = (bool) isset($_POST['outline']);
        $_POST['border_radius'] = in_array($_POST['border_radius'], ['straight', 'round', 'rounded']) ? Database::clean_string($_POST['border_radius']) : 'rounded';
        $_POST['animation'] = in_array($_POST['animation'], require APP_PATH . 'includes/biolink_animations.php') || $_POST['animation'] == 'false' ? Database::clean_string($_POST['animation']) : false;
        $_POST['animation_runs'] = in_array($_POST['animation_runs'], ['repeat-1', 'repeat-2', 'repeat-3', 'infinite']) ? Database::clean_string($_POST['animation_runs']) : false;
        $_POST['icon'] = trim(Database::clean_string($_POST['icon']));
        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#000' : $_POST['text_color'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];
        if(isset($_POST['schedule']) && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }

        /* Check for any errors */
        $required_fields = ['location_url', 'name'];

        /* Check for any errors */
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]))) {
                Response::json(language()->global->error_message->empty_fields, 'error');
                break 1;
            }
        }

        $this->check_location_url($_POST['location_url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }
        $link->settings = json_decode($link->settings);

        /* Image upload */
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']) && !isset($_POST['image_remove']);
        $db_image = $link->settings->image;

        if($image) {
            $image_file_extension = explode('.', $_FILES['image']['name']);
            $image_file_extension = strtolower(end($image_file_extension));
            $image_file_temp = $_FILES['image']['tmp_name'];

            if(!is_writable(UPLOADS_PATH . 'block_thumbnail_images/')) {
                Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_thumbnail_images/'), 'error');
            }

            if($_FILES['image']['error']) {
                Response::json(language()->global->error_message->file_upload, 'error');
            }

            if(!in_array($image_file_extension, $image_allowed_extensions)) {
                Response::json(language()->global->error_message->invalid_file_type, 'error');
            }

            if($_FILES['image']['size'] > settings()->links->thumbnail_image_size_limit * 1000000) {
                Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->thumbnail_image_size_limit), 'error');
            }

            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image);
            }

            /* Generate new name for the image */
            $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

            /* Upload the original */
            move_uploaded_file($image_file_temp, UPLOADS_PATH . 'block_thumbnail_images/' . $image_new_name);

            $db_image = $image_new_name;
        }

        /* Check for the removal of the already uploaded file */
        if(isset($_POST['image_remove'])) {
            /* Delete current file */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image);
            }
            $db_image = null;
        }

        $image_url = $db_image ? SITE_URL . UPLOADS_URL_PATH . 'block_thumbnail_images/' . $db_image : null;

        $settings = json_encode([
            'name' => $_POST['name'],
            'text_color' => $_POST['text_color'],
            'background_color' => $_POST['background_color'],
            'outline' => $_POST['outline'],
            'border_radius' => $_POST['border_radius'],
            'animation' => $_POST['animation'],
            'animation_runs' => $_POST['animation_runs'],
            'icon' => $_POST['icon'],
            'image' => $db_image,
        ]);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success', ['image_prop' => true, 'image_url' => $image_url]);
    }

    private function update_biolink_other($subtype) {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        $url = '';

        $this->check_location_url($_POST['location_url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        $stmt = database()->prepare("UPDATE `links` SET `url` = ?, `location_url` = ? WHERE `link_id` = ?");
        $stmt->bind_param('sss', $url, $_POST['location_url'], $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success');
    }

    private function update_biolink_mail() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url']), '-', false) : false;
        $_POST['outline'] = (bool) isset($_POST['outline']);
        $_POST['border_radius'] = in_array($_POST['border_radius'], ['straight', 'round', 'rounded']) ? Database::clean_string($_POST['border_radius']) : 'rounded';
        $_POST['animation'] = in_array($_POST['animation'], require APP_PATH . 'includes/biolink_animations.php') || $_POST['animation'] == 'false' ? Database::clean_string($_POST['animation']) : false;
        $_POST['animation_runs'] = in_array($_POST['animation_runs'], ['repeat-1', 'repeat-2', 'repeat-3', 'infinite']) ? Database::clean_string($_POST['animation_runs']) : false;
        $_POST['icon'] = trim(Database::clean_string($_POST['icon']));
        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#000' : $_POST['text_color'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];

        $_POST['email_placeholder'] = trim(Database::clean_string($_POST['email_placeholder']));
        $_POST['button_text'] = trim(Database::clean_string($_POST['button_text']));
        $_POST['success_text'] = trim(Database::clean_string($_POST['success_text']));
        $_POST['show_agreement'] = (bool) isset($_POST['show_agreement']);
        $_POST['agreement_url'] = trim(Database::clean_string($_POST['agreement_url']));
        $_POST['agreement_text'] = trim(Database::clean_string($_POST['agreement_text']));
        $_POST['mailchimp_api'] = trim(Database::clean_string($_POST['mailchimp_api']));
        $_POST['mailchimp_api_list'] = trim(Database::clean_string($_POST['mailchimp_api_list']));
        $_POST['webhook_url'] = trim(Database::clean_string($_POST['webhook_url']));

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }
        $link->settings = json_decode($link->settings);

        /* Image upload */
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']) && !isset($_POST['image_remove']);
        $db_image = $link->settings->image;

        if($image) {
            $image_file_extension = explode('.', $_FILES['image']['name']);
            $image_file_extension = strtolower(end($image_file_extension));
            $image_file_temp = $_FILES['image']['tmp_name'];

            if(!is_writable(UPLOADS_PATH . 'block_thumbnail_images/')) {
                Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_thumbnail_images/'), 'error');
            }

            if($_FILES['image']['error']) {
                Response::json(language()->global->error_message->file_upload, 'error');
            }

            if(!in_array($image_file_extension, $image_allowed_extensions)) {
                Response::json(language()->global->error_message->invalid_file_type, 'error');
            }

            if($_FILES['image']['size'] > settings()->links->thumbnail_image_size_limit * 1000000) {
                Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->thumbnail_image_size_limit), 'error');
            }

            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image);
            }

            /* Generate new name for the image */
            $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

            /* Upload the original */
            move_uploaded_file($image_file_temp, UPLOADS_PATH . 'block_thumbnail_images/' . $image_new_name);

            $db_image = $image_new_name;
        }

        /* Check for the removal of the already uploaded file */
        if(isset($_POST['image_remove'])) {
            /* Delete current file */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image);
            }
            $db_image = null;
        }

        $image_url = $db_image ? SITE_URL . UPLOADS_URL_PATH . 'block_thumbnail_images/' . $db_image : null;

        $settings = json_encode([
            'name' => $_POST['name'],
            'image' => $db_image,
            'text_color' => $_POST['text_color'],
            'background_color' => $_POST['background_color'],
            'outline' => $_POST['outline'],
            'border_radius' => $_POST['border_radius'],
            'animation' => $_POST['animation'],
            'animation_runs' => $_POST['animation_runs'],
            'icon' => $_POST['icon'],

            'email_placeholder' => $_POST['email_placeholder'],
            'button_text' => $_POST['button_text'],
            'success_text' => $_POST['success_text'],
            'show_agreement' => $_POST['show_agreement'],
            'agreement_url' => $_POST['agreement_url'],
            'agreement_text' => $_POST['agreement_text'],
            'mailchimp_api' => $_POST['mailchimp_api'],
            'mailchimp_api_list' => $_POST['mailchimp_api_list'],
            'webhook_url' => $_POST['webhook_url']
        ]);

        db()->where('link_id', $_POST['link_id'])->update('links', ['settings' => $settings]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success', ['image_prop' => true, 'image_url' => $image_url]);
    }

    private function update_biolink_rss_feed() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        $url = '';
        $_POST['amount'] = (int) Database::clean_string($_POST['amount']);
        $_POST['outline'] = (bool) isset($_POST['outline']);
        $_POST['border_radius'] = in_array($_POST['border_radius'], ['straight', 'round', 'rounded']) ? Database::clean_string($_POST['border_radius']) : 'rounded';
        $_POST['animation'] = in_array($_POST['animation'], require APP_PATH . 'includes/biolink_animations.php') || $_POST['animation'] == 'false' ? Database::clean_string($_POST['animation']) : false;
        $_POST['animation_runs'] = in_array($_POST['animation_runs'], ['repeat-1', 'repeat-2', 'repeat-3', 'infinite']) ? Database::clean_string($_POST['animation_runs']) : false;
        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#000' : $_POST['text_color'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];

        $this->check_location_url($_POST['location_url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        $settings = json_encode([
            'amount' => $_POST['amount'],
            'text_color' => $_POST['text_color'],
            'background_color' => $_POST['background_color'],
            'outline' => $_POST['outline'],
            'border_radius' => $_POST['border_radius'],
            'animation' => $_POST['animation'],
            'animation_runs' => $_POST['animation_runs'],
        ]);

        $stmt = database()->prepare("UPDATE `links` SET `url` = ?, `location_url` = ?, `settings` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ssss', $url, $_POST['location_url'], $settings, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success');
    }

    private function update_biolink_custom_html() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['html'] = trim($_POST['html']);
        $url = $location_url = '';

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        $settings = json_encode([
            'html' => $_POST['html'],
        ]);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success');
    }

    private function update_biolink_vcard() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url']), '-', false) : false;
        $location_url = '';
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['image'] = trim(Database::clean_string($_POST['image']));
        $_POST['outline'] = (bool) isset($_POST['outline']);
        $_POST['border_radius'] = in_array($_POST['border_radius'], ['straight', 'round', 'rounded']) ? Database::clean_string($_POST['border_radius']) : 'rounded';
        $_POST['animation'] = in_array($_POST['animation'], require APP_PATH . 'includes/biolink_animations.php') || $_POST['animation'] == 'false' ? Database::clean_string($_POST['animation']) : false;
        $_POST['animation_runs'] = in_array($_POST['animation_runs'], ['repeat-1', 'repeat-2', 'repeat-3', 'infinite']) ? Database::clean_string($_POST['animation_runs']) : false;
        $_POST['text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['text_color']) ? '#000' : $_POST['text_color'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];
        $_POST['icon'] = trim(Database::clean_string($_POST['icon']));
        foreach(['first_name', 'last_name', 'phone', 'street', 'city', 'zip', 'region', 'country', 'email', 'website', 'company', 'note'] as $key) {
            $_POST[$key] = trim(Database::clean_string($_POST[$key]));
        }

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }
        $link->settings = json_decode($link->settings);

        /* URL */
        $this->check_url($_POST['url']);

        $url = $_POST['url'];

        if(empty($_POST['url'])) {
            /* Generate random url if not specified */
            $url = string_generate(10);

            while(db()->where('url', $url)->getValue('links', 'link_id')) {
                $url = string_generate(10);
            }
        }

        /* Image upload */
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']) && !isset($_POST['image_remove']);
        $db_image = $link->settings->image;

        if($image) {
            $image_file_extension = explode('.', $_FILES['image']['name']);
            $image_file_extension = strtolower(end($image_file_extension));
            $image_file_temp = $_FILES['image']['tmp_name'];

            if(!is_writable(UPLOADS_PATH . 'block_thumbnail_images/')) {
                Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_thumbnail_images/'), 'error');
            }

            if($_FILES['image']['error']) {
                Response::json(language()->global->error_message->file_upload, 'error');
            }

            if(!in_array($image_file_extension, $image_allowed_extensions)) {
                Response::json(language()->global->error_message->invalid_file_type, 'error');
            }

            if($_FILES['image']['size'] > settings()->links->thumbnail_image_size_limit * 1000000) {
                Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->thumbnail_image_size_limit), 'error');
            }

            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image);
            }

            /* Generate new name for the image */
            $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

            /* Upload the original */
            move_uploaded_file($image_file_temp, UPLOADS_PATH . 'block_thumbnail_images/' . $image_new_name);

            $db_image = $image_new_name;
        }

        /* Check for the removal of the already uploaded file */
        if(isset($_POST['image_remove'])) {
            /* Delete current file */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image);
            }
            $db_image = null;
        }

        $image_url = $db_image ? SITE_URL . UPLOADS_URL_PATH . 'block_thumbnail_images/' . $db_image : null;

        $settings = [
            'name' => $_POST['name'],
            'image' => $db_image,
            'text_color' => $_POST['text_color'],
            'background_color' => $_POST['background_color'],
            'outline' => $_POST['outline'],
            'border_radius' => $_POST['border_radius'],
            'animation' => $_POST['animation'],
            'animation_runs' => $_POST['animation_runs'],
            'icon' => $_POST['icon'],
        ];

        foreach(['first_name', 'last_name', 'phone', 'street', 'city', 'zip', 'region', 'country', 'email', 'website', 'company', 'note'] as $key) {
            $settings[$key] = $_POST[$key];
        }

        $settings = json_encode($settings);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success', ['image_prop' => true, 'image_url' => $image_url]);
    }

    private function update_biolink_text() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['title'] = trim(Database::clean_string($_POST['title']));
        $_POST['description'] = trim(filter_var(strip_tags($_POST['description']), FILTER_SANITIZE_STRING));
        $_POST['title_text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['title_text_color']) ? '#fff' : $_POST['title_text_color'];
        $_POST['description_text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['description_text_color']) ? '#fff' : $_POST['description_text_color'];

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        $settings = json_encode([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'title_text_color' => $_POST['title_text_color'],
            'description_text_color' => $_POST['description_text_color'],
        ]);

        db()->where('link_id', $_POST['link_id'])->update('links', ['settings' => $settings]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success');
    }

    private function update_biolink_image() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }
        $link->settings = json_decode($link->settings);

        $this->check_location_url($_POST['location_url'], true);

        /* Image upload */
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']);
        $db_image = $link->settings->image;

        if($image) {
            $image_file_extension = explode('.', $_FILES['image']['name']);
            $image_file_extension = strtolower(end($image_file_extension));
            $image_file_temp = $_FILES['image']['tmp_name'];

            if(!is_writable(UPLOADS_PATH . 'block_images/')) {
                Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_images/'), 'error');
            }

            if($_FILES['image']['error']) {
                Response::json(language()->global->error_message->file_upload, 'error');
            }

            if(!in_array($image_file_extension, $image_allowed_extensions)) {
                Response::json(language()->global->error_message->invalid_file_type, 'error');
            }

            if($_FILES['image']['size'] > settings()->links->image_size_limit * 1000000) {
                Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->image_size_limit), 'error');
            }

            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_images/' . $link->settings->image);
            }

            /* Generate new name for the image */
            $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

            /* Upload the original */
            move_uploaded_file($image_file_temp, UPLOADS_PATH . 'block_images/' . $image_new_name);

            $db_image = $image_new_name;
        }

        $image_url = $db_image ? SITE_URL . UPLOADS_URL_PATH . 'block_images/' . $db_image : null;

        $settings = json_encode([
            'image' => $db_image,
        ]);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success', ['image_prop' => true, 'image_url' => $image_url]);
    }

    private function update_biolink_image_grid() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }
        $link->settings = json_decode($link->settings);

        $this->check_location_url($_POST['location_url'], true);

        /* Image upload */
        $image_allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'gif'];
        $image = (bool) !empty($_FILES['image']['name']);
        $db_image = $link->settings->image;

        if($image) {
            $image_file_extension = explode('.', $_FILES['image']['name']);
            $image_file_extension = strtolower(end($image_file_extension));
            $image_file_temp = $_FILES['image']['tmp_name'];

            if(!is_writable(UPLOADS_PATH . 'block_images/')) {
                Response::json(sprintf(language()->global->error_message->directory_not_writable, UPLOADS_PATH . 'block_images/'), 'error');
            }

            if($_FILES['image']['error']) {
                Response::json(language()->global->error_message->file_upload, 'error');
            }

            if(!in_array($image_file_extension, $image_allowed_extensions)) {
                Response::json(language()->global->error_message->invalid_file_type, 'error');
            }

            if($_FILES['image']['size'] > settings()->links->image_size_limit * 1000000) {
                Response::json(sprintf(language()->global->error_message->file_size_limit, settings()->links->image_size_limit), 'error');
            }

            /* Delete current image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_images/' . $link->settings->image);
            }

            /* Generate new name for the image */
            $image_new_name = md5(time() . rand()) . '.' . $image_file_extension;

            /* Upload the original */
            move_uploaded_file($image_file_temp, UPLOADS_PATH . 'block_images/' . $image_new_name);

            $db_image = $image_new_name;
        }

        $image_url = $db_image ? SITE_URL . UPLOADS_URL_PATH . 'block_images/' . $db_image : null;

        $settings = json_encode([
            'name' => $_POST['name'],
            'image' => $db_image,
        ]);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success', ['image_prop' => true, 'image_url' => $image_url]);
    }

    private function update_biolink_divider() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['margin_top'] = $_POST['margin_top'] > 7 || $_POST['margin_top'] < 0 ? 3 : (int) $_POST['margin_top'];
        $_POST['margin_bottom'] = $_POST['margin_bottom'] > 7 || $_POST['margin_bottom'] < 0 ? 3 : (int) $_POST['margin_bottom'];
        $_POST['background_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? '#fff' : $_POST['background_color'];
        $_POST['icon'] = trim(Database::clean_string($_POST['icon']));

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        $url = $location_url = '';

        $settings = json_encode([
            'margin_top' => $_POST['margin_top'],
            'margin_bottom' => $_POST['margin_bottom'],
            'background_color' => $_POST['background_color'],
            'icon' => $_POST['icon'],
        ]);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'url' => $url,
            'location_url' => $location_url,
            'settings' => $settings
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success');
    }

    private function update_link() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug(Database::clean_string($_POST['url']), '-', false) : false;
        $_POST['location_url'] = trim(Database::clean_string($_POST['location_url']));
        if(isset($_POST['schedule']) && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }

        $_POST['sensitive_content'] = (bool) isset($_POST['sensitive_content']);

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Middlewares\Authentication::is_admin()) {
            die();
        }

        /* Check if custom domain is set */
        $domain_id = $this->get_domain_id($_POST['domain_id'] ?? false);

        /* Check for any errors */
        $required_fields = ['location_url'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]))) {
                Response::json(language()->global->error_message->empty_fields, 'error');
                break 1;
            }
        }

        $this->check_url($_POST['url']);

        $this->check_location_url($_POST['location_url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        if($_POST['project_id'] && !$project = db()->where('project_id', $_POST['project_id'])->where('user_id', $this->user->user_id)->getOne('projects', ['project_id'])) {
            die();
        }

        /* Check for a password set */
        $_POST['password'] = !empty($_POST['qweasdzxc']) ?
            ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;


        /* Check for duplicate url if needed */
        if($_POST['url'] && ($_POST['url'] != $link->url || $domain_id != $link->domain_id)) {

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(language()->create_link_modal->error_message->url_exists, 'error');
            }

        }

        $url = $_POST['url'];

        if(empty($_POST['url'])) {
            /* Generate random url if not specified */
            $url = string_generate(10);

            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = string_generate(10);
            }

        }

        $settings = json_encode([
            'password' => $_POST['password'],
            'sensitive_content' => $_POST['sensitive_content'],
        ]);

        $stmt = database()->prepare("UPDATE `links` SET `project_id` = ?, `domain_id` = ?, `url` = ?, `location_url` = ?, `start_date` = ?, `end_date` = ?, `settings` = ? WHERE `link_id` = ?");
        $stmt->bind_param('ssssssss', $_POST['project_id'], $domain_id, $url, $_POST['location_url'], $_POST['start_date'], $_POST['end_date'], $settings, $_POST['link_id']);
        $stmt->execute();
        $stmt->close();

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $this->user->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

        Response::json(language()->link->success_message->settings_updated, 'success');
    }

    private function delete() {
        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Check for possible errors */
        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'biolink_id', 'type', 'subtype'])) {
            die();
        }

        (new \Altum\Models\Link())->delete($link->link_id);

        /* Determine where to redirect the user */
        $redirect_url = $link->type == 'biolink' && $link->subtype != 'base' ? url('link/' . $link->biolink_id . '?tab=links') : url('dashboard');

        Response::json('', 'success', ['url' => $redirect_url]);

    }

    private function mail() {
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['email'] = mb_substr(trim(Database::clean_string($_POST['email'])), 0, 320);

        /* Get the link data */
        $link = db()->where('link_id', $_POST['link_id'])->where('type', 'biolink')->where('subtype', 'mail')->getOne('links');

        if($link) {
            $link->settings = json_decode($link->settings);

            /* Send the webhook */
            if($link->settings->webhook_url) {

                $body = \Unirest\Request\Body::form(['email' => $_POST['email']]);

                $response = \Unirest\Request::post($link->settings->webhook_url, [], $body);

            }

            /* Send the email to mailchimp */
            if($link->settings->mailchimp_api && $link->settings->mailchimp_api_list) {

                /* Check the mailchimp api list and get data */
                $explode = explode('-', $link->settings->mailchimp_api);

                if(count($explode) < 2) {
                    die();
                }

                $dc = $explode[1];
                $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $link->settings->mailchimp_api_list . '/members';

                /* Try to subscribe the user to mailchimp list */
                \Unirest\Request::auth('altum', $link->settings->mailchimp_api);

                $body = \Unirest\Request\Body::json([
                    'email_address' => $_POST['email'],
                    'status' => 'subscribed',
                ]);

                \Unirest\Request::post(
                    $url,
                    [],
                    $body
                );

            }

            Response::json($link->settings->success_text, 'success');
        }
    }

    /* Function to bundle together all the checks of a custom url */
    private function check_url($url) {

        if($url) {
            /* Make sure the url alias is not blocked by a route of the product */
            if(array_key_exists($url, Router::$routes[''])) {
                Response::json(language()->link->error_message->blacklisted_url, 'error');
            }

            /* Make sure the custom url is not blacklisted */
            if(in_array(strtolower($url), explode(',', settings()->links->blacklisted_keywords))) {
                Response::json(language()->link->error_message->blacklisted_keyword, 'error');
            }

        }

    }

    /* Function to bundle together all the checks of an url */
    private function check_location_url($url, $can_be_empty = false) {

        if(empty(trim($url)) && $can_be_empty) {
            return;
        }

        if(empty(trim($url))) {
            Response::json(language()->global->error_message->empty_fields, 'error');
        }

        $url_details = parse_url($url);

        if(!isset($url_details['scheme'])) {
            Response::json(language()->link->error_message->invalid_location_url, 'error');
        }

        if(!$this->user->plan_settings->deep_links && !in_array($url_details['scheme'], ['http', 'https'])) {
            Response::json(language()->link->error_message->invalid_location_url, 'error');
        }

        /* Make sure the domain is not blacklisted */
        if(in_array(strtolower(get_domain($url)), explode(',', settings()->links->blacklisted_domains))) {
            Response::json(language()->link->error_message->blacklisted_domain, 'error');
        }

        /* Check the url with phishtank to make sure its not a phishing site */
        if(settings()->links->phishtank_is_enabled) {
            if(phishtank_check($url, settings()->links->phishtank_api_key)) {
                Response::json(language()->link->error_message->blacklisted_location_url, 'error');
            }
        }

        /* Check the url with google safe browsing to make sure it is a safe website */
        if(settings()->links->google_safe_browsing_is_enabled) {
            if(google_safe_browsing_check($url, settings()->links->google_safe_browsing_api_key)) {
                Response::json(language()->link->error_message->blacklisted_location_url, 'error');
            }
        }
    }

    /* Check if custom domain is set and return the proper value */
    private function get_domain_id($posted_domain_id) {

        $domain_id = 0;

        if(isset($posted_domain_id)) {
            $domain_id = (int) Database::clean_string($posted_domain_id);

            /* Make sure the user has access to global additional domains */
            if($this->user->plan_settings->additional_global_domains) {
                $domain_id = database()->query("SELECT `domain_id` FROM `domains` WHERE `domain_id` = {$domain_id} AND (`user_id` = {$this->user->user_id} OR `type` = 1)")->fetch_object()->domain_id ?? 0;
            } else {
                $domain_id = database()->query("SELECT `domain_id` FROM `domains` WHERE `domain_id` = {$domain_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->domain_id ?? 0;
            }

        }

        return $domain_id;
    }
}
