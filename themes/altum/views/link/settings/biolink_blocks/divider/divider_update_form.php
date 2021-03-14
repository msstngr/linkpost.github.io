<?php defined('ALTUMCODE') || die() ?>

<form name="update_biolink_" method="post" role="form">
    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
    <input type="hidden" name="request_type" value="update" />
    <input type="hidden" name="type" value="biolink" />
    <input type="hidden" name="subtype" value="divider" />
    <input type="hidden" name="link_id" value="<?= $row->link_id ?>" />

    <div class="notification-container"></div>

    <div class="form-group">
        <label><?= $this->language->create_biolink_divider_modal->input->margin_top ?></label>
        <input type="range" name="margin_top" min="0" max="7" step="1" <?= $row->settings->margin_top ?> class="form-control-range" />
    </div>

    <div class="form-group">
        <label><?= $this->language->create_biolink_divider_modal->input->margin_bottom ?></label>
        <input type="range" name="margin_bottom" min="0" max="7" step="1" value="<?= $row->settings->margin_bottom ?>" class="form-control-range" />
    </div>

    <div <?= $this->user->plan_settings->custom_colored_links ? null : 'data-toggle="tooltip" title="' . $this->language->global->info_message->plan_feature_no_access . '"' ?>>
        <div class="<?= $this->user->plan_settings->custom_colored_links ? null : 'container-disabled' ?>">

            <div class="form-group">
                <label><i class="fa fa-fw fa-fill fa-sm mr-1"></i> <?= $this->language->create_biolink_link_modal->input->background_color ?></label>
                <input type="hidden" name="background_color" class="form-control" value="<?= $row->settings->background_color ?>" required="required" />
                <div class="background_color_pickr"></div>
            </div>

        </div>
    </div>

    <div class="form-group">
        <label><i class="fa fa-fw fa-globe fa-sm mr-1"></i> <?= $this->language->create_biolink_link_modal->input->icon ?></label>
        <input type="text" name="icon" class="form-control" value="<?= $row->settings->icon ?>" placeholder="<?= $this->language->create_biolink_link_modal->input->icon_placeholder ?>" />
        <small class="form-text text-muted"><?= $this->language->create_biolink_link_modal->input->icon_help ?></small>
    </div>

    <div class="mt-4">
        <button type="submit" name="submit" class="btn btn-block btn-primary"><?= $this->language->global->update ?></button>
    </div>
</form>
