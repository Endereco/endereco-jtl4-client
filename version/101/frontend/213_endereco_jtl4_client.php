<?php
/**
 * HOOK_IO_HANDLE_REQUEST
 *
 * We use io.php to proxy requests through onlineshop, stripping customers browser fingerprint and ip address.
 *
 * 'endereco_request' is used for proxiing *
 * 'endereco_inner_request' is used for requests to the onlineshop itself, e.g. to create or edit addresses.
 */

if ('endereco_request' === $_REQUEST['io']) {
    if ('GET' === $_SERVER['REQUEST_METHOD']) {
        die('We expect a POST request here.');
    }

    $agent_info  = "Endereco JTL4 Client v" . $oPlugin->nVersion;
    $post_data   = json_decode(file_get_contents('php://input'), true);
    $api_key     = trim($_SERVER['HTTP_X_AUTH_KEY']);
    $data_string = json_encode($post_data);
    $ch          = curl_init(trim($_SERVER['HTTP_X_REMOTE_API_URL']));

    if ($_SERVER['HTTP_X_TRANSACTION_ID']) {
        $tid = $_SERVER['HTTP_X_TRANSACTION_ID'];
    } else {
        $tid = 'not_set';
    }

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'X-Auth-Key: ' . $api_key,
            'X-Transaction-Id: ' . $tid,
            'X-Agent: ' . $agent_info,
            'X-Transaction-Referer: ' . $_SERVER['HTTP_X_TRANSACTION_REFERER'],
            'Content-Length: ' . strlen($data_string))
    );

    $result = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: application/json');
    echo $result;
    exit();
}

