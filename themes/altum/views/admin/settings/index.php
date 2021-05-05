<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex mb-4">
    <h1 class="h3"><i class="fa fa-fw fa-xs fa-wrench text-primary-900 mr-2"></i> <?= language()->admin_settings->header ?></h1>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="row">
    <div class="mb-3 mb-xl-5 mb-xl-0 col-12 col-xl-3">
        <div class="d-xl-none">
            <select name="settings_menu" class="form-control">
                <option value="#main" class="nav-link"><?= language()->admin_settings->tab->main ?></option>
                <option value="#links" class="nav-link"><?= language()->admin_settings->tab->links ?></option>
                <option value="#payment" class="nav-link"><?= language()->admin_settings->tab->payment ?></option>
                <option value="#business" class="nav-link"><?= language()->admin_settings->tab->business ?></option>
                <option value="#captcha" class="nav-link"><?= language()->admin_settings->tab->captcha ?></option>
                <option value="#facebook" class="nav-link"><?= language()->admin_settings->tab->facebook ?></option>
                <option value="#ads" class="nav-link"><?= language()->admin_settings->tab->ads ?></option>
                <option value="#socials" class="nav-link"><?= language()->admin_settings->tab->socials ?></option>
                <option value="#smtp" class="nav-link"><?= language()->admin_settings->tab->smtp ?></option>
                <option value="#custom" class="nav-link"><?= language()->admin_settings->tab->custom ?></option>
                <option value="#announcements" class="nav-link"><?= language()->admin_settings->tab->announcements ?></option>
                <option value="#email_notifications" class="nav-link"><?= language()->admin_settings->tab->email_notifications ?></option>
                <option value="#webhooks" class="nav-link"><?= language()->admin_settings->tab->webhooks ?></option>
                <option value="#license" class="nav-link"><?= language()->admin_settings->tab->license ?></option>
            </select>
        </div>

        <?php ob_start() ?>
        <script>
            document.querySelector('select[name="settings_menu"]').addEventListener('change', event => {
                document.querySelector(`a[href="${event.currentTarget.value}"]`).click();
            })
        </script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

        <div class="nav flex-column nav-pills d-none d-xl-flex" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <a class="nav-link active" href="#main" data-toggle="pill" role="tab"><i class="fa fa-fw fa-home fa-sm mr-1"></i> <?= language()->admin_settings->tab->main ?></a>
            <a class="nav-link" href="#links" data-toggle="pill" role="tab"><i class="fa fa-fw fa-link fa-sm mr-1"></i> <?= language()->admin_settings->tab->links ?></a>
            <a class="nav-link" href="#payment" data-toggle="pill" role="tab"><i class="fa fa-fw fa-dollar-sign fa-sm mr-1"></i> <?= language()->admin_settings->tab->payment ?></a>
            <a class="nav-link" href="#business" data-toggle="pill" role="tab"><i class="fa fa-fw fa-briefcase fa-sm mr-1"></i> <?= language()->admin_settings->tab->business ?></a>
            <a class="nav-link" href="#captcha" data-toggle="pill" role="tab"><i class="fa fa-fw fa-low-vision fa-sm mr-1"></i> <?= language()->admin_settings->tab->captcha ?></a>
            <a class="nav-link" href="#facebook" data-toggle="pill" role="tab"><i class="fab fa-fw fa-facebook fa-sm mr-1"></i> <?= language()->admin_settings->tab->facebook ?></a>
            <a class="nav-link" href="#ads" data-toggle="pill" role="tab"><i class="fa fa-fw fa-ad fa-sm mr-1"></i> <?= language()->admin_settings->tab->ads ?></a>
            <a class="nav-link" href="#socials" data-toggle="pill" role="tab"><i class="fab fa-fw fa-instagram fa-sm mr-1"></i> <?= language()->admin_settings->tab->socials ?></a>
            <a class="nav-link" href="#smtp" data-toggle="pill" role="tab"><i class="fa fa-fw fa-mail-bulk fa-sm mr-1"></i> <?= language()->admin_settings->tab->smtp ?></a>
            <a class="nav-link" href="#custom" data-toggle="pill" role="tab"><i class="fa fa-fw fa-paint-brush fa-sm mr-1"></i> <?= language()->admin_settings->tab->custom ?></a>
            <a class="nav-link" href="#announcements" data-toggle="pill" role="tab"><i class="fa fa-fw fa-sm fa-bullhorn mr-1"></i> <?= language()->admin_settings->tab->announcements ?></a>
            <a class="nav-link" href="#email_notifications" data-toggle="pill" role="tab"><i class="fa fa-fw fa-bell fa-sm mr-1"></i> <?= language()->admin_settings->tab->email_notifications ?></a>
            <a class="nav-link" href="#webhooks" data-toggle="pill" role="tab"><i class="fa fa-fw fa-sm fa-code-branch mr-1"></i> <?= language()->admin_settings->tab->webhooks ?></a>
            <a class="nav-link" href="#license" data-toggle="pill" role="tab"><i class="fa fa-fw fa-sm fa-key fa-sm mr-1"></i> <?= language()->admin_settings->tab->license ?></a>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body">

                <form action="" method="post" role="form" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?= \Altum\Middlewares\Csrf::get() ?>" />

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="main">
                            <div class="form-group">
                                <label for="title"><i class="fa fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= language()->admin_settings->main->title ?></label>
                                <input id="title" type="text" name="title" class="form-control form-control-lg" value="<?= settings()->title ?>" />
                            </div>

                            <div class="form-group">
                                <label for="default_language"><i class="fa fa-fw fa-sm fa-language text-muted mr-1"></i> <?= language()->admin_settings->main->default_language ?></label>
                                <select id="default_language" name="default_language" class="form-control form-control-lg">
                                    <?php foreach(\Altum\Language::$languages as $value) echo '<option value="' . $value . '" ' . (settings()->default_language == $value ? 'selected="selected"' : null) . '>' . $value . '</option>' ?>
                                </select>
                                <small class="form-text text-muted"><?= language()->admin_settings->main->default_language_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="default_theme_style"><i class="fa fa-fw fa-sm fa-fill-drip text-muted mr-1"></i> <?= language()->admin_settings->main->default_theme_style ?></label>
                                <select id="default_theme_style" name="default_theme_style" class="form-control form-control-lg">
                                    <?php foreach(\Altum\ThemeStyle::$themes as $key => $value) echo '<option value="' . $key . '" ' . (settings()->default_theme_style == $key ? 'selected="selected"' : null) . '>' . $key . '</option>' ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="logo"><i class="fa fa-fw fa-sm fa-eye text-muted mr-1"></i> <?= language()->admin_settings->main->logo ?></label>
                                <?php if(!empty(settings()->logo)): ?>
                                    <div class="m-1">
                                        <img src="<?= SITE_URL . UPLOADS_URL_PATH . 'logo/' . settings()->logo ?>" class="img-fluid" style="max-height: 2.5rem;height: 2.5rem;" />
                                    </div>
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="logo_remove" name="logo_remove" type="checkbox" class="custom-control-input" onchange="this.checked ? document.querySelector('#logo').classList.add('d-none') : document.querySelector('#logo').classList.remove('d-none')">
                                        <label class="custom-control-label" for="logo_remove">
                                            <span class="text-muted"><?= language()->global->delete_file ?></span>
                                        </label>
                                    </div>
                                <?php endif ?>
                                <input id="logo" type="file" name="logo" accept=".gif, .ico, .png, .jpg, .jpeg, .svg" class="form-control-file" />
                                <small class="form-text text-muted"><?= language()->admin_settings->main->logo_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="favicon"><i class="fa fa-fw fa-sm fa-icons text-muted mr-1"></i> <?= language()->admin_settings->main->favicon ?></label>
                                <?php if(!empty(settings()->favicon)): ?>
                                    <div class="m-1">
                                        <img src="<?= SITE_URL . UPLOADS_URL_PATH . 'favicon/' . settings()->favicon ?>" class="img-fluid" style="max-height: 32px;height: 32px;" />
                                    </div>
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="favicon_remove" name="favicon_remove" type="checkbox" class="custom-control-input" onchange="this.checked ? document.querySelector('#favicon').classList.add('d-none') : document.querySelector('#favicon').classList.remove('d-none')">
                                        <label class="custom-control-label" for="favicon_remove">
                                            <span class="text-muted"><?= language()->global->delete_file ?></span>
                                        </label>
                                    </div>
                                <?php endif ?>
                                <input id="favicon" type="file" name="favicon" accept=".gif, .ico, .png, .jpg, .jpeg, .svg" class="form-control-file" />
                                <small class="form-text text-muted"><?= language()->admin_settings->main->favicon_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="default_timezone"><i class="fa fa-fw fa-sm fa-atlas text-muted mr-1"></i> <?= language()->admin_settings->main->default_timezone ?></label>
                                <select id="default_timezone" name="default_timezone" class="form-control form-control-lg">
                                    <?php foreach(DateTimeZone::listIdentifiers() as $timezone) echo '<option value="' . $timezone . '" ' . (settings()->default_timezone == $timezone ? 'selected="selected"' : null) . '>' . $timezone . '</option>' ?>
                                </select>
                                <small class="form-text text-muted"><?= language()->admin_settings->main->default_timezone_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="email_confirmation"><i class="fa fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= language()->admin_settings->main->email_confirmation ?></label>
                                <select id="email_confirmation" name="email_confirmation" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->email_confirmation ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->email_confirmation ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                                <small class="form-text text-muted"><?= language()->admin_settings->main->email_confirmation_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="register_is_enabled"><i class="fa fa-fw fa-sm fa-users text-muted mr-1"></i> <?= language()->admin_settings->main->register_is_enabled ?></label>
                                <select id="register_is_enabled" name="register_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->register_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->register_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="index_url"><i class="fa fa-fw fa-sm fa-sitemap text-muted mr-1"></i> <?= language()->admin_settings->main->index_url ?></label>
                                <input id="index_url" type="text" name="index_url" class="form-control form-control-lg" value="<?= settings()->index_url ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->main->index_url_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="terms_and_conditions_url"><i class="fa fa-fw fa-sm fa-file-word text-muted mr-1"></i> <?= language()->admin_settings->main->terms_and_conditions_url ?></label>
                                <input id="terms_and_conditions_url" type="text" name="terms_and_conditions_url" class="form-control form-control-lg" value="<?= settings()->terms_and_conditions_url ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->main->terms_and_conditions_url_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="privacy_policy_url"><i class="fa fa-fw fa-sm fa-file-word text-muted mr-1"></i> <?= language()->admin_settings->main->privacy_policy_url ?></label>
                                <input id="privacy_policy_url" type="text" name="privacy_policy_url" class="form-control form-control-lg" value="<?= settings()->privacy_policy_url ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->main->privacy_policy_url_help ?></small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="links">
                            <div class="form-group">
                                <label for="links_branding"><?= language()->admin_settings->links->branding ?></label>
                                <textarea id="links_branding" name="links_branding" class="form-control form-control-lg"><?= settings()->links->branding ?></textarea>
                                <small class="form-text text-muted"><?= language()->admin_settings->links->branding_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_shortener_is_enabled"><?= language()->admin_settings->links->shortener_is_enabled ?></label>
                                <select id="links_shortener_is_enabled" name="links_shortener_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->links->shortener_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->links->shortener_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                                <small class="form-text text-muted"><?= language()->admin_settings->links->shortener_is_enabled_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_domains_is_enabled"><?= language()->admin_settings->links->domains_is_enabled ?></label>
                                <select id="links_domains_is_enabled" name="links_domains_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->links->domains_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->links->domains_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                                <small class="form-text text-muted"><?= language()->admin_settings->links->domains_is_enabled_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_main_domain_is_enabled"><?= language()->admin_settings->links->main_domain_is_enabled ?></label>
                                <select id="links_main_domain_is_enabled" name="links_main_domain_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->links->main_domain_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->links->main_domain_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                                <small class="form-text text-muted"><?= language()->admin_settings->links->main_domain_is_enabled_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_avatar_size_limit"><?= language()->admin_settings->links->avatar_size_limit ?></label>
                                <input id="links_avatar_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="links_avatar_size_limit" class="form-control form-control-lg" value="<?= settings()->links->avatar_size_limit ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->links->size_limit_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_background_size_limit"><?= language()->admin_settings->links->background_size_limit ?></label>
                                <input id="links_background_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="links_background_size_limit" class="form-control form-control-lg" value="<?= settings()->links->background_size_limit ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->links->size_limit_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_thumbnail_image_size_limit"><?= language()->admin_settings->links->thumbnail_image_size_limit ?></label>
                                <input id="links_thumbnail_image_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="links_thumbnail_image_size_limit" class="form-control form-control-lg" value="<?= settings()->links->thumbnail_image_size_limit ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->links->size_limit_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_image_size_limit"><?= language()->admin_settings->links->image_size_limit ?></label>
                                <input id="links_image_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="links_image_size_limit" class="form-control form-control-lg" value="<?= settings()->links->image_size_limit ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->links->size_limit_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_blacklisted_domains"><?= language()->admin_settings->links->blacklisted_domains ?></label>
                                <textarea id="links_blacklisted_domains" class="form-control form-control-lg" name="links_blacklisted_domains"><?= settings()->links->blacklisted_domains ?></textarea>
                                <small class="form-text text-muted"><?= language()->admin_settings->links->blacklisted_domains_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="links_blacklisted_keywords"><?= language()->admin_settings->links->blacklisted_keywords ?></label>
                                <textarea id="links_blacklisted_keywords" class="form-control form-control-lg" name="links_blacklisted_keywords"><?= settings()->links->blacklisted_keywords ?></textarea>
                                <small class="form-text text-muted"><?= language()->admin_settings->links->blacklisted_keywords_help ?></small>
                            </div>

                            <hr class="my-4">

                            <p class="h5"><i class="fa fa-fw fa-fish fa-sm mr-1 text-muted"></i> <?= language()->admin_settings->links->phishtank ?></p>
                            <p class="text-muted"><?= language()->admin_settings->links->phishtank_help ?></p>

                            <div class="form-group">
                                <label for="links_phishtank_is_enabled"><?= language()->admin_settings->links->phishtank_is_enabled ?></label>
                                <select id="links_phishtank_is_enabled" name="links_phishtank_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->links->phishtank_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->links->phishtank_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="links_phishtank_api_key"><?= language()->admin_settings->links->phishtank_api_key ?></label>
                                <input id="links_phishtank_api_key" type="text" name="links_phishtank_api_key" class="form-control form-control-lg" value="<?= settings()->links->phishtank_api_key ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->links->phishtank_api_key_help ?></small>
                            </div>

                            <hr class="my-4">

                            <p class="h5"><i class="fab fa-fw fa-google fa-sm mr-1 text-muted"></i> <?= language()->admin_settings->links->google_safe_browsing ?></p>
                            <p class="text-muted"><?= language()->admin_settings->links->google_safe_browsing_help ?></p>

                            <div class="form-group">
                                <label for="links_google_safe_browsing_is_enabled"><?= language()->admin_settings->links->google_safe_browsing_is_enabled ?></label>
                                <select id="links_google_safe_browsing_is_enabled" name="links_google_safe_browsing_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->links->google_safe_browsing_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->links->google_safe_browsing_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="links_google_safe_browsing_api_key"><?= language()->admin_settings->links->google_safe_browsing_api_key ?></label>
                                <input id="links_google_safe_browsing_api_key" type="text" name="links_google_safe_browsing_api_key" class="form-control form-control-lg" value="<?= settings()->links->google_safe_browsing_api_key ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->links->google_safe_browsing_api_key_help ?></small>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="payment">
                            <?php if(!in_array(settings()->license->type, ['Extended License', 'extended'])): ?>
                                <div class="alert alert-primary" role="alert">
                                    You need to own the Extended License in order to activate the payment system.
                                </div>
                            <?php endif ?>

                            <div class="<?= !in_array(settings()->license->type, ['Extended License', 'extended']) ? 'container-disabled' : null ?>">
                                <div class="form-group">
                                    <label for="payment_is_enabled"><i class="fa fa-fw fa-sm fa-dollar-sign text-muted mr-1"></i> <?= language()->admin_settings->payment->is_enabled ?></label>
                                    <select id="payment_is_enabled" name="payment_is_enabled" class="form-control form-control-lg">
                                        <option value="1" <?= settings()->payment->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                        <option value="0" <?= !settings()->payment->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                    </select>
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->is_enabled_help ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="payment_type"><i class="fa fa-fw fa-sm fa-credit-card text-muted mr-1"></i> <?= language()->admin_settings->payment->type ?></label>
                                    <select id="payment_type" name="payment_type" class="form-control form-control-lg">
                                        <option value="one_time" <?= settings()->payment->type == 'one_time' ? 'selected="selected"' : null ?>><?= language()->admin_settings->payment->type_one_time ?></option>
                                        <option value="recurring" <?= settings()->payment->type == 'recurring' ? 'selected="selected"' : null ?>><?= language()->admin_settings->payment->type_recurring ?></option>
                                        <option value="both" <?= settings()->payment->type == 'both' ? 'selected="selected"' : null ?>><?= language()->admin_settings->payment->type_both ?></option>
                                    </select>
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->type_help ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="payment_brand_name"><i class="fa fa-fw fa-sm fa-copyright text-muted mr-1"></i> <?= language()->admin_settings->payment->brand_name ?></label>
                                    <input id="payment_brand_name" type="text" name="payment_brand_name" class="form-control form-control-lg" value="<?= settings()->payment->brand_name ?>" />
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->brand_name_help ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="payment_currency"><i class="fa fa-fw fa-sm fa-coins text-muted mr-1"></i> <?= language()->admin_settings->payment->currency ?></label>
                                    <input id="payment_currency" type="text" name="payment_currency" class="form-control form-control-lg" value="<?= settings()->payment->currency ?>" />
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->currency_help ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="payment_codes_is_enabled"><i class="fa fa-fw fa-sm fa-tags text-muted mr-1"></i> <?= language()->admin_settings->payment->codes_is_enabled ?></label>
                                    <select id="payment_codes_is_enabled" name="payment_codes_is_enabled" class="form-control form-control-lg">
                                        <option value="1" <?= settings()->payment->codes_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                        <option value="0" <?= !settings()->payment->codes_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                    </select>
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->codes_is_enabled_help ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="payment_taxes_and_billing_is_enabled"><i class="fa fa-fw fa-sm fa-receipt text-muted mr-1"></i> <?= language()->admin_settings->payment->taxes_and_billing_is_enabled ?></label>
                                    <select id="payment_taxes_and_billing_is_enabled" name="payment_taxes_and_billing_is_enabled" class="form-control form-control-lg">
                                        <option value="1" <?= settings()->payment->taxes_and_billing_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                        <option value="0" <?= !settings()->payment->taxes_and_billing_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                    </select>
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->taxes_and_billing_is_enabled_help ?></small>
                                </div>

                                <hr class="my-4">

                                <p class="h5"><?= language()->admin_settings->payment->paypal ?></p>

                                <div class="form-group">
                                    <label for="paypal_is_enabled"><?= language()->admin_settings->payment->paypal_is_enabled ?></label>
                                    <select id="paypal_is_enabled" name="paypal_is_enabled" class="form-control form-control-lg">
                                        <option value="1" <?= settings()->paypal->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                        <option value="0" <?= !settings()->paypal->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="paypal_mode"><?= language()->admin_settings->payment->paypal_mode ?></label>
                                    <select id="paypal_mode" name="paypal_mode" class="form-control form-control-lg">
                                        <option value="live" <?= settings()->paypal->mode == 'live' ? 'selected="selected"' : null ?>>live</option>
                                        <option value="sandbox" <?= settings()->paypal->mode == 'sandbox' ? 'selected="selected"' : null ?>>sandbox</option>
                                    </select>
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->paypal_mode_help ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="paypal_client_id"><?= language()->admin_settings->payment->paypal_client_id ?></label>
                                    <input type="text" name="paypal_client_id" class="form-control form-control-lg" value="<?= settings()->paypal->client_id ?>" />
                                </div>

                                <div class="form-group">
                                    <label for="paypal_secret"><?= language()->admin_settings->payment->paypal_secret ?></label>
                                    <input type="text" name="paypal_secret" class="form-control form-control-lg" value="<?= settings()->paypal->secret ?>" />
                                </div>

                                <hr class="my-4">

                                <p class="h5"><?= language()->admin_settings->payment->stripe ?></p>

                                <div class="form-group">
                                    <label for="stripe_is_enabled"><?= language()->admin_settings->payment->stripe_is_enabled ?></label>
                                    <select id="stripe_is_enabled" name="stripe_is_enabled" class="form-control form-control-lg">
                                        <option value="1" <?= settings()->stripe->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                        <option value="0" <?= !settings()->stripe->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="stripe_publishable_key"><?= language()->admin_settings->payment->stripe_publishable_key ?></label>
                                    <input type="text" name="stripe_publishable_key" class="form-control form-control-lg" value="<?= settings()->stripe->publishable_key ?>" />
                                </div>

                                <div class="form-group">
                                    <label for="stripe_secret_key"><?= language()->admin_settings->payment->stripe_secret_key ?></label>
                                    <input type="text" name="stripe_secret_key" class="form-control form-control-lg" value="<?= settings()->stripe->secret_key ?>" />
                                </div>

                                <div class="form-group">
                                    <label for="stripe_webhook_secret"><?= language()->admin_settings->payment->stripe_webhook_secret ?></label>
                                    <input type="text" name="stripe_webhook_secret" class="form-control form-control-lg" value="<?= settings()->stripe->webhook_secret ?>" />
                                </div>

                                <hr class="my-4">

                                <p class="h5"><?= language()->admin_settings->payment->offline_payment ?></p>

                                <div class="form-group">
                                    <label for="offline_payment_is_enabled"><?= language()->admin_settings->payment->offline_payment_is_enabled ?></label>
                                    <select id="offline_payment_is_enabled" name="offline_payment_is_enabled" class="form-control form-control-lg">
                                        <option value="1" <?= settings()->offline_payment->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                        <option value="0" <?= !settings()->offline_payment->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="offline_payment_instructions"><?= language()->admin_settings->payment->offline_payment_instructions ?></label>
                                    <textarea id="offline_payment_instructions" name="offline_payment_instructions" class="form-control form-control-lg"><?= settings()->offline_payment->instructions ?></textarea>
                                    <small class="form-text text-muted"><?= language()->admin_settings->payment->offline_payment_instructions_help ?></small>
                                </div>

                            </div>
                        </div>

                        <div class="tab-pane fade" id="business">
                            <p class="h5"><?= language()->admin_settings->business->header ?></p>
                            <p class="text-muted"><?= language()->admin_settings->business->subheader ?></p>

                            <div class="form-group">
                                <label for="business_invoice_is_enabled"><?= language()->admin_settings->business->invoice_is_enabled ?></label>
                                <select id="business_invoice_is_enabled" name="business_invoice_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->business->invoice_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->business->invoice_is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                                <small class="form-text text-muted"><?= language()->admin_settings->business->invoice_is_enabled_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="business_invoice_nr_prefix"><?= language()->admin_settings->business->invoice_nr_prefix ?></label>
                                <input type="text" name="business_invoice_nr_prefix" class="form-control form-control-lg" value="<?= settings()->business->invoice_nr_prefix ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->business->invoice_nr_prefix_help ?></small>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="business_name"><?= language()->admin_settings->business->name ?></label>
                                        <input type="text" name="business_name" class="form-control form-control-lg" value="<?= settings()->business->name ?>" />
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="business_address"><?= language()->admin_settings->business->address ?></label>
                                        <input type="text" name="business_address" class="form-control form-control-lg" value="<?= settings()->business->address ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_city"><?= language()->admin_settings->business->city ?></label>
                                        <input type="text" name="business_city" class="form-control form-control-lg" value="<?= settings()->business->city ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-4">
                                    <div class="form-group">
                                        <label for="business_county"><?= language()->admin_settings->business->county ?></label>
                                        <input type="text" name="business_county" class="form-control form-control-lg" value="<?= settings()->business->county ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-2">
                                    <div class="form-group">
                                        <label for="business_zip"><?= language()->admin_settings->business->zip ?></label>
                                        <input type="text" name="business_zip" class="form-control form-control-lg" value="<?= settings()->business->zip ?>" />
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="business_country"><?= language()->admin_settings->business->country ?></label>
                                        <select id="business_country" name="business_country" class="form-control form-control-lg">
                                            <?php foreach(get_countries_array() as $key => $value): ?>
                                                <option value="<?= $key ?>" <?= settings()->business->country == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_email"><?= language()->admin_settings->business->email ?></label>
                                        <input type="text" name="business_email" class="form-control form-control-lg" value="<?= settings()->business->email ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_phone"><?= language()->admin_settings->business->phone ?></label>
                                        <input type="text" name="business_phone" class="form-control form-control-lg" value="<?= settings()->business->phone ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_tax_type"><?= language()->admin_settings->business->tax_type ?></label>
                                        <input type="text" name="business_tax_type" class="form-control form-control-lg" value="<?= settings()->business->tax_type ?>" placeholder="<?= language()->admin_settings->business->tax_type_placeholder ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_tax_id"><?= language()->admin_settings->business->tax_id ?></label>
                                        <input type="text" name="business_tax_id" class="form-control form-control-lg" value="<?= settings()->business->tax_id ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_custom_key_one"><?= language()->admin_settings->business->custom_key_one ?></label>
                                        <input type="text" name="business_custom_key_one" class="form-control form-control-lg" value="<?= settings()->business->custom_key_one ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_custom_value_one"><?= language()->admin_settings->business->custom_value_one ?></label>
                                        <input type="text" name="business_custom_value_one" class="form-control form-control-lg" value="<?= settings()->business->custom_value_one ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_custom_key_two"><?= language()->admin_settings->business->custom_key_two ?></label>
                                        <input type="text" name="business_custom_key_two" class="form-control form-control-lg" value="<?= settings()->business->custom_key_two ?>" />
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="business_custom_value_two"><?= language()->admin_settings->business->custom_value_two ?></label>
                                        <input type="text" name="business_custom_value_two" class="form-control form-control-lg" value="<?= settings()->business->custom_value_two ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="ads">
                            <p class="text-muted"><?= language()->admin_settings->ads->ads_help ?></p>

                            <div class="form-group">
                                <label for="a_header"><?= language()->admin_settings->ads->header ?></label>
                                <textarea id="a_header" name="a_header" class="form-control form-control-lg"><?= settings()->ads->header ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="a_footer"><?= language()->admin_settings->ads->footer ?></label>
                                <textarea id="a_footer" name="a_footer" class="form-control form-control-lg"><?= settings()->ads->footer ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="a_header_biolink"><?= language()->admin_settings->ads->header_biolink ?></label>
                                <textarea id="a_header_biolink" name="a_header_biolink" class="form-control form-control-lg"><?= settings()->ads->header_biolink ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="a_footer_biolink"><?= language()->admin_settings->ads->footer_biolink ?></label>
                                <textarea id="a_footer_biolink" name="a_footer_biolink" class="form-control form-control-lg"><?= settings()->ads->footer_biolink ?></textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="captcha">

                            <div class="form-group">
                                <label for="captcha_type"><?= language()->admin_settings->captcha->type ?></label>
                                <select id="captcha_type" name="captcha_type" class="form-control form-control-lg">
                                    <option value="basic" <?= settings()->captcha->type == 'basic' ? 'selected="selected"' : null ?>><?= language()->admin_settings->captcha->type_basic ?></option>
                                    <option value="recaptcha" <?= settings()->captcha->type == 'recaptcha' ? 'selected="selected"' : null ?>><?= language()->admin_settings->captcha->type_recaptcha ?></option>
                                    <option value="hcaptcha" <?= settings()->captcha->type == 'hcaptcha' ? 'selected="selected"' : null ?>><?= language()->admin_settings->captcha->type_hcaptcha ?></option>
                                </select>
                            </div>

                            <div id="captcha_recaptcha">
                                <div class="form-group">
                                    <label for="captcha_recaptcha_public_key"><?= language()->admin_settings->captcha->recaptcha_public_key ?></label>
                                    <input id="captcha_recaptcha_public_key" type="text" name="captcha_recaptcha_public_key" class="form-control form-control-lg" value="<?= settings()->captcha->recaptcha_public_key ?>" />
                                </div>

                                <div class="form-group">
                                    <label for="captcha_recaptcha_private_key"><?= language()->admin_settings->captcha->recaptcha_private_key ?></label>
                                    <input id="captcha_recaptcha_private_key" type="text" name="captcha_recaptcha_private_key" class="form-control form-control-lg" value="<?= settings()->captcha->recaptcha_private_key ?>" />
                                </div>
                            </div>

                            <div id="captcha_hcaptcha">
                                <div class="form-group">
                                    <label for="captcha_hcaptcha_site_key"><?= language()->admin_settings->captcha->hcaptcha_site_key ?></label>
                                    <input id="captcha_hcaptcha_site_key" type="text" name="captcha_hcaptcha_site_key" class="form-control form-control-lg" value="<?= settings()->captcha->hcaptcha_site_key ?>" />
                                </div>

                                <div class="form-group">
                                    <label for="captcha_hcaptcha_secret_key"><?= language()->admin_settings->captcha->hcaptcha_secret_key ?></label>
                                    <input id="captcha_hcaptcha_secret_key" type="text" name="captcha_hcaptcha_secret_key" class="form-control form-control-lg" value="<?= settings()->captcha->hcaptcha_secret_key ?>" />
                                </div>
                            </div>

                            <?php foreach(['login', 'register', 'lost_password', 'resend_activation'] as $key): ?>
                                <div class="form-group">
                                    <label for="captcha_<?= $key ?>_is_enabled"><?= language()->admin_settings->captcha->{$key . '_is_enabled'} ?></label>
                                    <select id="captcha_<?= $key ?>_is_enabled" name="captcha_<?= $key ?>_is_enabled" class="form-control form-control-lg">
                                        <option value="1" <?= settings()->captcha->{$key . '_is_enabled'} ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                        <option value="0" <?= !settings()->captcha->{$key . '_is_enabled'} ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                    </select>
                                </div>
                            <?php endforeach ?>

                        </div>

                        <div class="tab-pane fade" id="facebook">

                            <div class="form-group">
                                <label for="facebook_is_enabled"><?= language()->admin_settings->facebook->is_enabled ?></label>
                                <select id="facebook_is_enabled" name="facebook_is_enabled" class="form-control form-control-lg">
                                    <option value="1" <?= settings()->facebook->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->yes ?></option>
                                    <option value="0" <?= !settings()->facebook->is_enabled ? 'selected="selected"' : null ?>><?= language()->global->no ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="facebook_app_id"><?= language()->admin_settings->facebook->app_id ?></label>
                                <input type="text" name="facebook_app_id" class="form-control form-control-lg" value="<?= settings()->facebook->app_id ?>" />
                            </div>

                            <div class="form-group">
                                <label for="facebook_app_secret"><?= language()->admin_settings->facebook->app_secret ?></label>
                                <input type="text" name="facebook_app_secret" class="form-control form-control-lg" value="<?= settings()->facebook->app_secret ?>" />
                            </div>
                        </div>

                        <div class="tab-pane fade" id="socials">
                            <p class="text-muted"><?= language()->admin_settings->socials->socials_help ?></p>

                            <?php foreach(require APP_PATH . 'includes/admin_socials.php' AS $key => $value): ?>
                                <div class="form-group">
                                    <label for="socials_"><i class="<?= $value['icon'] ?> fa-fw fa-sm mr-1 text-muted"></i> <?= $value['name'] ?></label>
                                    <input type="text" name="socials_<?= $key ?>" class="form-control form-control-lg" value="<?= settings()->socials->{$key} ?>" />
                                </div>
                            <?php endforeach ?>
                        </div>

                        <div class="tab-pane fade" id="smtp">

                            <div class="form-group">
                                <label for="smtp_from_name"><?= language()->admin_settings->smtp->from_name ?></label>
                                <input type="text" name="smtp_from_name" class="form-control form-control-lg" value="<?= settings()->smtp->from_name ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->smtp->from_name_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="smtp_from"><?= language()->admin_settings->smtp->from ?></label>
                                <input type="text" name="smtp_from" class="form-control form-control-lg" value="<?= settings()->smtp->from ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->smtp->from_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="smtp_host"><?= language()->admin_settings->smtp->host ?></label>
                                <input type="text" name="smtp_host" class="form-control form-control-lg" value="<?= settings()->smtp->host ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->smtp->host_help ?></small>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="smtp_encryption"><?= language()->admin_settings->smtp->encryption ?></label>
                                        <select id="smtp_encryption" name="smtp_encryption" class="form-control form-control-lg">
                                            <option value="0" <?= settings()->smtp->encryption == '0' ? 'selected="selected"' : null ?>>None</option>
                                            <option value="ssl" <?= settings()->smtp->encryption == 'ssl' ? 'selected="selected"' : null ?>>SSL</option>
                                            <option value="tls" <?= settings()->smtp->encryption == 'tls' ? 'selected="selected"' : null ?>>TLS</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label for="smtp_port"><?= language()->admin_settings->smtp->port ?></label>
                                        <input type="text" name="smtp_port" class="form-control form-control-lg" value="<?= settings()->smtp->port ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input id="smtp_auth" name="smtp_auth" type="checkbox" class="custom-control-input" <?= settings()->smtp->auth ? 'checked="checked"' : null ?>>
                                <label class="custom-control-label" for="smtp_auth"><?= language()->admin_settings->smtp->auth ?></label>
                            </div>

                            <div class="form-group">
                                <label for="smtp_username"><?= language()->admin_settings->smtp->username ?></label>
                                <input type="text" name="smtp_username" class="form-control form-control-lg" value="<?= settings()->smtp->username ?>" />
                            </div>

                            <div class="form-group">
                                <label for="smtp_password"><?= language()->admin_settings->smtp->password ?></label>
                                <input id="smtp_password" type="password" name="smtp_password" class="form-control form-control-lg" value="<?= settings()->smtp->password ?>" />
                            </div>

                            <div class="my-3">
                                <a href="admin/settings/testemail<?= \Altum\Middlewares\Csrf::get_url_query() ?>" class="btn btn-outline-info"><?= language()->admin_settings->button->test_email ?></a>
                                <small class="form-text text-muted"><?= language()->admin_settings->button->test_email_help ?></small>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="custom">
                            <div class="form-group">
                                <label for="custom_head_js"><i class="fab fa-fw fa-sm fa-js text-muted mr-1"></i> <?= language()->admin_settings->custom->head_js ?></label>
                                <textarea id="custom_head_js" name="custom_head_js" class="form-control form-control-lg"><?= settings()->custom->head_js ?></textarea>
                                <small class="form-text text-muted"><?= language()->admin_settings->custom->head_js_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="custom_head_css"><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= language()->admin_settings->custom->head_css ?></label>
                                <textarea id="custom_head_css" name="custom_head_css" class="form-control form-control-lg"><?= settings()->custom->head_css ?></textarea>
                                <small class="form-text text-muted"><?= language()->admin_settings->custom->head_css_help ?></small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="announcements">
                            <div class="form-group">
                                <label for="announcements_content"><?= language()->admin_settings->announcements->content ?></label>
                                <textarea id="announcements_content" name="announcements_content" class="form-control form-control-lg"><?= settings()->announcements->content ?></textarea>
                                <small class="form-text text-muted"><?= language()->admin_settings->announcements->content_help ?></small>
                            </div>

                            <div class="custom-control custom-switch my-3">
                                <input id="announcements_show_logged_in" name="announcements_show_logged_in" type="checkbox" class="custom-control-input" <?= settings()->announcements->show_logged_in ? 'checked' : null?>>
                                <label class="custom-control-label" for="announcements_show_logged_in"><?= language()->admin_settings->announcements->show_logged_in ?></label>
                            </div>

                            <div class="custom-control custom-switch my-3">
                                <input id="announcements_show_logged_out" name="announcements_show_logged_out" type="checkbox" class="custom-control-input" <?= settings()->announcements->show_logged_out ? 'checked' : null?>>
                                <label class="custom-control-label" for="announcements_show_logged_out"><?= language()->admin_settings->announcements->show_logged_out ?></label>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="email_notifications">

                            <div class="form-group">
                                <label for="email_notifications_emails"><?= language()->admin_settings->email_notifications->emails ?></label>
                                <textarea id="email_notifications_emails" name="email_notifications_emails" class="form-control form-control-lg" rows="5"><?= settings()->email_notifications->emails ?></textarea>
                                <small class="form-text text-muted"><?= language()->admin_settings->email_notifications->emails_help ?></small>
                            </div>

                            <div class="custom-control custom-switch my-3">
                                <input id="email_notifications_new_user" name="email_notifications_new_user" type="checkbox" class="custom-control-input" <?= settings()->email_notifications->new_user ? 'checked' : null?>>
                                <label class="custom-control-label" for="email_notifications_new_user"><?= language()->admin_settings->email_notifications->new_user ?></label>
                                <small class="form-text text-muted"><?= language()->admin_settings->email_notifications->new_user_help ?></small>
                            </div>

                            <div class="custom-control custom-switch my-3">
                                <input id="email_notifications_new_payment" name="email_notifications_new_payment" type="checkbox" class="custom-control-input" <?= settings()->email_notifications->new_payment ? 'checked' : null?>>
                                <label class="custom-control-label" for="email_notifications_new_payment"><?= language()->admin_settings->email_notifications->new_payment ?></label>
                                <small class="form-text text-muted"><?= language()->admin_settings->email_notifications->new_payment_help ?></small>
                            </div>

                            <div class="custom-control custom-switch my-3">
                                <input id="email_notifications_new_domain" name="email_notifications_new_domain" type="checkbox" class="custom-control-input" <?= settings()->email_notifications->new_domain ? 'checked' : null?>>
                                <label class="custom-control-label" for="email_notifications_new_domain"><?= language()->admin_settings->email_notifications->new_domain ?></label>
                                <small class="form-text text-muted"><?= language()->admin_settings->email_notifications->new_domain_help ?></small>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="webhooks">

                            <div class="form-group">
                                <label for="webhooks_user_new"><?= language()->admin_settings->webhooks->user_new ?></label>
                                <input type="url" name="webhooks_user_new" class="form-control form-control-lg" value="<?= settings()->webhooks->user_new ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->webhooks->user_new_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="webhooks_user_delete"><?= language()->admin_settings->webhooks->user_delete ?></label>
                                <input type="url" name="webhooks_user_delete" class="form-control form-control-lg" value="<?= settings()->webhooks->user_delete ?>" />
                                <small class="form-text text-muted"><?= language()->admin_settings->webhooks->user_delete_help ?></small>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="license">
                            <div class="form-group">
                                <label for="license_license"><?= language()->admin_settings->license->license ?></label>
                                <input id="license_license" name="license_license" type="text" class="form-control form-control-lg disabled" value="<?= settings()->license->license ?>" readonly="readonly" />
                                <small class="form-text text-muted"><?= language()->admin_settings->license->license_help ?></small>
                            </div>

                            <div class="form-group">
                                <label for="license_type"><?= language()->admin_settings->license->type ?></label>
                                <input id="license_type" name="license_type" type="text" class="form-control form-control-lg disabled" value="<?= settings()->license->type ?>" readonly="readonly" />
                            </div>

                            <div class="form-group">
                                <label for="license_new_license"><?= language()->admin_settings->license->new_license ?></label>
                                <input id="license_new_license" name="license_new_license" type="text" class="form-control form-control-lg" value="" />
                                <small class="form-text text-muted"><?= language()->admin_settings->license->new_license_help ?></small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="submit" class="btn btn-primary"><?= language()->global->update ?></button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <p class="text-muted my-3"><?= language()->admin_settings->documentation ?></p>
    </div>
