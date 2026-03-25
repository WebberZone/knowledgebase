(function ($) {
    'use strict';

    function slugify(value) {
        if (!value) {
            return '';
        }
        return String(value)
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9\-]/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^\-+|\-+$/g, '');
    }

    function getNextIndex($tbody) {
        var maxIndex = -1;
        $tbody.find('tr.wzkb-wizard-repeater-row').each(function () {
            var $name = $(this).find('input.wzkb-wizard-name');
            var nameAttr = $name.attr('name') || '';
            var match = nameAttr.match(/\[(\d+)\]\[name\]/);
            if (match && match[1]) {
                maxIndex = Math.max(maxIndex, parseInt(match[1], 10));
            }
        });
        return maxIndex + 1;
    }

    function updateRowIndex($row, fieldName, index) {
        $row.find('input, textarea, select').each(function () {
            var $field = $(this);
            var currentName = $field.attr('name') || '';
            currentName = currentName.replace(new RegExp('^' + fieldName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[\\d+\\]'), fieldName + '[' + index + ']');
            $field.attr('name', currentName);
        });
    }

    function clearRowValues($row) {
        $row.find('input[type="text"], textarea').val('');
        $row.find('select').prop('selectedIndex', 0);
        $row.find('.wzkb-wizard-slug').removeData('userEdited');
        $row.find('.wzkb-wizard-remove-row').text('Remove');
    }

    function updateRowActionLabel($row) {
        var $existing = $row.find('.wzkb-wizard-existing-select');
        if (!$existing.length) {
            return;
        }
        var existingId = parseInt($existing.val(), 10) || 0;
        $row.find('.wzkb-wizard-remove-row').text(existingId > 0 ? 'Clear row' : 'Remove');
    }

    function addRow(fieldName, $tbody) {
        var $templateRow = $tbody.find('tr.wzkb-wizard-repeater-row').first();
        if (0 === $templateRow.length) {
            return;
        }
        var index = getNextIndex($tbody);
        var $newRow = $templateRow.clone(true, true);
        updateRowIndex($newRow, fieldName, index);
        clearRowValues($newRow);
        $tbody.append($newRow);
    }

    $(document).on('click', '.wzkb-wizard-add-row', function (e) {
        e.preventDefault();
        var fieldName = $(this).data('target');
        var $tbody = $(this).closest('.wzkb-wizard-repeater').find('tbody.wzkb-wizard-repeater-rows');
        addRow(fieldName, $tbody);
    });

    $(document).on('click', '.wzkb-wizard-remove-row', function (e) {
        e.preventDefault();
        var $tbody = $(this).closest('tbody.wzkb-wizard-repeater-rows');
        var $rows = $tbody.find('tr.wzkb-wizard-repeater-row');
        var $row = $(this).closest('tr.wzkb-wizard-repeater-row');
        var $existing = $row.find('.wzkb-wizard-existing-select');
        var existingId = $existing.length ? (parseInt($existing.val(), 10) || 0) : 0;
        if (existingId > 0) {
            clearRowValues($row);
            return;
        }
        if ($rows.length <= 1) {
            clearRowValues($rows.first());
            return;
        }
        $(this).closest('tr.wzkb-wizard-repeater-row').remove();
    });

    $(document).on('input', '.wzkb-wizard-slug', function () {
        $(this).data('userEdited', true);
    });

    $(document).on('blur', '.wzkb-wizard-name', function () {
        var $name = $(this);
        var $row = $name.closest('tr.wzkb-wizard-repeater-row');
        var $slug = $row.find('.wzkb-wizard-slug');
        if (0 === $slug.length) {
            return;
        }
        if ($slug.data('userEdited')) {
            return;
        }
        if ($slug.val()) {
            return;
        }
        $slug.val(slugify($name.val()));
    });

    $(document).on('change', '.wzkb-wizard-existing-select', function () {
        var $select = $(this);
        var $row = $select.closest('tr.wzkb-wizard-repeater-row');
        var $selected = $select.find('option:selected');

        var existingId = parseInt($selected.val(), 10) || 0;
        updateRowActionLabel($row);
        if (existingId <= 0) {
            return;
        }

        var name = $selected.data('name') || '';
        var slug = $selected.data('slug') || '';
        var description = $selected.data('description') || '';
        var parent = parseInt($selected.data('parent'), 10) || 0;
        var relatedId = parseInt($selected.data('related-id'), 10) || 0;

        var $name = $row.find('.wzkb-wizard-name');
        var $slug = $row.find('.wzkb-wizard-slug');
        var $desc = $row.find('.wzkb-wizard-description');
        var $parentSelect = $row.find('.wzkb-wizard-term-select');

        if ($name.length) {
            $name.val(name);
        }
        if ($slug.length) {
            $slug.val(slug);
            $slug.data('userEdited', true);
        }
        if ($desc.length) {
            $desc.val(description);
        }
        if ($parentSelect.length) {
            if (parent > 0) {
                $parentSelect.val(String(parent));
            } else if (relatedId > 0) {
                $parentSelect.val(String(relatedId));
            }
        }
    });
})(jQuery);
