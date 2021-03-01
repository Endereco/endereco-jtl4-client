<?php
/**
 * HOOK_JTL_PAGE
 *
 * Right after the customer edits and saves his address in his account area
 * we also save the meta results in the database and send doAccountigs.
 */

global $knd;
if (isset($_POST['editRechnungsadresse']) && (int)$_POST['editRechnungsadresse'] === 1 && !empty($knd->kKunde)) {
    $editRechnungsadresse = (int)$_POST['editRechnungsadresse'];
}

if ($editRechnungsadresse) {
    if ($knd) {
        $customerId = $knd->kKunde;
        if ($customerId) {
            // Save meta results in the database.
            Shop::DB()->queryPrepared(
                "INSERT INTO `xplugin_endereco_jtl4_client_tams` 
                    (`kKunde`,`kRechnungsadresse`, `kLieferadresse`, `enderecoamsts`, `enderecoamsstatus`, `enderecoamspredictions`, `last_change_at`)
                 VALUES 
                    (:kKunde, NULL, NULL, :enderecoamsts, :enderecoamsstatus, :enderecoamspredictions, now())
                ON DUPLICATE KEY UPDATE    
                   `kKunde`=:kKunde2, `enderecoamsts`=:enderecoamsts2, `enderecoamsstatus`=:enderecoamsstatus2, `enderecoamspredictions`=:enderecoamspredictions2, `last_change_at`=now()
                ",
                [
                    ':kKunde' => $customerId,
                    ':enderecoamsts' => $_POST['enderecoamsts'],
                    ':enderecoamsstatus' => $_POST['enderecoamsstatus'],
                    ':enderecoamspredictions' => $_POST['enderecoamspredictions'],
                    ':kKunde2' => $customerId,
                    ':enderecoamsts2' => $_POST['enderecoamsts'],
                    ':enderecoamsstatus2' => $_POST['enderecoamsstatus'],
                    ':enderecoamspredictions2' => $_POST['enderecoamspredictions'],
                ],
                1
            );

            // Send do accountings.
            require_once $oPlugin->cFrontendPfad . 'inc/class.endereco_jtl4_client.helper.php';
            $helper = EndrecoJtl4ClientHelper::getInstance($oPlugin);
            $helper->doAccountings(
                $helper->findSessions()
            );
        }
    }
}

