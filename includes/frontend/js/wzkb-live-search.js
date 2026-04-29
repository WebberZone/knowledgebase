/**
 * Knowledge Base AJAX live search autocomplete.
 */
class WZKBSearchAutocomplete {
	static SELECTOR = '.wzkb-search-form';
	static DEBOUNCE_DELAY = 300;
	static CACHE_TIMEOUT = 5 * 60 * 1000; // 5 minutes.

	constructor(form) {
		this.form = form;
		this.searchInput = form.querySelector('input[name="s"]');
		this.submitButton = form.querySelector(
			'input[type="submit"], button[type="submit"]'
		);
		this.selectedIndex = -1;
		this.debounceTimer = null;
		this.cache = new Map();
		this.abortController = null;

		if (!this.searchInput) return;

		// Add class to identify forms with KB live search functionality.
		this.form.classList.add('wzkb-live-search-enabled');

		this.instanceId = this.generateId();

		this.initializeElements();
		this.bindEvents();
	}

	/**
	 * Initializes DOM elements and sets up ARIA attributes.
	 */
	initializeElements() {
		this.announceRegion = this.createAnnounceRegion();
		this.form.insertBefore(this.announceRegion, this.form.firstChild);

		this.resultsContainer = this.createResultsContainer();
		this.insertResultsContainer();

		this.spinner = this.createSpinner();
		this.insertSpinner();

		this.configureSearchInput();
	}

	/**
	 * Creates announcement region for screen readers.
	 *
	 * @return {HTMLDivElement}
	 */
	createAnnounceRegion() {
		const region = document.createElement('div');
		region.className = 'wzkb-visually-hidden';
		region.setAttribute('aria-live', 'polite');
		region.id = `wzkb-announce-${this.instanceId}`;
		return region;
	}

	/**
	 * Creates results container.
	 *
	 * @return {HTMLDivElement}
	 */
	createResultsContainer() {
		const container = document.createElement('div');
		container.className = 'wzkb-autocomplete-results';
		container.setAttribute('role', 'listbox');
		container.setAttribute('aria-label', wzkb_live_search.strings.suggestions_label);
		container.id = `wzkb-search-suggestions-${this.instanceId}`;
		return container;
	}

	/**
	 * Creates loading spinner.
	 *
	 * @return {HTMLSpanElement}
	 */
	createSpinner() {
		const spinner = document.createElement('span');
		spinner.className = 'wzkb-search-spinner';
		spinner.setAttribute('aria-hidden', 'true');
		return spinner;
	}

	/**
	 * Generates random ID for elements.
	 *
	 * @return {string}
	 */
	generateId() {
		return Math.random().toString(36).substring(2, 9);
	}

	/**
	 * Inserts results container after the search input.
	 */
	insertResultsContainer() {
		this.form.appendChild(this.resultsContainer);
		this.positionResults();
	}

	/**
	 * Inserts spinner as an absolutely positioned element.
	 */
	insertSpinner() {
		this.form.appendChild(this.spinner);
		this.positionSpinner();
	}

	/**
	 * Positions the spinner over the search input.
	 */
	positionSpinner() {
		const inputRect = this.searchInput.getBoundingClientRect();
		const formRect = this.form.getBoundingClientRect();
		const isRTL = document.dir === 'rtl' || this.form.dir === 'rtl';

		this.spinner.style.position = 'absolute';

		if (isRTL) {
			this.spinner.style.left = `${inputRect.left - formRect.left + 12}px`;
			this.spinner.style.right = 'auto';
		} else {
			this.spinner.style.right = `${formRect.right - inputRect.right + 12}px`;
			this.spinner.style.left = 'auto';
		}

		this.spinner.style.top = `${inputRect.top - formRect.top + inputRect.height / 2 - 8}px`;
		this.spinner.style.transform = 'none';
	}

	/**
	 * Positions the results container relative to the search input.
	 */
	positionResults() {
		const inputRect = this.searchInput.getBoundingClientRect();
		const formRect = this.form.getBoundingClientRect();

		this.resultsContainer.style.position = 'absolute';
		this.resultsContainer.style.top = `${inputRect.bottom - formRect.top + 4}px`;
		this.resultsContainer.style.left = `${inputRect.left - formRect.left}px`;
		this.resultsContainer.style.width = `${inputRect.width}px`;
	}

