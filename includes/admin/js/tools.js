/**
 * Tools page scripts.
 *
 * @package WebberZone\Snippetz
 */

/* global jQuery, ajaxurl, ata_tools_data */
jQuery(document).ready(function ($) {
    $('#cache_clear').on('click', function (e) {
        e.preventDefault();

        if (!confirm(ata_tools_data.strings.confirm_clear)) {
            return false;
        }

        var button = $(this);
        var originalText = button.text();

        button.prop('disabled', true);
        button.text(ata_tools_data.strings.clearing);

        $.post(ajaxurl, {
            action: 'ata_clear_cache',
            security: ata_tools_data.nonce
        }, function (response) {
            if (response.success) {
                button.text(ata_tools_data.strings.cleared);
                setTimeout(function () {
                    button.prop('disabled', false);
                    button.text(originalText);
                }, 2000);
            } else {
                alert(ata_tools_data.strings.error);
                button.prop('disabled', false);
                button.text(originalText);
            }
        }).fail(function () {
            alert(ata_tools_data.strings.error);
            button.prop('disabled', false);
            button.text(originalText);
        });
    });
});
