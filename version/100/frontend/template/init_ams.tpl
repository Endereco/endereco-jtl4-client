<script>
    ( function() {
        var $waitForIt = setInterval( function() {
            if (
                !!document.querySelector('[name="anrede"]') &&
                !!document.querySelector('[name="vorname"]') &&
                window.EnderecoIntegrator &&
                window.EnderecoIntegrator.ready
            ) {
                clearInterval($waitForIt);
                window.EnderecoIntegrator.initPersonServices(
                    '',
                    {
                        name: 'general'
                    }
                );
            }
        }, 1);
    })();
</script>

<script>
    ( function() {
        var $waitForIt = setInterval( function() {
            if (
                !!document.querySelector('#panel-register-form [name="email"]') &&
                window.EnderecoIntegrator &&
                window.EnderecoIntegrator.ready
            ) {
                clearInterval($waitForIt);
                window.EnderecoIntegrator.initEmailServices(
                    '',
                    {
                        name: 'general',
                        postfixCollection: {
                            email: '#panel-register-form [name="email"]'
                        }
                    }
                );
            }
        }, 1);
    })();
</script>

<script>
    ( function() {
        var $waitForIt = setInterval( function() {
            if (
                !!document.querySelector('[name="land"]') &&
                !!document.querySelector('[name="plz"]') &&
                !!document.querySelector('[name="ort"]') &&
                !!document.querySelector('[name="strasse"]') &&
                window.EnderecoIntegrator &&
                window.EnderecoIntegrator.ready
            ) {
                clearInterval($waitForIt);
                window.EnderecoIntegrator.initAMS(
                    '',
                    {
                        addressType: 'general_address',
                        name: 'general_address'
                    }
                )
            }
        }, 1);
    })();
</script>

<script>
    ( function() {
        var $waitForIt = setInterval( function() {
            if (
                !!document.querySelector('[name="register[shipping_address][land]"]') &&
                !!document.querySelector('[name="register[shipping_address][plz]"]') &&
                !!document.querySelector('[name="register[shipping_address][ort]"]') &&
                !!document.querySelector('[name="register[shipping_address][strasse]"]') &&
                window.EnderecoIntegrator &&
                window.EnderecoIntegrator.ready
            ) {
                clearInterval($waitForIt);
                window.EnderecoIntegrator.initAMS(
                    '',
                    {
                        name: 'shipping_address',
                        addressType: 'shipping_address',
                        postfixCollection: {
                            countryCode: 'register[shipping_address][land]',
                            postalCode: 'register[shipping_address][plz]',
                            locality: 'register[shipping_address][ort]',
                            streetFull: '',
                            streetName: 'register[shipping_address][strasse]',
                            buildingNumber: 'register[shipping_address][hausnummer]',
                            addressStatus: 'enderecodeliveryamsstatus',
                            addressTimestamp: 'enderecodeliveryamsts',
                            addressPredictions: 'enderecodeliveryamspredictions',
                            additionalInfo: 'register[shipping_address][adresszusatz]',
                        }
                    }
                )
            }
        }, 1);
    })();
</script>
