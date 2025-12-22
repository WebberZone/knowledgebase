/**
 * Sections product filter for Knowledge Base plugin.
 *
 * @package WebberZone\Knowledge_Base
 */

/* global knowledgebaseProductFilter */

(function ($) {
	'use strict';

	var DATA = window.knowledgebaseProductFilter || {};

	/**
	 * Build and insert the product filter form.
	 */
	function renderProductFilter() {
		if ($('.wzkb-sections-product-filter').length) {
			return;
		}

		var products = DATA.products || [];
		if (!products.length) {
			return;
		}

		var strings = DATA.strings || {};
		var selected = DATA.selectedProduct;
		if (selected === undefined || selected === null || '' === String(selected)) {
			selected = DATA.currentProduct || '';
		}
		var queryParams = $.extend({}, DATA.queryParams || {});

		if (!queryParams.taxonomy) {
			queryParams.taxonomy = 'wzkb_category';
		}

		var $form = $('<form>', {
			class: 'wzkb-sections-product-filter',
			method: 'get'
		}).css({
			marginTop: '12px'
		});

		$.each(queryParams, function (key, value) {
			if ('wzkb_product' === key) {
				return;
			}

			if (value === undefined || value === null || '' === String(value)) {
				return;
			}

			$form.append(
				$('<input>', {
					type: 'hidden',
					name: key,
					value: value
				})
			);
		});

		var $label = $('<label>', {
			for: 'wzkb_product',
			class: 'screen-reader-text'
		}).text(strings.productLabel || 'Product:');

		var instructionText = strings.filterInstruction || 'Switch product to refine this list:';
		var $instruction = $('<span>', {
			class: 'wzkb-sections-product-filter__instruction',
			text: instructionText
		});

		var $select = $('<select>', {
			id: 'wzkb_product',
			name: 'wzkb_product',
			class: 'postform'
		});

		$select.append(
			$('<option>', {
				value: '',
				text: strings.allProducts || 'All Products'
			})
		);

		$.each(products, function (index, product) {
			$select.append(
				$('<option>', {
					value: product.term_id,
					text: product.name,
					selected: String(product.term_id) === String(selected)
				})
			);
		});

		$form.append($label, $instruction, $select);

		$select.on('change', function () {
			$form.trigger('submit');
		});

		$form.append(
			'<noscript><button type="submit" class="button">' + (strings.filter || 'Filter') + '</button></noscript>'
		);

		var $wrap = $('.wrap');
		if ($wrap.length) {
			var $button = $wrap.children('.wzkb_button').first();
			if ($button.length) {
				$button.after($form);
			} else {
				var $heading = $wrap.children('h1').first();
				if ($heading.length) {
					$heading.after($form);
				} else {
					$wrap.prepend($form);
				}
			}
		}
	}

	/**
	 * Enhance the search form placeholder when present.
	 */
	function enhanceSearch() {
		var strings = DATA.strings || {};
		var placeholder = strings.searchPlaceholder;
		var $searchInput = $('.search-form').find('#tag-search-input');

		if ($searchInput.length && placeholder) {
			$searchInput.attr('placeholder', placeholder);
		}

		if (DATA.queryParams && DATA.queryParams.s && !$searchInput.val()) {
			$searchInput.val(DATA.queryParams.s);
		}
	}

	$(function () {
		renderProductFilter();
		enhanceSearch();
	});

})(jQuery);
