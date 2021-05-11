<?php
/**
 * HOOK_BESTELLVORGANG_PAGE
 *
 * When customers logins in the checkout we check all his addresses, that haven't been checked yet.
 * Its important to do it, before he land on the order confirmation page.
 */

if (isset($_POST['login']) && intval($_POST['login']) === 1) {

    // Check if customer is set.
    if (
        $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_check_existing'] && (
            (isset($_POST['userLogin']) && isset($_POST['passLogin'])) ||
            (isset($_POST['email']) && isset($_POST['passwort']))
        )
    ) {
        if ($_SESSION['Kunde']) {
            // Check his invoice and delivery addresses, if needed.
            require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
            $helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);
            $helper->checkCustomersAddresses($_SESSION['Kunde']->kKunde);
        }
    }

}