	/**
	 * Configures search input ARIA attributes.
	 */
	configureSearchInput() {
		Object.entries({
			role: 'combobox',
			autocomplete: 'off',
			'aria-autocomplete': 'list',
			'aria-controls': this.resultsContainer.id,
			'aria-expanded': 'false',
			autocapitalize: 'off',
			spellcheck: 'false',
		}).forEach(([key, value]) => {
			this.searchInput.setAttribute(key, value);
		});
	}

	/**
	 * Binds all event listeners.
	 */
	bindEvents() {
		this.form.addEventListener('submit', () => this.clearCache());
		this.searchInput.addEventListener(
			'input',
			this.handleInput.bind(this)
		);
		this.searchInput.addEventListener(
			'keydown',
			this.handleInputKeydown.bind(this)
		);
		this.searchInput.addEventListener(
			'focus',
			this.handleInputFocus.bind(this)
		);
		this.searchInput.addEventListener(
			'focusout',
			this.handleInputBlur.bind(this)
		);

		if (this.submitButton) {
			this.submitButton.addEventListener(
				'keydown',
				this.handleSubmitKeydown.bind(this)
			);
		}

		this.resultsContainer.addEventListener(
			'keydown',
			this.handleResultsKeydown.bind(this)
		);
		this.resultsContainer.addEventListener(
			'focusout',
			this.handleResultsBlur.bind(this)
		);
		this.resultsContainer.addEventListener(
			'focusin',
			this.handleResultsFocusin.bind(this)
		);

		document.addEventListener(
			'click',
			this.handleDocumentClick.bind(this)
		);
		window.addEventListener('resize', () => {
			this.positionResults();
			this.positionSpinner();
		});
	}

	/**
	 * Handles input changes with debouncing.
	 */
	handleInput() {
		clearTimeout(this.debounceTimer);
		this.debounceTimer = setTimeout(() => {
			const searchTerm = this.searchInput.value.trim();

			if (searchTerm.length > 2) {
				this.announce(wzkb_live_search.strings.searching);
				this.showSpinner();
				this.fetchResults(searchTerm);
			} else {
				this.announce(
					searchTerm.length === 0
						? ''
						: wzkb_live_search.strings.min_chars
				);
				this.clearResults();
			}
		}, WZKBSearchAutocomplete.DEBOUNCE_DELAY);
	}

	/**
	 * Handles keyboard navigation in input.
	 *
	 * @param {KeyboardEvent} event
	 */
	handleInputKeydown(event) {
		const items = this.resultsContainer.querySelectorAll('li');

		switch (event.key) {
			case 'Escape':
				event.preventDefault();
				this.clearResults();
				this.announce(wzkb_live_search.strings.suggestions_closed);
				break;

			case 'ArrowDown':
				event.preventDefault();
				this.handleArrowDown(items);
				break;

			case 'ArrowUp':
				event.preventDefault();
				this.handleArrowUp(items);
				break;

			case 'Enter':
				this.handleEnter(items, event);
				break;
		}
	}

	/**
	 * Handles ArrowDown navigation.
	 *
	 * @param {NodeList} items
	 */
	handleArrowDown(items) {
		if (!items.length && this.searchInput.value.length > 2) {
			this.fetchResults(this.searchInput.value);
			return;
		}
		if (this.selectedIndex === -1) {
			this.selectedIndex = 0;
		} else {
			this.selectedIndex = items.length
				? Math.min(this.selectedIndex + 1, items.length - 1)
				: 0;
		}
		this.updateSelection(items);
	}

	/**
	 * Handles ArrowUp navigation.
	 *
	 * @param {NodeList} items
	 */
	handleArrowUp(items) {
		if (!items.length) return;
		if (this.selectedIndex === -1) {
			this.selectedIndex = items.length - 1;
		} else {
			this.selectedIndex =
				this.selectedIndex > 0
					? this.selectedIndex - 1
					: items.length - 1;
		}
		this.updateSelection(items);
	}

