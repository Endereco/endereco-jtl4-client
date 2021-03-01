<?php
/**
 * HOOK_SMARTY_OUTPUTFILTER
 */

if (version_compare(JTL_VERSION, 400, '>=') && class_exists('Shop')) {
    $smarty = Shop::Smarty();
} else {
    global $smarty;
}

require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
$helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);

// Render custom endereco fields.
if (pq('[name="land"]')->length && pq('[name="strasse"]')->length) {
    $file = $oPlugin->cFrontendPfad . 'template/custom_fields_general.tpl';

    // Check if customer is set.
    if ($_SESSION['Kunde']) {
        $customerId = intval($_SESSION['Kunde']->kKunde);
        if ($customerId) {

            if (pq('[name="lieferdaten"]')->length) {
                $amsmeta = Shop::DB()->queryPrepared(
                    "SELECT `xplugin_endereco_jtl4_client_tams`.*
                    FROM `trechnungsadresse`
                    LEFT JOIN `xplugin_endereco_jtl4_client_tams` ON `xplugin_endereco_jtl4_client_tams`.`kLieferadresse` = `trechnungsadresse`.`kLieferadresse`
                    WHERE `kKunde` = :id",
                    [':id' => $customerId],
                    1
                );
            } else {
                $amsmeta = Shop::DB()->queryPrepared(
                    "SELECT `xplugin_endereco_jtl4_client_tams`.*
                    FROM `xplugin_endereco_jtl4_client_tams`
                    WHERE `kKunde` = :id",
                    [':id' => $customerId],
                    1
                );
            }
            $smarty->assign('endereco_amsts', $amsmeta->enderecoamsts);
            $smarty->assign('endereco_amsstatus', $amsmeta->enderecoamsstatus);
            $smarty->assign('endereco_amspredictions', $amsmeta->enderecoamspredictions);
        }
    } else {
        $smarty->assign('endereco_amsts', '');
        $smarty->assign('endereco_amsstatus', '');
        $smarty->assign('endereco_amspredictions', '');
    }

    $html = $smarty->fetch($file);
    pq('[name="strasse"]')->after($html);
}

if (pq('[name="register[shipping_address][land]"]')->length && pq('[name="register[shipping_address][strasse]"]')->length) {
    $file = $oPlugin->cFrontendPfad . 'template/custom_fields_delivery.tpl';

    // Check if customer is set.
    if ($_SESSION['Kunde']) {
        $customerId = intval($_SESSION['Kunde']->kKunde);
        if ($customerId) {
            $amsmeta = Shop::DB()->queryPrepared(
                "SELECT `xplugin_endereco_jtl4_client_tams`.*
                    FROM `trechnungsadresse`
                    LEFT JOIN `xplugin_endereco_jtl4_client_tams` ON `xplugin_endereco_jtl4_client_tams`.`kLieferadresse` = `trechnungsadresse`.`kLieferadresse`
                    WHERE `kKunde` = :id",
                [':id' => $customerId],
                1
            );
            $smarty->assign('endereco_delivery_amsts', $amsmeta->enderecoamsts);
            $smarty->assign('endereco_delivery_amsstatus', $amsmeta->enderecoamsstatus);
            $smarty->assign('endereco_delivery_amspredictions', $amsmeta->enderecoamspredictions);
        }
    } else {
        $smarty->assign('endereco_delivery_amsts', '');
        $smarty->assign('endereco_delivery_amsstatus', '');
        $smarty->assign('endereco_delivery_amspredictions', '');
    }

    $html = $smarty->fetch($file);
    pq('[name="register[shipping_address][strasse]"]')->after($html);
}

// Render template
$file = $oPlugin->cFrontendPfad . 'template/init_ams.tpl';
$html = $smarty->fetch($file);
// Insert to the bottom.
pq('body')->append($html);

