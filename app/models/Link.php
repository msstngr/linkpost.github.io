<?php

namespace Altum\Models;

class Link extends Model {

    public function delete($link_id) {

        if(!$link = db()->where('link_id', $link_id)->getOne('links', ['user_id', 'link_id', 'biolink_id', 'type', 'subtype', 'settings'])) {
            return;
        }

        /* Process to delete the stored files of the link */
        if($link->type == 'biolink' && $link->subtype == 'base') {
            $link->settings = json_decode($link->settings);

            /* Delete avatar */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'avatars/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'avatars/' . $link->settings->image);
            }

            /* Delete background */
            if(is_string($link->settings->background) && !empty($link->settings->background) && file_exists(UPLOADS_PATH . 'backgrounds/' . $link->settings->background)) {
                unlink(UPLOADS_PATH . 'backgrounds/' . $link->settings->background);
            }

            /* Delete seo opengraph image */
            if(is_string($link->settings->seo->image) && !empty($link->settings->seo->image) && file_exists(UPLOADS_PATH . 'backgrounds/' . $link->settings->seo->image)) {
                unlink(UPLOADS_PATH . 'block_images/' . $link->settings->seo->image);
            }

            /* Get all the available biolink blocks and iterate over them to delete the stored images */
            $result = database()->query("SELECT `subtype`, `settings` FROM `links` WHERE `biolink_id` = {$link->link_id} AND `type` = 'biolink' AND `subtype` IN ('link', 'image', 'image_grid')");
            while($row = $result->fetch_object()) {
                $row->settings = json_decode($row->settings);

                /* Delete block image */
                if(in_array($row->subtype, ['image', 'image_grid'])) {
                    if(!empty($row->settings->image) && file_exists(UPLOADS_PATH . 'block_images/' . $row->settings->image)) {
                        unlink(UPLOADS_PATH . 'block_images/' . $row->settings->image);
                    }
                }

                /* Delete thumbnail image */
                if(in_array($row->subtype, ['link'])) {
                    if(!empty($row->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $row->settings->image)) {
                        unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $row->settings->image);
                    }
                }
            }
        }

        /* Delete the stored files of the link, if any */
        if($link->type == 'biolink' && in_array($link->subtype, ['image', 'image_grid'])) {
            $link->settings = json_decode($link->settings);

            /* Delete block image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_images/' . $link->settings->image);
            }
        }

        /* Delete the stored files of the link, if any */
        if($link->type == 'biolink' && in_array($link->subtype, ['link', 'mail', 'vcard'])) {
            $link->settings = json_decode($link->settings);

            /* Delete thumbnail image */
            if(!empty($link->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image)) {
                unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $link->settings->image);
            }
        }

        /* Delete from database */
        db()->where('link_id', $link_id)->delete('links');
        db()->where('biolink_id', $link_id)->delete('links');

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $link->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $link->link_id);

    }
}
