<?php defined('ALTUMCODE') || die() ?>

<?php

$vcard = new JeroenDesloovere\VCard\VCard();

$vcard->addName($data->link->settings->last_name, $data->link->settings->first_name);
$vcard->addAddress(null, null, $data->link->settings->street, $data->link->settings->city, $data->link->settings->region, $data->link->settings->zip, $data->link->settings->country);
$vcard->addPhoneNumber($data->link->settings->phone);
$vcard->addEmail($data->link->settings->email);
$vcard->addURL($data->link->settings->website);
$vcard->addNote($data->link->settings->note);
?>

<div data-link-id="<?= $data->link->link_id ?>" class="col-12 my-3">
    <a href="data:text/plain;charset=UTF-8,<?= $vcard->getOutput() ?>" download="contact.vcf" class="btn btn-block btn-primary link-btn <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>">
        <?php if($data->link->settings->icon): ?>
            <i class="<?= $data->link->settings->icon ?> mr-1"></i>
        <?php endif ?>

        <?= $data->link->settings->name ?>
    </a>
</div>

