<?php defined('ALTUMCODE') || die() ?>

<div data-link-id="<?= $data->link->link_id ?>" class="col-6 my-3">
    <?php if($data->link->location_url): ?>
    <a href="<?= $data->link->location_url . $data->link->utm_query ?>" data-link-url="<?= $data->link->url ?>" target="_blank">
    <?php endif ?>

        <div class="link-grid-image-wrapper" style="background-image: url('<?= $data->link->settings->image ?>')">

            <?php if($data->link->settings->name): ?>
                <div class="link-grid-image-overlay">
                    <span class="link-grid-image-overlay-text text-truncate"><?= $data->link->settings->name ?></span>
                </div>
            <?php endif ?>

        </div>

    <?php if($data->link->location_url): ?>
    </a>
    <?php endif ?>
</div>
