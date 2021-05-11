<?php
/**
 * HOOK_REGISTRIEREN_PAGE_REGISTRIEREN_PLAUSI
 *
 * We use this hook to save address meta data when the customer
 * is registering himself.
 *
 * Finally we check if there any session that need doAccounting, and send doAccounting if there are.
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

