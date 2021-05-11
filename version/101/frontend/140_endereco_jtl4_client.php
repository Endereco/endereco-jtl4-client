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
    if ($_SESSION['Kunde'] && !empty($_SESSION['Kunde']->kKunde)) {
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
        if (!empty($_SESSION['EnderecoBillingAddressMeta'])) {
            $smarty->assign('endereco_amsts', $_SESSION['EnderecoBillingAddressMeta']['enderecoamsts']);
            $smarty->assign('endereco_amsstatus', $_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus']);
            $smarty->assign('endereco_amspredictions', $_SESSION['EnderecoBillingAddressMeta']['enderecoamspredictions']);
        } else {
            $smarty->assign('endereco_amsts', '');
            $smarty->assign('endereco_amsstatus', '');
            $smarty->assign('endereco_amspredictions', '');
        }
    }

    $html = $smarty->fetch($file);
    pq('[name="strasse"]')->after($html);
}

if (pq('[name="register[shipping_address][land]"]')->length && pq('[name="register[shipping_address][strasse]"]')->length) {
    $file = $oPlugin->cFrontendPfad . 'template/custom_fields_delivery.tpl';

    // Check if customer is set.
    if ($_SESSION['Kunde'] && !empty($_SESSION['Kunde']->kKunde)) {
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
        if (!empty($_SESSION['EnderecoShippingAddressMeta'])) {
            $smarty->assign('endereco_delivery_amsts', $_SESSION['EnderecoShippingAddressMeta']['enderecoamsts']);
            $smarty->assign('endereco_delivery_amsstatus', $_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus']);
            $smarty->assign('endereco_delivery_amspredictions', $_SESSION['EnderecoShippingAddressMeta']['enderecoamspredictions']);
        } else {
            $smarty->assign('endereco_delivery_amsts', '');
            $smarty->assign('endereco_delivery_amsstatus', '');
            $smarty->assign('endereco_delivery_amspredictions', '');
        }
        $smarty->assign('endereco_delivery_amsts', '');
        $smarty->assign('endereco_delivery_amsstatus', '');
        $smarty->assign('endereco_delivery_amspredictions', '');
    }

    $html = $smarty->fetch($file);
    pq('[name="register[shipping_address][strasse]"]')->after($html);
}

// Call init ams.
$file = $oPlugin->cFrontendPfad . 'template/init_ams.tpl';
$html = $smarty->fetch($file);
// Insert to the bottom.
pq('body')->append($html);

// Add js loader
$file = $oPlugin->cFrontendPfad . 'template/load_endereco_js.tpl';
$plugin_version = $oPlugin->nVersion;
$smarty->assign('endereco_js_file_path', URL_SHOP . '/includes/plugins/endereco_jtl4_client/version/'.$plugin_version.'/frontend/js/endereco.min.js');
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
$smarty->assign('endereco_jtl4_client_allow_close_modal', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_allow_close_modal']);
$smarty->assign('endereco_jtl4_client_resume_submit', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_resume_submit']);
$smarty->assign('endereco_jtl4_client_use_standart_css', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_use_standart_css']);
$smarty->assign('endereco_jtl4_client_show_email_status', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_show_email_status']);
$smarty->assign('endereco_jtl4_client_confirm_with_checkbox', $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_confirm_with_checkbox']);

// Texts
$smarty->assign('endereco_jtl4_client_popup_headline', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_popup_headline']);
$smarty->assign('endereco_jtl4_client_popup_subline', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_popup_subline']);
$smarty->assign('endereco_jtl4_client_mistake_no_predictions_subline', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_mistake_no_predictions_subline']);
$smarty->assign('endereco_jtl4_client_confirm_my_address_checkout', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_confirm_my_address_checkout']);
$smarty->assign('endereco_jtl4_client_popup_subline_not_found', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_popup_subline_not_found']);
$smarty->assign('endereco_jtl4_client_your_input', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_your_input']);
$smarty->assign('endereco_jtl4_client_edit_input', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_edit_input']);
$smarty->assign('endereco_jtl4_client_our_suggestions', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_our_suggestions']);

$smarty->assign('endereco_jtl4_client_use_selected', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_use_selected']);
$smarty->assign('endereco_jtl4_client_edit_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_edit_address']);
$smarty->assign('endereco_jtl4_client_confirm_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_confirm_address']);

$smarty->assign('endereco_jtl4_client_general_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_general_address']);
$smarty->assign('endereco_jtl4_client_billing_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_billing_address']);
$smarty->assign('endereco_jtl4_client_shipping_address', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_shipping_address']);

$smarty->assign('endereco_jtl4_client_status_email_not_correct', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_not_correct']);
$smarty->assign('endereco_jtl4_client_status_email_cant_receive', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_cant_receive']);
$smarty->assign('endereco_jtl4_client_status_email_syntax_error', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_syntax_error']);
$smarty->assign('endereco_jtl4_client_status_email_no_mx', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_email_no_mx']);

$smarty->assign('endereco_jtl4_client_status_error_building_number_is_missing', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_error_building_number_is_missing']);
$smarty->assign('endereco_jtl4_client_status_error_building_number_not_found', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_error_building_number_not_found']);
$smarty->assign('endereco_jtl4_client_status_error_street_name_needs_correction', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_error_street_name_needs_correction']);
$smarty->assign('endereco_jtl4_client_status_error_locality_needs_correction', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_error_locality_needs_correction']);
$smarty->assign('endereco_jtl4_client_status_error_postal_code_needs_correction', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_error_postal_code_needs_correction']);
$smarty->assign('endereco_jtl4_client_status_error_country_code_needs_correction', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_status_error_country_code_needs_correction']);

$smarty->assign('endereco_jtl4_client_faulty_address_warning', $oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_faulty_address_warning']);

$smarty->assign(
    'endereco_jtl4_client_button_html',
    '<button class="btn btn-primary btn-lg" type="button" endereco-use-selection>'.$oPlugin->oPluginSprachvariableAssoc_arr['endereco_jtl4_client_use_selected'].'</button>'
);

$smarty->assign(
    'endereco_jtl4_client_button_edit_html',
    '<button class="btn btn-primary btn-lg" type="button" endereco-edit-address>Edit address</button>'
);

$smarty->assign(
    'endereco_jtl4_client_button_confirm_html',
    '<button class="btn btn-secondary btn-lg" type="button" endereco-confirm-address>Confirm address</button>'
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

// Get country mapping.
$countires = Shop::DB()->queryPrepared(
    "SELECT *
FROM `tland`",
    [],
    2
);
$countryMapping = [];
foreach ($countires as $country) {
    if (!empty($_SESSION['cISOSprache']) && 'ger' === $_SESSION['cISOSprache']) {
        $countryMapping[$country->cISO] = $country->cDeutsch;
    } else {
        $countryMapping[$country->cISO] = $country->cEnglisch;
    }
}
$countryMapping = $helper->utf8_encode_array($countryMapping);

$smarty->assign('endereco_jtl4_client_country_mapping', str_replace('\'', '\\\'', json_encode($countryMapping)));

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
        } else if (!empty($_SESSION['EnderecoBillingAddressMeta']) && !empty($_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'])) {
            $smarty->assign('bendereco_amsts', $_SESSION['EnderecoBillingAddressMeta']['enderecoamsts']);
            $smarty->assign('bendereco_amsstatus', $_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus']);
            $smarty->assign('bendereco_amspredictions', $_SESSION['EnderecoBillingAddressMeta']['enderecoamspredictions']);
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
        } else if (!empty($_SESSION['EnderecoShippingAddressMeta']) && !empty($_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'])) {
            $smarty->assign('sendereco_amsts', $_SESSION['EnderecoShippingAddressMeta']['enderecoamsts']);
            $smarty->assign('sendereco_amsstatus', $_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus']);
            $smarty->assign('sendereco_amspredictions', $_SESSION['EnderecoShippingAddressMeta']['enderecoamspredictions']);
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

