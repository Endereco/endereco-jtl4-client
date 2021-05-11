<?php
/**
 * HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE
 *
 * When the order is created, we check if there are any saved address metadata. If there are - we save them to database.
 */
if (isset($args_arr) && $args_arr['oBestellung']) {

    // Save status.
    if ($args_arr['oBestellung']->kLieferadresse) {
        if ($_SESSION['EnderecoShippingAddressMeta'] && !empty($_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'])) {
            $delivery_status = $_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'];
            $delivery_predictions = $_SESSION['EnderecoShippingAddressMeta']['enderecoamspredictions'];
            $delivery_timestamp = $_SESSION['EnderecoShippingAddressMeta']['enderecoamsts'];
        }
    } else if ($args_arr['oBestellung']->kKunde) {
        if (!empty($_SESSION['EnderecoBillingAddressMeta']) && !empty($_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'])) {
            $delivery_status = $_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'];
            $delivery_predictions = $_SESSION['EnderecoBillingAddressMeta']['enderecoamspredictions'];
            $delivery_timestamp = $_SESSION['EnderecoBillingAddressMeta']['enderecoamsts'];
        }
    }
    if (!empty($delivery_status)) {
        try {
            Shop::DB()->queryPrepared(
                "INSERT INTO `tbestellattribut` 
                    (`kBestellung`, `cName`, `cValue`)
                 VALUES 
                    (:id1, :name1, :value1),
                    (:id2, :name2, :value2),
                    (:id3, :name3, :value3) 
                ON DUPLICATE KEY UPDATE    
                   `cValue`=VALUES(`cValue`)
                ",
                [
                    ':id1' => $args_arr['oBestellung']->kBestellung,
                    ':name1' => 'enderecoamsts',
                    ':value1' => $delivery_timestamp,
                    ':id2' => $args_arr['oBestellung']->kBestellung,
                    ':name2' => 'enderecoamsstatus',
                    ':value2' => $delivery_status,
                    ':id3' => $args_arr['oBestellung']->kBestellung,
                    ':name3' => 'enderecoamspredictions',
                    ':value3' => $delivery_predictions,
                ],
                1
            );
        } catch(\Exception $e) {
            // TODO: log it.
        }
    }

    // Save billig naddress result.
    if (!empty($_SESSION['EnderecoBillingAddressMeta']) && !empty($_SESSION['EnderecoBillingAddressMeta']['enderecoamsstatus'])) {
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
    if (!empty($_SESSION['EnderecoShippingAddressMeta']) && !empty($_SESSION['EnderecoShippingAddressMeta']['enderecoamsstatus'])) {
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

        if (!empty($shipping_meta) && !empty($shipping_meta->enderecoamsstatus)) {
            // Predictions
            $predictions = $helper->utf8_decode_array(
                json_decode(
                    utf8_encode($shipping_meta->enderecoamspredictions),
                    true,
                    512,
                    JSON_UNESCAPED_UNICODE
                )
            );
            $statusCodes = explode(',', $shipping_meta->enderecoamsstatus);

            $mainMessage = "";
            if (in_array('address_correct', $statusCodes)) {
                $mainMessage = $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_address_correct'];
            }

            if (in_array('address_needs_correction', $statusCodes)) {
                $mainMessage = $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_address_needs_correction'];

                if (in_array('building_number_not_found', $statusCodes)) {
                    $mainMessage = $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_building_number_not_found'];
                }

                if (in_array('building_number_is_missing', $statusCodes)) {
                    $mainMessage = $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_building_number_is_missing'];
                }
            }

            if (in_array('address_not_found', $statusCodes)) {
                $mainMessage = $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_address_not_found'];
            }

            if (in_array('address_multiple_variants', $statusCodes)) {
                $mainMessage = $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_address_multiple_variants'];
            }

            if (!empty($mainMessage) && in_array('address_selected_by_customer', $statusCodes)) {
                $mainMessage .= $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_address_selected_by_customer'];
            }

            if (!empty($mainMessage) && in_array('address_selected_automatically', $statusCodes)) {
                $mainMessage .= $oPlugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_wawi_address_selected_automatically'];
            }

            $correctionAdvice = "";
            if (in_array('address_needs_correction', $statusCodes)
                && !in_array('building_number_not_found', $statusCodes)
                && !in_array('building_number_is_missing', $statusCodes)
                && !empty($predictions[0])
            ) {
                $correctionAdvice = "\n";
                if ($shipping_address->cStrasse !== $predictions[0]['streetName']) {
                    $correctionAdvice .= $shipping_address->cStrasse . ' -> ' . $predictions[0]['streetName'] . PHP_EOL;
                }
                if ($shipping_address->cPLZ !== $predictions[0]['postalCode']) {
                    $correctionAdvice .= $shipping_address->cPLZ . ' -> ' . $predictions[0]['postalCode'] . PHP_EOL;
                }
                if ($shipping_address->cOrt !== $predictions[0]['locality']) {
                    $correctionAdvice .= $shipping_address->cOrt . ' -> ' . $predictions[0]['locality'] . PHP_EOL;
                }
                if (strtolower($shipping_address->cLand) !== strtolower($predictions[0]['countryCode'])) {
                    $correctionAdvice .= $shipping_address->cLand . ' -> ' . strtoupper($predictions[0]['countryCode']) . PHP_EOL;
                }
            }

            if (!empty($mainMessage)) {
                if (!empty($comment)) {
                    $comment .= PHP_EOL;
                }
                $comment .= $mainMessage . $correctionAdvice;
                // Update order.
                $args_arr['oBestellung']->cKommentar = $comment;
                $args_arr['oBestellung']->updateInDb();
            }
        }
    }
}