// Render config setup.
$file = $oPlugin->cFrontendPfad . 'template/endereco_config.tpl';
$smarty->assign('endereco_api_url', URL_SHOP . '/io.php?io=endereco_request');
$smarty->assign('endereco_jtl4_client_api_key', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_api_key']);
$smarty->assign('endereco_jtl4_client_show_debug_info', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_show_debug_info']);
$smarty->assign('endereco_jtl4_client_remote_url', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_remote_url']);
$smarty->assign('endereco_jtl4_client_onblur_trigger', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_onblur_trigger']);
$smarty->assign('endereco_jtl4_client_onsubmit_trigger', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_onsubmit_trigger']);
$smarty->assign('endereco_jtl4_client_smart_fill', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_smart_fill']);
$smarty->assign('endereco_jtl4_client_check_existing', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_check_existing']);
$smarty->assign('endereco_jtl4_client_resume_submit', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_resume_submit']);
$smarty->assign('endereco_jtl4_client_use_standart_css', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_use_standart_css']);
$smarty->assign('endereco_jtl4_client_show_email_status', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_show_email_status']);
// Texts
$smarty->assign('endereco_jtl4_client_popup_headline', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_popup_headline']);
$smarty->assign('endereco_jtl4_client_popup_subline', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_popup_subline']);
$smarty->assign('endereco_jtl4_client_your_input', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_your_input']);
$smarty->assign('endereco_jtl4_client_edit_input', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_edit_input']);
$smarty->assign('endereco_jtl4_client_our_suggestions', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_our_suggestions']);
$smarty->assign('endereco_jtl4_client_use_selected', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_use_selected']);
$smarty->assign('endereco_jtl4_client_general_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_general_address']);
$smarty->assign('endereco_jtl4_client_billing_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_billing_address']);
$smarty->assign('endereco_jtl4_client_shipping_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_shipping_address']);

$smarty->assign('endereco_jtl4_client_status_email_not_correct', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_not_correct']);
$smarty->assign('endereco_jtl4_client_status_email_cant_receive', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_cant_receive']);
$smarty->assign('endereco_jtl4_client_status_email_syntax_error', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_syntax_error']);
$smarty->assign('endereco_jtl4_client_status_email_no_mx', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_no_mx']);

$smarty->assign(
    'endereco_jtl4_client_button_html',
    '<button class="btn btn-primary btn-lg" type="button" endereco-use-selection>'.$oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_use_selected'].'</button>'
);

$smarty->assign('endereco_jtl4_client_ams_active', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_ams_active']);
$smarty->assign('endereco_jtl4_client_es_active', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_es_active']);
$smarty->assign('endereco_jtl4_client_ps_active', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_ps_active']);

$smarty->assign('endereco_jtl4_client_color_1', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_color_1']);
$smarty->assign('endereco_jtl4_client_color_2', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_color_2']);
$smarty->assign('endereco_jtl4_client_color_3', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_color_3']);

list($red, $green, $blue) = $helper->hex2rgb($oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_color_1']);
$smarty->assign('endereco_jtl4_client_color_1_bg', "rgba($red, $green, $blue, 0.1)");
list($red, $green, $blue) = $helper->hex2rgb($oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_color_2']);
$smarty->assign('endereco_jtl4_client_color_2_bg', "rgba($red, $green, $blue, 0.125)");
list($red, $green, $blue) = $helper->hex2rgb($oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_color_3']);
$smarty->assign('endereco_jtl4_client_color_3_bg', "rgba($red, $green, $blue, 0.125)");

$templateName = Template::getInstance()->getDir();
$smarty->assign('endereco_theme_name', strtolower($templateName));

$html = $smarty->fetch($file);

// Insert to the bottom.
pq('head')->append($html);

// Insert fake address data.
if (!empty($GLOBALS['enderecoShowCheckForExistingAddress']) && $GLOBALS['enderecoShowCheckForExistingAddress']) {

    // Load billing address data.
    if ($_SESSION['Kunde']) {
        $customerId = intval($_SESSION['Kunde']->kKunde);
        if ($customerId) {
            $amsmeta = Shop::DB()->queryPrepared(
                "SELECT `xplugin_endereco_jtl4_client_tams`.*
                    FROM `xplugin_endereco_jtl4_client_tams`
                    WHERE `kKunde` = :id",
                [':id' => $customerId],
                1
            );
            $smarty->assign('bendereco_amsts', $amsmeta->enderecoamsts);
            $smarty->assign('bendereco_amsstatus', $amsmeta->enderecoamsstatus);
            $smarty->assign('bendereco_amspredictions', $amsmeta->enderecoamspredictions);
        }
    } else {
        $smarty->assign('bendereco_amsts', '');
        $smarty->assign('bendereco_amsstatus', '');
        $smarty->assign('bendereco_amspredictions', '');
    }
    // Load shipping address data.
    if ($_SESSION['Lieferadresse']) {
        $shippingAddressId = intval($_SESSION['Lieferadresse']->kLieferadresse);
        if ($shippingAddressId) {
            $amsmeta = Shop::DB()->queryPrepared(
                "SELECT `xplugin_endereco_jtl4_client_tams`.*
                    FROM `xplugin_endereco_jtl4_client_tams`
                    WHERE `kLieferadresse` = :id",
                [':id' => $shippingAddressId],
                1
            );
            $smarty->assign('sendereco_amsts', $amsmeta->enderecoamsts);
            $smarty->assign('sendereco_amsstatus', $amsmeta->enderecoamsstatus);
            $smarty->assign('sendereco_amspredictions', $amsmeta->enderecoamspredictions);
        }
    } else {
        $smarty->assign('sendereco_amsts', '');
        $smarty->assign('sendereco_amsstatus', '');
        $smarty->assign('sendereco_amspredictions', '');
    }

    $file = $oPlugin->cFrontendPfad . 'template/inject_endereco_ams_for_existingaddress.tpl';
    $html = $smarty->fetch($file);
    pq('body')->append($html);
}

