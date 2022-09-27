<script>
    ( function() {
        var $interval = setInterval( function() {
            if (window.EnderecoIntegrator && window.EnderecoIntegrator.loaded) {
                window.EnderecoIntegrator.defaultCountry = 'de';
                window.EnderecoIntegrator.themeName = '{$endereco_theme_name}';
                window.EnderecoIntegrator.defaultCountrySelect = false;
                window.EnderecoIntegrator.config.apiUrl = '{$endereco_api_url}';
                window.EnderecoIntegrator.config.apiKey = '{$endereco_jtl4_client_api_key}';
                window.EnderecoIntegrator.config.showDebugInfo = ('on' === '{$endereco_jtl4_client_show_debug_info}');
                window.EnderecoIntegrator.config.remoteApiUrl = '{$endereco_jtl4_client_remote_url}';
                window.EnderecoIntegrator.config.trigger.onblur = ('on' === '{$endereco_jtl4_client_onblur_trigger}');
                window.EnderecoIntegrator.config.trigger.onsubmit = ('on' === '{$endereco_jtl4_client_onsubmit_trigger}');
                window.EnderecoIntegrator.config.ux.smartFill = ('on' === '{$endereco_jtl4_client_smart_fill}');
                window.EnderecoIntegrator.config.ux.checkExisting = ('on' === '{$endereco_jtl4_client_check_existing}');
                window.EnderecoIntegrator.config.ux.resumeSubmit = ('on' === '{$endereco_jtl4_client_resume_submit}');
                window.EnderecoIntegrator.config.ux.useStandardCss = ('on' === '{$endereco_jtl4_client_use_standart_css}');
                window.EnderecoIntegrator.config.ux.showEmailStatus = ('on' === '{$endereco_jtl4_client_show_email_status}');
                window.EnderecoIntegrator.config.ux.changeFieldsOrder = true;
                window.EnderecoIntegrator.countryMappingUrl = '';
                window.EnderecoIntegrator.config.templates.buttonClasses = 'btn btn-primary btn-lg';
                window.EnderecoIntegrator.config.texts = {
                    popUpHeadline: '{$endereco_jtl4_client_popup_headline|escape}',
                    popUpSubline: '{$endereco_jtl4_client_popup_subline|escape}',
                    yourInput: '{$endereco_jtl4_client_your_input|escape}',
                    editYourInput: '{$endereco_jtl4_client_edit_input|escape}',
                    ourSuggestions: '{$endereco_jtl4_client_our_suggestions|escape}',
                    useSelected: '{$endereco_jtl4_client_use_selected|escape}',
                    popupHeadlines: {
                        general_address: '{$endereco_jtl4_client_general_address|escape}',
                        billing_address: '{$endereco_jtl4_client_billing_address|escape}',
                        shipping_address: '{$endereco_jtl4_client_shipping_address|escape}',
                    },
                    statuses: {
                        email_not_correct: '{$endereco_jtl4_client_status_email_not_correct|escape}',
                        email_cant_receive: '{$endereco_jtl4_client_status_email_cant_receive|escape}',
                        email_syntax_error: '{$endereco_jtl4_client_status_email_syntax_error|escape}',
                        email_no_mx: '{$endereco_jtl4_client_status_email_no_mx|escape}'
                    }
                };
                window.EnderecoIntegrator.activeServices = {
                    ams: ('on' === '{$endereco_jtl4_client_ams_active}'),
                    emailService: ('on' === '{$endereco_jtl4_client_es_active}'),
                    personService: ('on' === '{$endereco_jtl4_client_ps_active}')
                }
                window.EnderecoIntegrator.ready = true;
                clearInterval($interval);
            }
        }, 1);
    })();
</script>

{if $endereco_jtl4_client_color_1}
    <style>
        .endereco-predictions-wrapper .endereco-span--neutral {
            border-bottom: 1px dotted {$endereco_jtl4_client_color_1} !important;
            color: {$endereco_jtl4_client_color_1} !important;
        }
        .endereco-predictions .endereco-predictions__item.endereco-predictions__item.endereco-predictions__item:hover,
        .endereco-predictions .endereco-predictions__item.endereco-predictions__item.endereco-predictions__item.active {
            background-color: {$endereco_jtl4_client_color_1_bg} !important;
        }
    </style>
{/if}

{if $endereco_jtl4_client_color_2}
    <style>
        .endereco-modal__header-main {
            color: {$endereco_jtl4_client_color_2} !important;
        }

        .endereco-address-predictions--original .endereco-address-predictions__label {
            border-color: {$endereco_jtl4_client_color_2} !important;
        }

        .endereco-address-predictions--original .endereco-span--remove {
            background-color: {$endereco_jtl4_client_color_2_bg} !important;
            border-bottom: 1px solid {$endereco_jtl4_client_color_2} !important;
        }
    </style>
{/if}

{if $endereco_jtl4_client_color_3}
    <style>
        .endereco-address-predictions__radio:checked ~ .endereco-address-predictions__label,
        .endereco-address-predictions__item.active .endereco-address-predictions__label {
            border-color: {$endereco_jtl4_client_color_3} !important;
        }

        .endereco-address-predictions__radio:checked ~ .endereco-address-predictions__label::before,
        .endereco-address-predictions__item.active .endereco-address-predictions__label::before {
            border-color: {$endereco_jtl4_client_color_3} !important;
        }

        .endereco-address-predictions__label::after {
            background-color: {$endereco_jtl4_client_color_3} !important;
        }

        .endereco-address-predictions--suggestions .endereco-span--add {
            border-bottom: 1px solid {$endereco_jtl4_client_color_3};
            background-color:  {$endereco_jtl4_client_color_3_bg} !important;
        }
    </style>
{/if}
