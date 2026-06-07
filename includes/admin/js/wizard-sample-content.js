(function ($) {
    'use strict';

    $(document).on('click', '#wzkb-import-sample-btn', function () {
        var $btn    = $(this);
        var $status = $('#wzkb-import-sample-status');

        $btn.prop('disabled', true);
        $status.text(WZKBSampleContent.importing);

        $.post(
            ajaxurl,
            {
                action:                    'wzkb_import_sample_content',
                nonce:                     WZKBSampleContent.nonce,
                wzkb_import_sample_content: '1',
                wzkb_sample_multi_product:  WZKBSampleContent.multiProduct
            },
            function (response) {
                if (response.success) {
                    $status.text('✓ ' + response.data.message);
                    $btn.text(WZKBSampleContent.imported);
                    // Show delete button if not already present.
                    if (!$('#wzkb-delete-sample-btn').length) {
                        $btn.after(
                            $('<button>', {
                                type: 'button',
                                id: 'wzkb-delete-sample-btn',
                                'class': 'button button-secondary',
                                css: { marginLeft: '0.5em' },
                                text: WZKBSampleContent.deleteLabel
                            })
                        );
                    }
                } else {
                    $status.text(
                        (response.data && response.data.message)
                            ? response.data.message
                            : WZKBSampleContent.importFailed
                    );
                    $btn.prop('disabled', false);
                }
            }
        ).fail(function () {
            $status.text(WZKBSampleContent.importFailed);
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '#wzkb-delete-sample-btn', function () {
        if (!window.confirm(WZKBSampleContent.confirmDelete)) {
            return;
        }

        var $btn    = $(this);
        var $status = $('#wzkb-delete-sample-status');

        $btn.prop('disabled', true);
        $status.text(WZKBSampleContent.deleting);

        $.post(
            ajaxurl,
            {
                action: 'wzkb_delete_sample_content',
                nonce:  WZKBSampleContent.deleteNonce
            },
            function (response) {
                if (response.success) {
                    $status.text('✓ ' + response.data.message);
                    $btn.text(WZKBSampleContent.deleted);
                    // Update description to reflect empty state.
                    $btn.closest('.inside').find('p:first').text(WZKBSampleContent.noContent);
                } else {
                    $status.text(
                        (response.data && response.data.message)
                            ? response.data.message
                            : WZKBSampleContent.deleteFailed
                    );
                    $btn.prop('disabled', false);
                }
            }
        ).fail(function () {
            $status.text(WZKBSampleContent.deleteFailed);
            $btn.prop('disabled', false);
        });
    });
})(jQuery);
