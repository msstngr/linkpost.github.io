<?php defined('ALTUMCODE') || die() ?>

<form name="update_biolink_" method="post" role="form">
    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" required="required" />
    <input type="hidden" name="request_type" value="update" />
    <input type="hidden" name="type" value="biolink" />
    <input type="hidden" name="subtype" value="mail" />
    <input type="hidden" name="link_id" value="<?= $row->link_id ?>" />

    <div class="notification-container"></div>

    <div class="form-group">
        <label><i class="fa fa-fw fa-paragraph fa-sm mr-1"></i> <?= language()->create_biolink_link_modal->input->name ?></label>
        <input type="text" name="name" class="form-control" value="<?= $row->settings->name ?>" required="required" />
    </div>

    <div class="form-group">
        <label><i class="fa fa-fw fa-image fa-sm mr-1"></i> <?= language()->create_biolink_link_modal->input->image ?></label>
        <div data-image-container class="<?= !empty($row->settings->image) ? null : 'd-none' ?>">
            <div class="row">
                <div class="m-1 col-6 col-xl-3">
                    <img src="<?= $row->settings->image ? SITE_URL . UPLOADS_URL_PATH . 'block_thumbnail_images/' . $row->settings->image : null ?>" class="img-fluid rounded <?= !empty($row->settings->image) ? null : 'd-none' ?>" loading="lazy" />
                </div>
            </div>
            <div class="custom-control custom-checkbox my-2">
                <input id="<?= $row->link_id . '_image_remove' ?>" name="image_remove" type="checkbox" class="custom-control-input" onchange="this.checked ? document.querySelector('#<?= 'image_' . $row->link_id ?>').classList.add('d-none') : document.querySelector('#<?= 'image_' . $row->link_id ?>').classList.remove('d-none')">
                <label class="custom-control-label" for="<?= $row->link_id . '_image_remove' ?>">
                    <span class="text-muted"><?= language()->global->delete_file ?></span>
                </label>
            </div>
        </div>
        <input id="<?= 'image_' . $row->link_id ?>" type="file" name="image" accept=".gif, .png, .jpg, .jpeg, .svg" class="form-control-file" />
    </div>

    <div class="form-group">
        <label><i class="fa fa-fw fa-globe fa-sm mr-1"></i> <?= language()->create_biolink_link_modal->input->icon ?></label>
        <input type="text" name="icon" class="form-control" value="<?= $row->settings->icon ?>" placeholder="<?= language()->create_biolink_link_modal->input->icon_placeholder ?>" />
        <small class="form-text text-muted"><?= language()->create_biolink_link_modal->input->icon_help ?></small>
    </div>

    <div <?= $this->user->plan_settings->custom_colored_links ? null : 'data-toggle="tooltip" title="' . language()->global->info_message->plan_feature_no_access . '"' ?>>
        <div class="<?= $this->user->plan_settings->custom_colored_links ? null : 'container-disabled' ?>">
            <div class="form-group">
                <label><i class="fa fa-fw fa-paint-brush fa-sm mr-1"></i> <?= language()->create_biolink_link_modal->input->text_color ?></label>
                <input type="hidden" name="text_color" class="form-control" value="<?= $row->settings->text_color ?>" required="required" />
                <div class="text_color_pickr"></div>
            </div>

            <div class="form-group">
                <label><i class="fa fa-fw fa-fill fa-sm mr-1"></i> <?= language()->create_biolink_link_modal->input->background_color ?></label>
                <input type="hidden" name="background_color" class="form-control" value="<?= $row->settings->background_color ?>" required="required" />
                <div class="background_color_pickr"></div>
            </div>

            <div class="custom-control custom-switch mr-3 mb-3">
                <input
                        type="checkbox"
                        class="custom-control-input"
                        id="outline_<?= $row->link_id ?>"
                        name="outline"
                    <?= $row->settings->outline ? 'checked="checked"' : null ?>
                >
                <label class="custom-control-label clickable" for="outline_<?= $row->link_id ?>"><?= language()->create_biolink_link_modal->input->outline ?></label>
            </div>

            <div class="form-group">
                <label><?= language()->create_biolink_link_modal->input->border_radius ?></label>
                <select name="border_radius" class="form-control">
                    <option value="straight" <?= $row->settings->border_radius == 'straight' ? 'selected="selected"' : null ?>><?= language()->create_biolink_link_modal->input->border_radius_straight ?></option>
                    <option value="round" <?= $row->settings->border_radius == 'round' ? 'selected="selected"' : null ?>><?= language()->create_biolink_link_modal->input->border_radius_round ?></option>
                    <option value="rounded" <?= $row->settings->border_radius == 'rounded' ? 'selected="selected"' : null ?>><?= language()->create_biolink_link_modal->input->border_radius_rounded ?></option>
                </select>
            </div>

            <div class="form-group">
                <label><?= language()->create_biolink_link_modal->input->animation ?></label>
                <select name="animation" class="form-control">
                    <option value="false" <?= !$row->settings->animation ? 'selected="selected"' : null ?>>-</option>
                    <?php foreach(require APP_PATH . 'includes/biolink_animations.php' as $animation): ?>
                    <option value="<?= $animation ?>" <?= $row->settings->animation == $animation ? 'selected="selected"' : null ?>><?= $animation ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label><?= language()->create_biolink_link_modal->input->animation_runs ?></label>
                <select name="animation_runs" class="form-control">
                    <option value="repeat-1" <?= $row->settings->animation_runs == 'repeat-1' ? 'selected="selected"' : null ?>>1</option>
                    <option value="repeat-2" <?= $row->settings->animation_runs == 'repeat-2' ? 'selected="selected"' : null ?>>2</option>
                    <option value="repeat-3" <?= $row->settings->animation_runs == 'repeat-3' ? 'selected="selected"' : null ?>>3</option>
                    <option value="infinite" <?= $row->settings->animation_runs == 'repeat-3' ? 'selected="selected"' : null ?>><?= language()->create_biolink_link_modal->input->animation_runs_infinite ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->email_placeholder ?></label>
        <input type="text" name="email_placeholder" class="form-control" value="<?= $row->settings->email_placeholder ?>" required="required" />
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->button_text ?></label>
        <input type="text" name="button_text" class="form-control" value="<?= $row->settings->button_text ?>" required="required" />
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->success_text ?></label>
        <input type="text" name="success_text" class="form-control" value="<?= $row->settings->success_text ?>" required="required" />
    </div>

    <div class="custom-control custom-switch mr-3 mb-3">
        <input
                type="checkbox"
                class="custom-control-input"
                id="show_agreement_<?= $row->link_id ?>"
                name="show_agreement"
            <?= $row->settings->show_agreement ? 'checked="checked"' : null ?>
        >
        <label class="custom-control-label clickable" for="show_agreement_<?= $row->link_id ?>"><?= language()->create_biolink_mail_modal->input->show_agreement ?></label>
        <div><small class="form-text text-muted"><?= language()->create_biolink_mail_modal->input->show_agreement_help ?></small></div>
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->agreement_text ?></label>
        <input type="text" name="agreement_text" class="form-control" value="<?= $row->settings->agreement_text ?>" />
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->agreement_url ?></label>
        <input type="text" name="agreement_url" class="form-control" value="<?= $row->settings->agreement_url ?>" />
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->mailchimp_api ?></label>
        <input type="text" name="mailchimp_api" class="form-control" value="<?= $row->settings->mailchimp_api ?>" />
        <small class="form-text text-muted"><?= language()->create_biolink_mail_modal->input->mailchimp_api_help ?></small>
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->mailchimp_api_list ?></label>
        <input type="text" name="mailchimp_api_list" class="form-control" value="<?= $row->settings->mailchimp_api_list ?>" />
        <small class="form-text text-muted"><?= language()->create_biolink_mail_modal->input->mailchimp_api_list_help ?></small>
    </div>

    <div class="form-group">
        <label><?= language()->create_biolink_mail_modal->input->webhook_url ?></label>
        <input type="text" name="webhook_url" class="form-control" value="<?= $row->settings->webhook_url ?>" />
        <small class="form-text text-muted"><?= language()->create_biolink_mail_modal->input->webhook_url_help ?></small>
    </div>

    <div class="mt-4">
        <button type="submit" name="submit" class="btn btn-block btn-primary"><?= language()->global->update ?></button>
    </div>
</form>