	/**
	 * Handles Enter key.
	 *
	 * @param {NodeList}    items
	 * @param {KeyboardEvent} event
	 */
	handleEnter(items, event) {
		if (items.length && this.selectedIndex >= 0) {
			event.preventDefault();
			const selectedItem = items[this.selectedIndex];
			if (selectedItem?.dataset.href) {
				this.announce(
					wzkb_live_search.strings.navigating_to.replace(
						'%s',
						selectedItem.textContent
					)
				);
				window.location.href = selectedItem.dataset.href;
			}
		} else {
			this.announce(wzkb_live_search.strings.submitting_search);
			this.form.submit();
		}
	}

	/**
	 * Handles submit button keyboard events.
	 *
	 * @param {KeyboardEvent} event
	 */
	handleSubmitKeydown(event) {
		const items = this.resultsContainer.querySelectorAll('li');

		switch (event.key) {
			case 'Escape':
				event.preventDefault();
				this.clearResults();
				this.searchInput.focus();
				this.announce(wzkb_live_search.strings.suggestions_closed);
				break;

			case 'ArrowDown':
				if (!items.length) return;
				event.preventDefault();
				this.selectedIndex = 0;
				this.searchInput.focus();
				this.updateSelection(items);
				break;

			case 'ArrowUp':
				event.preventDefault();
				this.searchInput.focus();
				this.announce(wzkb_live_search.strings.back_to_input);
				break;
		}
	}

	/**
	 * Handles results container keyboard events.
	 *
	 * @param {KeyboardEvent} event
	 */
	handleResultsKeydown(event) {
		const items = this.resultsContainer.querySelectorAll('li');
		if (!items.length) return;

		switch (event.key) {
			case 'ArrowDown':
				event.preventDefault();
				this.selectedIndex = Math.min(
					this.selectedIndex + 1,
					items.length - 1
				);
				this.updateSelection(items);
				items[this.selectedIndex].focus();
				break;

			case 'ArrowUp':
				event.preventDefault();
				this.handleResultsArrowUp(items);
				break;

			case 'Escape':
				event.preventDefault();
				this.clearResults();
				this.searchInput.focus();
				this.announce(wzkb_live_search.strings.suggestions_closed);
				break;

			case 'Enter':
				event.preventDefault();
				this.handleResultsEnter(items);
				break;
		}
	}

	/**
	 * Handles ArrowUp in results.
	 *
	 * @param {NodeList} items
	 */
	handleResultsArrowUp(items) {
		if (this.selectedIndex === 0) {
			this.searchInput.focus();
			this.selectedIndex = -1;
			this.announce(wzkb_live_search.strings.back_to_search);
		} else {
			this.selectedIndex--;
			this.updateSelection(items);
			items[this.selectedIndex].focus();
		}
	}

	/**
	 * Handles Enter in results.
	 *
	 * @param {NodeList} items
	 */
	handleResultsEnter(items) {
		if (
			this.selectedIndex >= 0 &&
			this.selectedIndex < items.length
		) {
			const selectedItem = items[this.selectedIndex];
			if (selectedItem?.dataset.href) {
				this.announce(
					wzkb_live_search.strings.navigating_to.replace(
						'%s',
						selectedItem.textContent
					)
				);
				window.location.href = selectedItem.dataset.href;
			}
		}
	}

	/**
	 * Syncs selectedIndex and aria-selected when Tab moves focus into a result item.
	 *
	 * @param {FocusEvent} event
	 */
	handleResultsFocusin(event) {
		if (!event.target.matches('li[role="option"]')) return;
		const items = this.resultsContainer.querySelectorAll('li');
		items.forEach((item) => {
			item.classList.remove('selected', 'wzkb-selected');
			item.setAttribute('aria-selected', 'false');
		});
		event.target.classList.add('selected', 'wzkb-selected');
		event.target.setAttribute('aria-selected', 'true');
		this.selectedIndex = Array.from(items).indexOf(event.target);
		this.searchInput.setAttribute('aria-activedescendant', event.target.id);
	}