</div>


<?php ob_start() ?>
<script>
    'use strict';

    /* SMTP */
    let smtp_auth_handler = () => {
        if(document.querySelector('input[name="smtp_auth"]').checked) {
            document.querySelector('input[name="smtp_username"]').removeAttribute('readonly');
            document.querySelector('input[name="smtp_password"]').removeAttribute('readonly');
        } else {
            document.querySelector('input[name="smtp_username"]').setAttribute('readonly', 'readonly');
            document.querySelector('input[name="smtp_password"]').setAttribute('readonly', 'readonly');
        }
    }

    smtp_auth_handler();
    document.querySelector('input[name="smtp_auth"]').addEventListener('change', smtp_auth_handler);

    /* Captcha */
    let initiate_captcha_type = () => {
        switch(document.querySelector('select[name="captcha_type"]').value) {
            case 'basic':
                document.querySelector('#captcha_hcaptcha').classList.add('d-none');
                document.querySelector('#captcha_recaptcha').classList.add('d-none');
                break;

            case 'recaptcha':
                document.querySelector('#captcha_hcaptcha').classList.add('d-none');
                document.querySelector('#captcha_recaptcha').classList.remove('d-none');
                break;

            case 'hcaptcha':
                document.querySelector('#captcha_hcaptcha').classList.remove('d-none');
                document.querySelector('#captcha_recaptcha').classList.add('d-none');
                break;
        }
    }

    initiate_captcha_type();
    document.querySelector('select[name="captcha_type"]').addEventListener('change', initiate_captcha_type);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
