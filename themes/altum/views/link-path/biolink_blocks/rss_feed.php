<?php defined('ALTUMCODE') || die() ?>

<div data-link-id="<?= $data->link->link_id ?>" class="col-12 my-3">
    <?php

    /* Get all the links inside of the biolink */
    $cache_instance = \Altum\Cache::$adapter->getItem('biolink_links_' . $data->link->link_id . '_rss_feed');

    /* Set cache if not existing */
    if(is_null($cache_instance->get())) {

        $rss = simplexml_load_file($data->link->location_url);
        $counter = 0;
        $rss_data = [];

        foreach($rss->channel->item as $item) {
            $rss_data[] = [
                'title' => (string) $item->title,
                'url' => (string) $item->url
            ];

            $counter++;
            if($counter >= $data->link->settings->amount) break;
        }

        \Altum\Cache::$adapter->save($cache_instance->set($rss_data)->expiresAfter(1800)->addTag('biolinks_links_user_' . $data->link->user_id));

    } else {

        $rss_data = $cache_instance->get();

    }

    $counter = 0;
    ?>

    <?php foreach($rss_data as $item): ?>
    <a href="<?= $item['url'] ?>" class="btn btn-block btn-primary link-btn <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>">
        <?= $item['title'] ?>
    </a>

        <?php
        $counter++;
        if($counter >= $data->link->settings->amount) break;
        ?>
    <?php endforeach ?>
</div>