	/**
	 * Handles document clicks for closing suggestions.
	 *
	 * @param {MouseEvent} event
	 */
	handleDocumentClick(event) {
		if (
			!this.form.contains(event.target) &&
			!this.resultsContainer.contains(event.target)
		) {
			this.clearResults();
		}
	}

	/**
	 * Handles input focus.
	 */
	handleInputFocus() {
		if (
			this.resultsContainer.innerHTML.trim() &&
			this.searchInput.value.length > 2
		) {
			this.positionResults();
			this.resultsContainer.style.display = 'block';
		}
	}

	/**
	 * Handles input blur.
	 */
	handleInputBlur() {
		setTimeout(() => {
			const isSearchElement =
				document.activeElement?.closest(
					WZKBSearchAutocomplete.SELECTOR
				) !== null ||
				document.activeElement?.closest(
					'.wzkb-autocomplete-results'
				) !== null;
			if (!isSearchElement) {
				this.clearResults();
				this.announce(wzkb_live_search.strings.suggestions_closed);
			}
		}, 150);
	}

	/**
	 * Handles results container blur.
	 */
	handleResultsBlur() {
		setTimeout(() => {
			const isSearchElement =
				document.activeElement?.closest(
					WZKBSearchAutocomplete.SELECTOR
				) !== null ||
				document.activeElement?.closest(
					'.wzkb-autocomplete-results'
				) !== null;
			if (!isSearchElement) {
				this.clearResults();
				this.announce(wzkb_live_search.strings.suggestions_closed);
			}
		}, 150);
	}

	/**
	 * Updates screen reader announcements.
	 *
	 * @param {string} message
	 */
	announce(message) {
		this.announceRegion.textContent = message;
	}

	/**
	 * Clears search results.
	 */
	clearResults() {
		this.hideSpinner();
		if (this.abortController) {
			this.abortController.abort();
			this.abortController = null;
		}

		this.resultsContainer.innerHTML = '';
		this.resultsContainer.style.display = 'none';
		this.selectedIndex = -1;
		this.searchInput.removeAttribute('aria-activedescendant');
		this.searchInput.setAttribute('aria-expanded', 'false');
		this.announceRegion.textContent = '';
	}

	/**
	 * Updates selection state.
	 *
	 * @param {NodeList} items
	 */
	updateSelection(items) {
		items.forEach((item) => {
			item.classList.remove('selected');
			item.classList.remove('wzkb-selected');
			item.setAttribute('aria-selected', 'false');
		});

		const selectedItem = items[this.selectedIndex];
		if (selectedItem) {
			selectedItem.classList.add('selected');
			selectedItem.classList.add('wzkb-selected');
			selectedItem.setAttribute('aria-selected', 'true');
			selectedItem.scrollIntoView({ block: 'nearest' });
			this.searchInput.setAttribute(
				'aria-activedescendant',
				selectedItem.id
			);

			if (wzkb_live_search.strings.result_position) {
				const positionMessage =
					wzkb_live_search.strings.result_position
						.replace('%1$d', this.selectedIndex + 1)
						.replace('%2$d', items.length);
				this.announce(
					`${selectedItem.textContent}. ${positionMessage}`
				);
			} else {
				this.announce(selectedItem.textContent);
			}
		}
	}

	/**
	 * Gets cached results if available and not expired.
	 *
	 * @param {string} searchTerm
	 * @return {Array|null}
	 */
	getCachedResults(searchTerm) {
		const cached = this.cache.get(searchTerm);
		if (!cached) return null;

		const now = Date.now();
		if (now - cached.timestamp > WZKBSearchAutocomplete.CACHE_TIMEOUT) {
			this.cache.delete(searchTerm);
			return null;
		}

		return cached.data;
	}

	/**
	 * Clears the results cache.
	 */
	clearCache() {
		this.cache.clear();
	}

	/**
	 * Shows the loading spinner.
	 */
	showSpinner() {
		this.positionSpinner();
		this.spinner.style.display = 'inline-block';
	}

