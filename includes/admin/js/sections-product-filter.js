/**
 * Sections product filter for Knowledge Base plugin.
 *
 * @package WebberZone\Knowledge_Base
 */

/* global knowledgebaseProductFilter */

(function ($) {
	'use strict';

	/**
	 * Add product filter to the Sections taxonomy screen.
	 */
	function addProductFilter() {
		// Find the search form
		var $searchForm = $('.search-form');
		if (!$searchForm.length) {
			return;
		}

		// Find the search box paragraph inside the form
		var $searchBox = $searchForm.find('.search-box');
		if (!$searchBox.length) {
			return;
		}

		// Create product filter element
		var $productFilter = $('<span class="product-filter-wrap" style="margin-right: 10px; display: inline-block; vertical-align: middle;">');
		var $productLabel = $('<label for="wzkb_product" style="margin-right: 5px; display: inline-block;">');
		$productLabel.text(knowledgebaseProductFilter.strings.productLabel);

		var $select = $('<select name="wzkb_product" id="wzkb_product" class="postform" style="vertical-align: middle;">');

		$select.append($('<option>', {
			value: '',
			text: knowledgebaseProductFilter.strings.allProducts
		}));

		// Add all product options.
		$.each(knowledgebaseProductFilter.products, function (index, product) {
			$select.append($('<option>', {
				value: product.term_id,
				text: product.name,
				selected: knowledgebaseProductFilter.selectedProduct == product.term_id
			}));
		});

		// Add product filter to search box
		$productFilter.append($select);

		// Insert product filter before the search box
		$searchBox.prepend($productFilter);

		// Add placeholder to search input
		$searchBox.find('#tag-search-input').attr('placeholder', knowledgebaseProductFilter.strings.searchPlaceholder);

		// Ensure we're not losing search parameter if it exists in the URL but not in the form
		if (knowledgebaseProductFilter.queryParams.s && !$searchForm.find('input[name="s"]').val()) {
			$searchForm.find('input[name="s"]').val(knowledgebaseProductFilter.queryParams.s);
		}
	}

	// Initialize when DOM is ready.
	$(document).ready(function () {
		addProductFilter();
	});

})(jQuery);
