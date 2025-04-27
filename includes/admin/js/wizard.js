jQuery(function ($) {
    'use strict';

    // Enable continue button when required fields are filled.
    $('.wzkb-setup-content').on('change', 'input, select', function () {
        var form = $(this).closest('form');
        var continue_btn = form.find('.button-next');

        var required_inputs = form.find('input[required], select[required]');
        var is_complete = true;

        required_inputs.each(function () {
            if (!$(this).val()) {
                is_complete = false;
                return false;
            }
        });

        continue_btn.prop('disabled', !is_complete);
    });

    // Confirm skip setup for "Not right now" button only.
    $('#wzkb-not-now').on('click', function () {
        if (confirm(wzkbWizard.skip_setup)) {
            window.location.href = wzkbWizard.dashboard_url;
        }
    });
});