<?php

/**
 * HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN_PLAUSI
 *
 * This hook is called after a guest customer is set in session variable. At this point we would set address meta
 * in the session too, and send doAccountings. *
 */

if (isset($args_arr) && 1 === $args_arr['nReturnValue']) {
    // Save meta data for later use.
    $_SESSION['EnderecoBillingAddressMeta'] = array(
        'enderecoamsts' => $_POST['enderecoamsts'],
        'enderecoamsstatus' => $_POST['enderecoamsstatus'],
        'enderecoamspredictions' => $_POST['enderecoamspredictions'],
    );

    if ($_POST['enderecodeliveryamsstatus']) {
        $_SESSION['EnderecoShippingAddressMeta'] = array(
            'enderecoamsts' => $_POST['enderecodeliveryamsts'],
            'enderecoamsstatus' => $_POST['enderecodeliveryamsstatus'],
            'enderecoamspredictions' => $_POST['enderecodeliveryamspredictions'],
        );
    }

    // Send do Accounting.
    require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
    $helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);
    $helper->doAccountings(
        $helper->findSessions()
    );
}
