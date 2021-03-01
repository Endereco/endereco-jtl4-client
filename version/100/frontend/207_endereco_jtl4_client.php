<?php
/**
 * HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE
 *
 * When the order is created, we check if there are any saved address metadata. If there are - we save them to database.
 */
if (isset($args_arr) && $args_arr['oBestellung']) {
    // Save billig naddress result.
    if ($_SESSION['EnderecoBillingAddressMeta']) {
        Shop::DB()->queryPrepared(
            "INSERT INTO `xplugin_endereco_jtl4_client_tams` 
                    (`kKunde`, `kRechnungsadresse`, `kLieferadresse`, `enderecoamsts`, `enderecoamsstatus`, `enderecoamspredictions`, `last_change_at`)
                 VALUES 
                    (:kKunde, NULL, NULL, :enderecoamsts, :enderecoamsstatus, :enderecoamspredictions, now())
                ON DUPLICATE KEY UPDATE    
                   `kKunde`=:kKunde2, `enderecoamsts`=:enderecoamsts2, `enderecoamsstatus`=:enderecoamsstatus2, `enderecoamspredictions`=:enderecoamspredictions2, `last_change_at`=now()
                ",
            [
                ':kKunde' => $args_arr['oBestellung']->kKunde,
                ':enderecoamsts' => $_SESSION['EnderecoBillingAddressMeta']['enderecoamsts'],
                ':enderecoamsstatus' => $_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'],
                ':enderecoamspredictions' => $_SESSION['EnderecoBillingAddressMeta']['enderecoamspredictions'],
                ':kKunde2' => $args_arr['oBestellung']->kKunde,
                ':enderecoamsts2' => $_SESSION['EnderecoBillingAddressMeta']['enderecoamsts'],
                ':enderecoamsstatus2' => $_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'],
                ':enderecoamspredictions2' => $_SESSION['EnderecoBillingAddressMeta']['enderecoamspredictions'],
            ],
            1
        );
        unset($_SESSION['EnderecoBillingAddressMeta']);
    }

    // Save delivery address result.
    if ($_SESSION['EnderecoShippingAddressMeta']) {
        Shop::DB()->queryPrepared(
            "INSERT INTO `xplugin_endereco_jtl4_client_tams` 
                    (`kKunde`, `kRechnungsadresse`, `kLieferadresse`, `enderecoamsts`, `enderecoamsstatus`, `enderecoamspredictions`, `last_change_at`)
                 VALUES 
                    ( NULL, NULL, :kLieferadresse,:enderecoamsts, :enderecoamsstatus, :enderecoamspredictions, now())
                ON DUPLICATE KEY UPDATE    
                   `kLieferadresse`=:kLieferadresse2, `enderecoamsts`=:enderecoamsts2, `enderecoamsstatus`=:enderecoamsstatus2, `enderecoamspredictions`=:enderecoamspredictions2, `last_change_at`=now()
                ",
            [
                ':kLieferadresse' => $args_arr['oBestellung']->kLieferadresse,
                ':enderecoamsts' => $_SESSION['EnderecoShippingAddressMeta']['enderecoamsts'],
                ':enderecoamsstatus' => $_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'],
                ':enderecoamspredictions' => $_SESSION['EnderecoShippingAddressMeta']['enderecoamspredictions'],
                ':kLieferadresse2' => $args_arr['oBestellung']->kLieferadresse,
                ':enderecoamsts2' => $_SESSION['EnderecoShippingAddressMeta']['enderecoamsts'],
                ':enderecoamsstatus2' => $_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'],
                ':enderecoamspredictions2' => $_SESSION['EnderecoShippingAddressMeta']['enderecoamspredictions'],
            ],
            1
        );
        unset($_SESSION['EnderecoShippingAddressMeta']);
    }


    /**
     * Write check details to the order entry in the database, so it can be exported to jtl wawi.
     */
    if ($oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_ams_to_comment']) {
        require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
        $helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $comment = $args_arr['oBestellung']->cKommentar;
        $shipping_meta = null;
        $shipping_address = null;
        if ($args_arr['oBestellung']->kLieferadresse) {
            $shipping_meta = Shop::DB()->queryPrepared(
                "SELECT `xplugin_endereco_jtl4_client_tams`.*
                    FROM `xplugin_endereco_jtl4_client_tams`
                    WHERE `kLieferadresse` = :id",
                [':id' => $args_arr['oBestellung']->kLieferadresse],
                1
            );
            $shipping_address = new Lieferadresse($args_arr['oBestellung']->kLieferadresse);
        } else if ($args_arr['oBestellung']->kKunde) {
            $shipping_meta = Shop::DB()->queryPrepared(
                "SELECT `xplugin_endereco_jtl4_client_tams`.*
                    FROM `xplugin_endereco_jtl4_client_tams`
                    WHERE `kKunde` = :id",
                [':id' => $args_arr['oBestellung']->kKunde],
                1
            );
            $shipping_address = new Rechnungsadresse($args_arr['oBestellung']->kRechnungsadresse);
        }

        if ($shipping_meta) {
            // Predictions
            $predictions = $helper->utf8_decode_array(json_decode($shipping_meta->enderecoamspredictions, true));

            // A a new line, if comment already has text.
            if ('' !== $comment) {
                $comment .= PHP_EOL;
            }

            // Update comment.
            $comment .= "Lieferadresse: ";
            $comment .= str_replace(
                    array(
                        ',',
                        'address_correct',
                        'address_needs_correction',
                        'address_not_found',
                        'address_selected_by_customer',
                        'address_multiple_variants',
                        'address_is_packstation'
                    ), array(
                    ', ',
                    'korrekt',
                    'korrekturbedürftig',
                    'unbekannt',
                    'manuell ausgewählt',
                    'mehrdeutig',
                    'Packstation'
                ),
                    $shipping_meta->enderecoamsstatus
            ) . PHP_EOL;

            if ((strpos($shipping_meta->enderecoamsstatus, 'address_needs_correction') !== false) && $predictions[0]) {
                if ($shipping_address->cStrasse !== $predictions[0]['streetName']) {
                    $comment .= $shipping_address->cStrasse . ' -> ' . $predictions[0]['streetName'] . PHP_EOL;
                }
                if ($shipping_address->cPLZ !== $predictions[0]['postalCode']) {
                    $comment .= $shipping_address->cPLZ . ' -> ' . $predictions[0]['postalCode'] . PHP_EOL;
                }
                if ($shipping_address->cOrt !== $predictions[0]['locality']) {
                    $comment .= $shipping_address->cOrt . ' -> ' . $predictions[0]['locality'] . PHP_EOL;
                }
            }

            if ((strpos($shipping_meta->enderecoamsstatus, 'address_multiple_variants') !== false) && $predictions) {
                $addresses_variants = [];
                foreach ($predictions as $prediction) {
                    $addresses_variants[] = $prediction['streetName'] . ' ' . $prediction['buildingNumber'] . ', ' . $prediction['postalCode'] . ' ' . $prediction['locality'];
                }
                $comment .= implode("\n", $addresses_variants);
            }

            // Update order.
            $args_arr['oBestellung']->cKommentar = $comment;
            $args_arr['oBestellung']->updateInDb();
        }
    }
}


