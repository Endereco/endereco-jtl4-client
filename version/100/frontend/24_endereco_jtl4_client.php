<?php
/**
 * HOOK_JTL_PAGE_REDIRECT
 *
 * When customer logins through normal login, we check all his addresses, that haven't been checked yet.
 */

if (
    $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_check_existing'] && (
        isset($_POST['login']) && intval($_POST['login']) === 1 && isset($_POST['email']) && isset($_POST['passwort'])
    )
) {

    // Check if customer is set.
    if ($_SESSION['Kunde']) {
        require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
        $helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);
        $helper->checkCustomersAddresses($_SESSION['Kunde']->kKunde);
    }
}