if ('endereco_inner_request' === $_REQUEST['io']) {
    if ('GET' === $_SERVER['REQUEST_METHOD']) {
        die('We expect a POST request here.');
    }

    $post_data   = json_decode(file_get_contents('php://input'), true);

    if ('editBillingAddress' === $post_data['method']) {

        // Update in DB.
        if (!empty($post_data['params']['customerId'])) {
            // Change customer address.
            $Kunde = new Kunde(intval($post_data['params']['customerId']));
            $Kunde->cStrasse      = (isset($post_data['params']['address']['streetName'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['streetName'])) : $Kunde->cStrasse;
            $Kunde->cHausnummer   = (isset($post_data['params']['address']['buildingNumber'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['buildingNumber'])) : $Kunde->cHausnummer;
            $Kunde->cAdressZusatz = (isset($post_data['params']['address']['additionalInfo'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['additionalInfo'])) : $Kunde->cAdressZusatz;
            $Kunde->cPLZ          = (isset($post_data['params']['address']['postalCode'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['postalCode'])) : $Kunde->cPLZ;
            $Kunde->cOrt          = (isset($post_data['params']['address']['locality'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['locality'])) : $Kunde->cOrt;
            $Kunde->cLand         = (isset($post_data['params']['address']['countryCode'])) ? strtoupper(StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['countryCode']))) : $Kunde->cLand;
            $Kunde->updateInDB();
            Kundendatenhistory::saveHistory($_SESSION['Kunde'], $Kunde, Kundendatenhistory::QUELLE_BESTELLUNG);
            $_SESSION['Kunde'] = new Kunde($Kunde->kKunde);

            // Save meta.
            Shop::DB()->queryPrepared(
                "INSERT INTO `xplugin_endereco_jtl4_client_tams` 
                    (`kKunde`, `kRechnungsadresse`, `kLieferadresse`, `enderecoamsts`, `enderecoamsstatus`, `enderecoamspredictions`, `last_change_at`)
                 VALUES 
                    (:kKunde, NULL,  NULL, :enderecoamsts, :enderecoamsstatus, :enderecoamspredictions, now())
                ON DUPLICATE KEY UPDATE    
                   `kKunde`=:kKunde2, `enderecoamsts`=:enderecoamsts2, `enderecoamsstatus`=:enderecoamsstatus2, `enderecoamspredictions`=:enderecoamspredictions2, `last_change_at`=now()
                ",
                [
                    ':kKunde' => intval($post_data['params']['customerId']),
                    ':enderecoamsts' => utf8_decode(StringHandler::filterXSS($post_data['params']['enderecometa']['ts'])),
                    ':enderecoamsstatus' => implode(',', StringHandler::filterXSS($post_data['params']['enderecometa']['status'])),
                    ':enderecoamspredictions' => utf8_decode(json_encode(StringHandler::filterXSS($post_data['params']['enderecometa']['predictions']))),
                    ':kKunde2' => intval($post_data['params']['customerId']),
                    ':enderecoamsts2' => utf8_decode(StringHandler::filterXSS($post_data['params']['enderecometa']['ts'])),
                    ':enderecoamsstatus2' => implode(',', StringHandler::filterXSS($post_data['params']['enderecometa']['status'])),
                    ':enderecoamspredictions2' => utf8_decode(json_encode(StringHandler::filterXSS($post_data['params']['enderecometa']['predictions']))),
                ],
                1
            );
        }

        // Update in session.
        if (!empty($_SESSION['Kunde'])) {
            $_SESSION['Kunde']->cStrasse      = (isset($post_data['params']['address']['streetName'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['streetName'])) : $Kunde->cStrasse;
            $_SESSION['Kunde']->cHausnummer   = (isset($post_data['params']['address']['buildingNumber'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['buildingNumber'])) : $Kunde->cHausnummer;
            $_SESSION['Kunde']->cAdressZusatz = (isset($post_data['params']['address']['additionalInfo'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['additionalInfo'])) : $Kunde->cAdressZusatz;
            $_SESSION['Kunde']->cPLZ          = (isset($post_data['params']['address']['postalCode'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['postalCode'])) : $Kunde->cPLZ;
            $_SESSION['Kunde']->cOrt          = (isset($post_data['params']['address']['locality'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['locality'])) : $Kunde->cOrt;
            $_SESSION['Kunde']->cLand         = (isset($post_data['params']['address']['countryCode'])) ? strtoupper(StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['countryCode']))) : $Kunde->cLand;
        }

        $_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'] = implode(',', $post_data['params']['enderecometa']['status']);
        $_SESSION['EnderecoBillingAddressMeta']['enderecoamspredictions'] = json_encode($post_data['params']['enderecometa']['predictions']);
        $_SESSION['EnderecoBillingAddressMeta']['enderecoamsts'] = $post_data['params']['enderecometa']['ts'];

        if ($post_data['params']['copyShippingToo']) {
            $_SESSION['Lieferadresse']->cStrasse      = (isset($post_data['params']['address']['streetName'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['streetName'])) : $Kunde->cStrasse;
            $_SESSION['Lieferadresse']->cHausnummer   = (isset($post_data['params']['address']['buildingNumber'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['buildingNumber'])) : $Kunde->cHausnummer;
            $_SESSION['Lieferadresse']->cAdressZusatz = (isset($post_data['params']['address']['additionalInfo'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['additionalInfo'])) : $Kunde->cAdressZusatz;
            $_SESSION['Lieferadresse']->cPLZ          = (isset($post_data['params']['address']['postalCode'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['postalCode'])) : $Kunde->cPLZ;
            $_SESSION['Lieferadresse']->cOrt          = (isset($post_data['params']['address']['locality'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['locality'])) : $Kunde->cOrt;
            $_SESSION['Lieferadresse']->cLand         = (isset($post_data['params']['address']['countryCode'])) ? strtoupper(StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['countryCode']))) : $Kunde->cLand;

            $_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'] = implode(',', $post_data['params']['enderecometa']['status']);
            $_SESSION['EnderecoShippingAddressMeta']['enderecoamspredictions'] = json_encode($post_data['params']['enderecometa']['predictions']);
            $_SESSION['EnderecoShippingAddressMeta']['enderecoamsts'] = $post_data['params']['enderecometa']['ts'];
        }
    }

    if ('editShippingAddress' === $post_data['method']) {

        if (!empty($post_data['params']['shippingAddressId'])) {
            // Change customer address.
            $originalId = intval($post_data['params']['shippingAddressId']);
            $Lieferadresse = new Lieferadresse(intval($post_data['params']['shippingAddressId']));

            // Get all shipping addresses.
            $tlieferadressen = Shop::DB()->queryPrepared(
                "SELECT `tlieferadresse`.*
            FROM `tlieferadresse`
            WHERE `tlieferadresse`.`kKunde` = :kKunde",
                [':kKunde' => $_SESSION['Kunde']->kKunde],
                9
            );
            $sameAddress = null;
            foreach ($tlieferadressen as $delivery_address) {
                $tmp_address = [
                    'cVorname' => trim($delivery_address['cVorname']),
                    'cNachname' => trim(entschluesselXTEA($delivery_address['cNachname'])),
                    'cStrasse' => trim(entschluesselXTEA($delivery_address['cStrasse'])),
                    'cHausnummer' => trim($delivery_address['cHausnummer']),
                    'cAdressZusatz' => trim($delivery_address['cAdressZusatz']),
                    'cPLZ' => trim($delivery_address['cPLZ']),
                    'cOrt' => trim($delivery_address['cOrt']),
                    'cLand' => trim($delivery_address['cLand'])
                ];

                if (
                    ($Lieferadresse->cVorname == $tmp_address['cVorname']) &&
                    ($Lieferadresse->cNachname == $tmp_address['cNachname']) &&
                    (utf8_decode(StringHandler::filterXSS($post_data['params']['address']['streetName'])) == $tmp_address['cStrasse']) &&
                    (StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['buildingNumber'])) == $tmp_address['cHausnummer']) &&
                    (StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['additionalInfo'])) == $tmp_address['cAdressZusatz'] )&&
                    (StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['postalCode'])) == $tmp_address['cPLZ']) &&
                    (utf8_decode(StringHandler::filterXSS($post_data['params']['address']['locality'])) == $tmp_address['cOrt']) &&
                    (strtoupper(StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['countryCode']))) == $tmp_address['cLand'])
                ) {
                    $sameAddress = $delivery_address;
                    break;
                }
            }

            if ($sameAddress) {
                $Lieferadresse = new Lieferadresse($sameAddress['kLieferadresse']);
            } else {
                $Lieferadresse->cStrasse      = (isset($post_data['params']['address']['streetName'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['streetName'])) : $Lieferadresse->cStrasse;
                $Lieferadresse->cHausnummer   = (isset($post_data['params']['address']['buildingNumber'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['buildingNumber'])) : $Lieferadresse->cHausnummer;
                $Lieferadresse->cAdressZusatz = (isset($post_data['params']['address']['additionalInfo'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['additionalInfo'])) : $Lieferadresse->cAdressZusatz;
                $Lieferadresse->cPLZ          = (isset($post_data['params']['address']['postalCode'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['postalCode'])) : $Lieferadresse->cPLZ;
                $Lieferadresse->cOrt          = (isset($post_data['params']['address']['locality'])) ? utf8_decode(StringHandler::filterXSS($post_data['params']['address']['locality'])) : $Lieferadresse->cOrt;
                $Lieferadresse->cLand         = (isset($post_data['params']['address']['countryCode'])) ? strtoupper(StringHandler::htmlentities(StringHandler::filterXSS($post_data['params']['address']['countryCode']))) : $Lieferadresse->cLand;
                $Lieferadresse->insertInDB();
                $Lieferadresse = new Lieferadresse($Lieferadresse->kLieferadresse);
            }

            // Save meta.
            Shop::DB()->queryPrepared(
                "INSERT INTO `xplugin_endereco_jtl4_client_tams` 
                    (`kKunde`, `kRechnungsadresse`, `kLieferadresse`, `enderecoamsts`, `enderecoamsstatus`, `enderecoamspredictions`, `last_change_at`)
                 VALUES 
                    ( NULL,  NULL, :kLieferadresse, :enderecoamsts, :enderecoamsstatus, :enderecoamspredictions, now())
                ON DUPLICATE KEY UPDATE    
                   `kLieferadresse`=:kLieferadresse2, `enderecoamsts`=:enderecoamsts2, `enderecoamsstatus`=:enderecoamsstatus2, `enderecoamspredictions`=:enderecoamspredictions2, `last_change_at`=now()
                ",
                [
                    ':kLieferadresse' => $Lieferadresse->kLieferadresse,
                    ':enderecoamsts' => utf8_decode(StringHandler::filterXSS($post_data['params']['enderecometa']['ts'])),
                    ':enderecoamsstatus' => implode(',', StringHandler::filterXSS($post_data['params']['enderecometa']['status'])),
                    ':enderecoamspredictions' => utf8_decode(json_encode(StringHandler::filterXSS($post_data['params']['enderecometa']['predictions']))),
                    ':kLieferadresse2' => $Lieferadresse->kLieferadresse,
                    ':enderecoamsts2' => utf8_decode(StringHandler::filterXSS($post_data['params']['enderecometa']['ts'])),
                    ':enderecoamsstatus2' => implode(',', StringHandler::filterXSS($post_data['params']['enderecometa']['status'])),
                    ':enderecoamspredictions2' => utf8_decode(json_encode(StringHandler::filterXSS($post_data['params']['enderecometa']['predictions']))),
                ],
                1
            );

            $_SESSION['Lieferadresse'] = $Lieferadresse;
            $_SESSION['Warenkorb']->kLieferadresse = $_SESSION['Lieferadresse']->kLieferadresse;
            $_SESSION['Bestellung']->kLieferadresse = $_SESSION['Lieferadresse']->kLieferadresse;
        }
    }

    exit();
}

