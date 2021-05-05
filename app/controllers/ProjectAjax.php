<?php

namespace Altum\Controllers;

use Altum\Database\Database;
use Altum\Date;
use Altum\Middlewares\Authentication;
use Altum\Middlewares\Csrf;
use Altum\Response;

class ProjectAjax extends Controller {

    public function index() {

        Authentication::guard();

        if(!empty($_POST) && (Csrf::check('token') || Csrf::check('global_token')) && isset($_POST['request_type'])) {

            switch($_POST['request_type']) {

                /* Create */
                case 'create': $this->create(); break;

                /* Update */
                case 'update': $this->update(); break;

                /* Delete */
                case 'delete': $this->delete(); break;

            }

        }

        die();
    }

    private function create() {
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['color']) ? '#000' : $_POST['color'];

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            $errors[] = language()->global->error_message->empty_fields;
        }

        /* Make sure that the user didn't exceed the limit */
        $user_total_projects = database()->query("SELECT COUNT(*) AS `total` FROM `projects` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total;
        if($this->user->plan_settings->projects_limit != -1 && $user_total_projects >= $this->user->plan_settings->projects_limit) {
            Response::json(language()->project_create_modal->error_message->projects_limit, 'error');
        }


        if(empty($errors)) {

            /* Insert to database */
            db()->insert('projects', [
                'user_id' => $this->user->user_id,
                'name' => $_POST['name'],
                'color' => $_POST['color'],
                'datetime' => Date::$date,
            ]);

            Response::json(language()->project_create_modal->success_message->created, 'success');

        }
    }

    private function update() {
        $_POST['project_id'] = (int) $_POST['project_id'];
        $_POST['name'] = trim(Database::clean_string($_POST['name']));
        $_POST['color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['color']) ? '#000' : $_POST['color'];

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            $errors[] = language()->global->error_message->empty_fields;
        }

        if(empty($errors)) {

            /* Insert to database */
            db()->where('project_id', $_POST['project_id'])->where('user_id', $this->user->user_id)->update('projects', [
                'name' => $_POST['name'],
                'color' => $_POST['color'],
                'last_datetime' => Date::$date,
            ]);

            Response::json(language()->project_update_modal->success_message->updated, 'success');

        }
    }

    private function delete() {
        $_POST['project_id'] = (int) $_POST['project_id'];

        /* Delete from database */
        db()->where('project_id', $_POST['project_id'])->where('user_id', $this->user->user_id)->delete('projects');

        Response::json(language()->project_delete_modal->success_message, 'success');

    }
}