	/**
	 * Hides the loading spinner.
	 */
	hideSpinner() {
		this.spinner.style.display = 'none';
	}

	/**
	 * Fetches search results via AJAX.
	 *
	 * @param {string} searchTerm
	 */
	async fetchResults(searchTerm) {
		const cached = this.getCachedResults(searchTerm);
		if (cached) {
			this.hideSpinner();
			this.displayResults(cached, searchTerm);
			return;
		}

		let controller = null;

		try {
			if (this.abortController) {
				this.abortController.abort();
			}

			controller = new AbortController();
			this.abortController = controller;

			const formData = new FormData();
			formData.append('action', 'wzkb_live_search');
			formData.append('s', searchTerm);

			// Pass product_id if available as a data attribute on the form.
			const productId = this.form.dataset.productId;
			if (productId) {
				formData.append('product_id', productId);
			}

			const response = await fetch(wzkb_live_search.ajax_url, {
				method: 'POST',
				body: formData,
				signal: controller.signal,
			});

			if (!response.ok) {
				throw new Error(
					`HTTP error! status: ${response.status}`
				);
			}

			const data = await response.json();

			this.cache.set(searchTerm, {
				data,
				timestamp: Date.now(),
			});

			if (this.searchInput.value.trim() !== searchTerm) {
				return;
			}

			this.displayResults(data, searchTerm);
		} catch (error) {
			if (error.name === 'AbortError') {
				return;
			}

			this.showError(wzkb_live_search.strings.error_loading);
		} finally {
			this.hideSpinner();
			if (this.abortController === controller) {
				this.abortController = null;
			}
		}
	}

	/**
	 * Displays search results.
	 *
	 * @param {Object} data
	 * @param {string} searchTerm
	 */
	displayResults(data, searchTerm) {
		this.resultsContainer.innerHTML = '';
		this.selectedIndex = -1;

		if (!data.results || !data.results.length) {
			this.showNoResults();
			this.announce(wzkb_live_search.strings.no_results);
			return;
		}

		const ul = document.createElement('ul');
		ul.className = 'wzkb-results-list';

		data.results.forEach((result, index) => {
			const li = document.createElement('li');
			li.id = `wzkb-search-suggestion-${this.instanceId}-${index}`;
			li.setAttribute('role', 'option');
			li.setAttribute('aria-selected', 'false');
			li.setAttribute('tabindex', '0');
			li.className = 'wzkb-result';
			li.textContent = result.title;
			li.dataset.href = result.link;
			li.addEventListener('click', () => {
				window.location.href = result.link;
			});
			ul.appendChild(li);
		});

		this.resultsContainer.appendChild(ul);
		this.positionResults();
		this.resultsContainer.style.display = 'block';
		this.searchInput.setAttribute('aria-expanded', 'true');

		const count = data.results.length;
		const foundMessage = wzkb_live_search.strings.suggestions_found.replace(
			'%d',
			count
		);
		this.announce(foundMessage);
	}

	/**
	 * Shows "No results" message.
	 */
	showNoResults() {
		const p = document.createElement('p');
		p.className = 'wzkb-no-results';
		p.textContent = wzkb_live_search.strings.no_suggestions;
		this.resultsContainer.appendChild(p);
		this.positionResults();
		this.resultsContainer.style.display = 'block';
		this.searchInput.setAttribute('aria-expanded', 'true');
	}

	/**
	 * Shows an error message.
	 *
	 * @param {string} message
	 */
	showError(message) {
		const p = document.createElement('p');
		p.className = 'wzkb-search-error';
		p.textContent = message;
		this.resultsContainer.innerHTML = '';
		this.resultsContainer.appendChild(p);
		this.positionResults();
		this.resultsContainer.style.display = 'block';
		this.searchInput.setAttribute('aria-expanded', 'true');
		this.announce(message);
	}
}

// Initialise on DOM ready.
document.addEventListener('DOMContentLoaded', () => {
	document
		.querySelectorAll(WZKBSearchAutocomplete.SELECTOR)
		.forEach((form) => new WZKBSearchAutocomplete(form));
});
