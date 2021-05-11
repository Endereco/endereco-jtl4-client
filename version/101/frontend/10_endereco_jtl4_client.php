<?php
/**
 * HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG
 *
 * In this hook we just set a global valiable to display helping form.
 */
$GLOBALS['enderecoShowCheckForExistingAddress'] = ('on' === $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_check_existing']);

$checkPaypalExpressCustomer = ('on' === $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_check_paypalexpress']);

// If its a paypal express payment, and its allowed to check those -> check and do accounting.
if ($checkPaypalExpressCustomer
    && $GLOBALS['enderecoShowCheckForExistingAddress']
    && empty($_SESSION['EnderecoBillingAddressMeta'])
    && (strpos($_SESSION['Zahlungsart']->cModulId, 'paypalexpress') !== false)
) {

    // Send do Accounting.
    require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
    $helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);

    // We only check delivery address.
    $metaBilling = $helper->checkAddress($_SESSION['Kunde']);

    // Save meta to SESSION
    $_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'] = implode(',', $metaBilling['status']);
    $_SESSION['EnderecoBillingAddressMeta']['enderecoamspredictions'] = json_encode($helper->utf8_encode_array($metaBilling['predictions']));
    $_SESSION['EnderecoBillingAddressMeta']['enderecoamsts'] = $metaBilling['ts'];

    // Save meta to SESSION
    $_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'] = implode(',', $metaBilling['status']);
    $_SESSION['EnderecoShippingAddressMeta']['enderecoamspredictions'] = json_encode($helper->utf8_encode_array($metaBilling['predictions']));
    $_SESSION['EnderecoShippingAddressMeta']['enderecoamsts'] = $metaBilling['ts'];
}
