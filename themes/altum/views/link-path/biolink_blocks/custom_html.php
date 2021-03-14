<?php defined('ALTUMCODE') || die() ?>

<div data-link-id="<?= $data->link->link_id ?>" class="col-12 my-3">
    <?= json_decode($data->link->settings)->html ?>
</div>
