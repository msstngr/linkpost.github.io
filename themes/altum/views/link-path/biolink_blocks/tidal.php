<?php defined('ALTUMCODE') || die() ?>

<div data-link-id="<?= $data->link->link_id ?>" class="col-12 my-3 link-iframe-round">
    <iframe class="embed-responsive-item" scrolling="no" frameborder="no" style="height: 96px;width:100%;overflow:hidden;background:transparent;" src="<?= $data->link->location_url ?>"></iframe>
</div>
