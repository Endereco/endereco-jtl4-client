<?php

/**
 * HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE
 *
 * Customer enters new delivery address. We send doAccountings immediately and save the metadata
 * for later. As the address is probably saved later too.
 *
 * We save the metadata to database when the order is created.
 */

if (isset($_POST['lieferdaten']) && intval($_POST['lieferdaten']) === 1 && isset($_POST['land'])) {

    // Save meta data for later use.
    $_SESSION['EnderecoShippingAddressMeta'] = array(
        'enderecoamsts' => $_POST['enderecoamsts'],
        'enderecoamsstatus' => $_POST['enderecoamsstatus'],
        'enderecoamspredictions' => $_POST['enderecoamspredictions'],
    );

    // Send do Accounting.
    require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
    $helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);
    $helper->doAccountings(
        $helper->findSessions()
    );
}
