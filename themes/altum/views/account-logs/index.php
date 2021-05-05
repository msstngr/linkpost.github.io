<?php defined('ALTUMCODE') || die() ?>

<header class="header pb-0">
    <div class="container">
        <?= $this->views['account_header'] ?>
    </div>
</header>

<section class="container pt-5">

    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(count($data->logs)): ?>
        <h2 class="h4"><?= language()->account_logs->header ?></h2>
        <p class="text-muted"><?= language()->account_logs->subheader ?></p>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= language()->account_logs->logs->type ?></th>
                    <th><?= language()->account_logs->logs->ip ?></th>
                    <th><?= language()->account_logs->logs->date ?></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->logs as $row): ?>
                    <tr>
                        <td><?= $row->type ?></td>
                        <td><?= $row->ip ?></td>
                        <td><span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->date, 1) ?>"><?= \Altum\Date::get_timeago($row->date) ?></span></td>
                    </tr>
                <?php endforeach ?>

                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>
        <div class="d-flex flex-column align-items-center justify-content-center">
            <img src="<?= SITE_URL . ASSETS_URL_PATH . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= language()->account_logs->logs->no_data ?>" />
            <h2 class="h4 text-muted mt-3"><?= language()->account_logs->logs->no_data ?></h2>
            <p class="text-muted"><?= language()->account_logs->logs->no_data_help ?></p>
        </div>
    <?php endif ?>

</section>
