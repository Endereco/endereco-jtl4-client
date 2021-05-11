<div style="display: none;">
    <span>kKunde: {$Kunde->kKunde}</span>
    <input type="text" name="billing-land" value="{$Kunde->cLand}">
    <input type="text" name="billing-plz" value="{$Kunde->cPLZ}">
    <input type="text" name="billing-ort" value="{$Kunde->cOrt}">
    <input type="text" name="billing-strasse" value="{$Kunde->cStrasse}">
    <input type="text" name="billing-hausnummer" value="{$Kunde->cHausnummer}">
    {if $Kunde->cAdressZusatz}
        <input type="text" name="billing-adresszusatz" value="{$Kunde->cAdressZusatz}">
    {/if}
    <input type="hidden" name="billing-enderecoamsts" value="{$bendereco_amsts}" >
    <input type="hidden" name="billing-enderecoamsstatus" value="{$bendereco_amsstatus|escape:'html'}" >
    <input type="hidden" name="billing-enderecoamspredictions" value="{$bendereco_amspredictions|escape:'html'}">
</div>
<div style="display: none;">
    <span>kLieferadresse: {$Lieferadresse->kLieferadresse}</span>
    <input type="text" name="shipping-land" value="{$Lieferadresse->cLand}">
    <input type="text" name="shipping-plz" value="{$Lieferadresse->cPLZ}">
    <input type="text" name="shipping-ort" value="{$Lieferadresse->cOrt}">
    <input type="text" name="shipping-strasse" value="{$Lieferadresse->cStrasse}">
    <input type="text" name="shipping-hausnummer" value="{$Lieferadresse->cHausnummer}">
    {if $Lieferadresse->cAdressZusatz}
        <input type="text" name="shipping-adresszusatz" value="{$Lieferadresse->cAdressZusatz}">
    {/if}
    <input type="hidden" name="shipping-enderecoamsts" value="{$sendereco_amsts}" >
    <input type="hidden" name="shipping-enderecoamsstatus" value="{$sendereco_amsstatus|escape:'html'}" >
    <input type="hidden" name="shipping-enderecoamspredictions" value="{$sendereco_amspredictions|escape:'html'}">
</div>
<script>
    ( function() {
        var $waitForIt = setInterval( function() {
            if (
                window.EnderecoIntegrator &&
                window.EnderecoIntegrator.ready
            ) {
                clearInterval($waitForIt);
                var billingAMS = window.EnderecoIntegrator.initAMS(
                    {
                        countryCode: 'billing-land',
                        postalCode: 'billing-plz',
                        locality: 'billing-ort',
                        streetName: 'billing-strasse',
                        buildingNumber: 'billing-hausnummer',
                        addressStatus: 'billing-enderecoamsstatus',
                        addressTimestamp: 'billing-enderecoamsts',
                        addressPredictions: 'billing-enderecoamspredictions',
                        additionalInfo: 'billing-adresszusatz',
                    },
                    {
                        addressType: 'billing_address',
                        name: 'billing_address'
                    }
                );

                window.EnderecoIntegrator.globalSpace.reloadPage = function() {
                    window.location.reload();
                }

                billingAMS.waitForAllExtension().then( function(EAO) {

                    EAO.onEditAddress.push( function() {
                        window.location = 'bestellvorgang.php?editRechnungsadresse=1';
                    })

                    EAO.onAfterAddressCheckSelected.push( function(EAO) {
                        EAO.waitForAllPopupsToClose().then(function() {
                            EAO.waitUntilReady().then( function() {
                                if (window.EnderecoIntegrator && window.EnderecoIntegrator.globalSpace.reloadPage) {
                                    window.EnderecoIntegrator.globalSpace.reloadPage();
                                    window.EnderecoIntegrator.globalSpace.reloadPage = undefined;
                                }
                            }).catch()
                        }).catch();
                        EAO._awaits++;
                        EAO.util.axios({
                            method: 'post',
                            url: 'io.php?io=endereco_inner_request',
                            data: {
                                method: 'editBillingAddress',
                                params: {
                                    customerId: '{$Kunde->kKunde}',
                                    address: EAO.address,
                                    copyShippingToo: {if $Lieferadresse->kLieferadresse} false {else} true {/if},
                                    enderecometa: {
                                        ts: EAO.addressTimestamp,
                                        status: EAO.addressStatus,
                                        predictions: EAO.addressPredictions,
                                    }
                                }
                            }
                        }).then( function(response) {
                            window.location.reload();
                        }).catch( function(error) {
                            console.log('Something went wrong.')
                        }).finally( function() {
                            EAO._awaits--;
                        });
                    });

                }).catch();

                {if $Lieferadresse->kLieferadresse}
                    var shippingAMS = window.EnderecoIntegrator.initAMS(
                        {
                            countryCode: 'shipping-land',
                            postalCode: 'shipping-plz',
                            locality: 'shipping-ort',
                            streetName: 'shipping-strasse',
                            buildingNumber: 'shipping-hausnummer',
                            addressStatus: 'shipping-enderecoamsstatus',
                            addressTimestamp: 'shipping-enderecoamsts',
                            addressPredictions: 'shipping-enderecoamspredictions',
                            additionalInfo: 'shipping-adresszusatz',
                        },
                        {
                            addressType: 'shipping_address',
                            name: 'shipping_address'
                        }
                    );
                    shippingAMS.waitForAllExtension().then( function(EAO) {

                        EAO.onEditAddress.push( function() {
                            window.location = 'bestellvorgang.php?editLieferadresse=1';
                        })

                        EAO.onAfterAddressCheckSelected.push( function(EAO) {

                            EAO.waitForAllPopupsToClose().then(function() {
                                EAO.waitUntilReady().then( function() {
                                    if (window.EnderecoIntegrator && window.EnderecoIntegrator.globalSpace.reloadPage) {
                                        window.EnderecoIntegrator.globalSpace.reloadPage();
                                        window.EnderecoIntegrator.globalSpace.reloadPage = undefined;
                                    }
                                }).catch()
                            }).catch();

                            EAO._awaits++;
                            EAO.util.axios({
                                method: 'post',
                                url: 'io.php?io=endereco_inner_request',
                                data: {
                                    method: 'editShippingAddress',
                                    params: {
                                        shippingAddressId: '{$Lieferadresse->kLieferadresse}',
                                        address: EAO.address,
                                        enderecometa: {
                                            ts: EAO.addressTimestamp,
                                            status: EAO.addressStatus,
                                            predictions: EAO.addressPredictions,
                                        }
                                    }
                                }
                            }).then( function(response) {
                                window.location.reload();
                            }).catch( function(error) {
                                console.log('Something went wrong.')
                            }).finally( function() {
                                EAO._awaits--;
                            });
                        })
                    }).catch();
                {/if}
            }
        }, 1);
    })();
</script>
