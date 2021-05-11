<script>
    function enderecoLoadAMSConfig() {
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
        window.EnderecoIntegrator.config.ux.allowCloseModal = ('on' === '{$endereco_jtl4_client_allow_close_modal}');
        window.EnderecoIntegrator.config.ux.confirmWithCheckbox = ('on' === '{$endereco_jtl4_client_confirm_with_checkbox}');
        window.EnderecoIntegrator.config.ux.changeFieldsOrder = true;
        window.EnderecoIntegrator.countryMappingUrl = '';
        window.EnderecoIntegrator.config.templates.primaryButtonClasses = 'btn btn-primary btn-lg';
        window.EnderecoIntegrator.config.templates.secondaryButtonClasses = 'btn btn-secondary btn-lg';
        window.EnderecoIntegrator.config.texts = {
            popUpHeadline: '{$endereco_jtl4_client_popup_headline}',
            popUpSubline: '{$endereco_jtl4_client_popup_subline}',
            mistakeNoPredictionSubline: '{$endereco_jtl4_client_mistake_no_predictions_subline}',
            notFoundSubline: '{$endereco_jtl4_client_popup_subline_not_found}',
            confirmMyAddressCheckbox: '{$endereco_jtl4_client_confirm_my_address_checkout}',
            yourInput: '{$endereco_jtl4_client_your_input}',
            editYourInput: '{$endereco_jtl4_client_edit_input}',
            ourSuggestions: '{$endereco_jtl4_client_our_suggestions}',
            useSelected: '{$endereco_jtl4_client_use_selected}',
            confirmAddress: '{$endereco_jtl4_client_confirm_address}',
            editAddress: '{$endereco_jtl4_client_edit_address}',
            warningText: '{$endereco_jtl4_client_faulty_address_warning}',
            popupHeadlines: {
                general_address: '{$endereco_jtl4_client_general_address}',
                billing_address: '{$endereco_jtl4_client_billing_address}',
                shipping_address: '{$endereco_jtl4_client_shipping_address}',
            },
            statuses: {
                email_not_correct: '{$endereco_jtl4_client_status_email_not_correct}',
                email_cant_receive: '{$endereco_jtl4_client_status_email_cant_receive}',
                email_syntax_error: '{$endereco_jtl4_client_status_email_syntax_error}',
                email_no_mx: '{$endereco_jtl4_client_status_email_no_mx}',
                building_number_is_missing: '{$endereco_jtl4_client_status_error_building_number_is_missing}',
                building_number_not_found: '{$endereco_jtl4_client_status_error_building_number_not_found}',
                street_name_needs_correction: '{$endereco_jtl4_client_status_error_street_name_needs_correction}',
                locality_needs_correction: '{$endereco_jtl4_client_status_error_locality_needs_correction}',
                postal_code_needs_correction: '{$endereco_jtl4_client_status_error_postal_code_needs_correction}',
                country_code_needs_correction: '{$endereco_jtl4_client_status_error_country_code_needs_correction}'
            }
        };
        window.EnderecoIntegrator.activeServices = {
            ams: ('on' === '{$endereco_jtl4_client_ams_active}'),
            emailService: ('on' === '{$endereco_jtl4_client_es_active}'),
            personService: ('on' === '{$endereco_jtl4_client_ps_active}')
        }
        window.EnderecoIntegrator.countryCodeToNameMapping = JSON.parse('{$endereco_jtl4_client_country_mapping}');
        window.EnderecoIntegrator.ready = true;
    }
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

        .endereco-s--salutation_correct .selectric,
		.endereco-s--country_code_correct .selectric {
			border-color: #4caf50;
			background-color: #f8fcf8;
		}

		.endereco-s--salutation_correct .selectric,
		.endereco-s--country_code_correct .selectric {
			border-color: #4caf50;
			background-color: #f8fcf8;
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
