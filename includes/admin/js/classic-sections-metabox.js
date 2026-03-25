/* global WZKBClassicSections */
(function (window, document) {
	if (!window.wp || !window.wp.apiFetch || !document) {
		return;
	}

	const config = window.WZKBClassicSections || {};
	if (!config.endpoint) {
		return;
	}

	const apiFetch = window.wp.apiFetch;
	if (config.nonce && apiFetch.createNonceMiddleware) {
		if (!window.wzkbClassicNonceMiddlewareAdded) {
			apiFetch.use(apiFetch.createNonceMiddleware(config.nonce));
			window.wzkbClassicNonceMiddlewareAdded = true;
		}
	}

	const root = document.querySelector('.wzkb-classic-sections[data-role="root"]');
	if (!root) {
		return;
	}

	const productsContainer = root.querySelector('[data-role="products"]');
	const sectionsContainer = root.querySelector('[data-role="sections"]');
	const productInput = document.getElementById('wzkb_classic_product_ids');
	const sectionInput = document.getElementById('wzkb_classic_section_ids');
	const searchInput = root.querySelector('[data-role="product-search"]');

	const strings = config.strings || {};
	const productMap = config.products || {};

	const selectedProducts = new Set(Array.isArray(config.meta?.products) ? config.meta.products : []);
	const selectedSections = new Set(Array.isArray(config.meta?.sections) ? config.meta.sections : []);

	const MAX_RENDERED_PRODUCTS = 15;
	const cachedResponses = new Map();
	let isLoading = false;
	let lastError = null;
	let inflightKey = null;
	let productFilter = '';

	const normalizeProductEntries = () => {
		return Object.keys(productMap)
			.map((id) => {
				const termId = parseInt(id, 10);
				return {
					id: termId,
					label: productMap[id],
				};
			})
			.filter((entry) => Number.isFinite(entry.id) && entry.id > 0)
			.sort((a, b) => (a.label || '').localeCompare(b.label || ''));
	};

	const allProducts = normalizeProductEntries();

	const normalize = (value) => {
		const items = Array.isArray(value) ? value : [];
		return items
			.map((item) => parseInt(item, 10))
			.filter((item) => Number.isFinite(item) && item > 0);
	};

	const serialize = (set) => Array.from(set).sort((a, b) => a - b).join(',');

	const updateHiddenInputs = () => {
		if (productInput) {
			productInput.value = serialize(selectedProducts);
		}
		if (sectionInput) {
			sectionInput.value = serialize(selectedSections);
		}
	};

	const createElement = (tag, className, text) => {
		const el = document.createElement(tag);
		if (className) {
			el.className = className;
		}
		if (text) {
			el.textContent = text;
		}
		return el;
	};

	const renderMessage = (container, message) => {
		container.innerHTML = '';
		container.appendChild(createElement('p', 'wzkb-classic-sections__message', message));
	};

	const buildTree = (terms) => {
		if (!Array.isArray(terms)) {
			return [];
		}
		const byId = new Map();
		terms.forEach((term) => {
			byId.set(term.id, {
				id: term.id,
				name: term.name,
				parent: term.parent,
				children: [],
			});
		});

		const roots = [];
		byId.forEach((node) => {
			if (node.parent && byId.has(node.parent)) {
				byId.get(node.parent).children.push(node);
			} else {
				roots.push(node);
			}
		});

		const sortNodes = (nodes) =>
			nodes
				.sort((a, b) => a.name.localeCompare(b.name))
				.map((node) => ({
					...node,
					children: sortNodes(node.children),
				}));

		return sortNodes(roots);
	};

	const groupByProduct = (terms, activeProducts) => {
		if (!Array.isArray(terms) || !activeProducts.length) {
			return [];
		}

		const groups = new Map();
		terms.forEach((term) => {
			const productId = Number.isFinite(parseInt(term.product, 10)) ? parseInt(term.product, 10) : 0;
			const bucket = productId > 0 ? productId : 0;
			if (productId > 0 && !activeProducts.includes(productId)) {
				return;
			}
			if (!groups.has(bucket)) {
				groups.set(bucket, []);
			}
			groups.get(bucket).push(term);
		});

		const ordered = [];
		activeProducts.forEach((productId) => {
			if (groups.has(productId)) {
				ordered.push({
					productId,
					label: (strings.productHeading || '%s sections').replace('%s', productMap[productId] || productId),
					nodes: buildTree(groups.get(productId)),
				});
				groups.delete(productId);
			}
		});

		if (groups.has(0)) {
			ordered.push({
				productId: 0,
				label: strings.unassigned || 'Sections without a product',
				nodes: buildTree(groups.get(0)),
			});
			groups.delete(0);
		}

		groups.forEach((nodes, productId) => {
			ordered.push({
				productId,
				label: (strings.productHeading || '%s sections').replace('%s', productMap[productId] || productId),
				nodes: buildTree(nodes),
			});
		});

		return ordered.filter((group) => group.nodes.length);
	};

	const getFilteredProducts = () => {
		if (!productFilter) {
			return allProducts;
		}

		const needle = productFilter.toLowerCase();
		return allProducts.filter((entry) => {
			const label = entry.label || '';
			return label.toLowerCase().indexOf(needle) !== -1 || String(entry.id).indexOf(needle) !== -1;
		});
	};

	const renderProducts = () => {
		productsContainer.innerHTML = '';

		const entries = getFilteredProducts();
		if (!entries.length) {
			renderMessage(productsContainer, strings.noProductMatches || 'No products match your search.');
			return;
		}

		const list = createElement('div', 'wzkb-classic-sections__product-list');
		const limitedEntries = entries.slice(0, MAX_RENDERED_PRODUCTS);

		limitedEntries.forEach((entry) => {
			const wrapper = createElement('label', 'wzkb-classic-sections__product-item');
			const checkbox = createElement('input');
			checkbox.type = 'checkbox';
			checkbox.value = entry.id;
			checkbox.checked = selectedProducts.has(entry.id);
			checkbox.addEventListener('change', (event) => {
				if (event.target.checked) {
					selectedProducts.add(entry.id);
				} else {
					selectedProducts.delete(entry.id);
				}
				updateHiddenInputs();
				loadSections();
			});

			const label = createElement('span', 'wzkb-classic-sections__product-label', entry.label || `Product ${entry.id}`);
			wrapper.appendChild(checkbox);
			wrapper.appendChild(label);
			list.appendChild(wrapper);
		});

		if (entries.length > MAX_RENDERED_PRODUCTS) {
			const note = createElement(
				'p',
				'wzkb-classic-sections__product-note',
				(strings.productOverflow || 'Showing first %1$s products out of %2$s. Refine your search.')
					.replace('%1$s', MAX_RENDERED_PRODUCTS)
					.replace('%2$s', entries.length)
			);
			list.appendChild(note);
		}

		productsContainer.appendChild(list);
	};

	const bindSearch = () => {
		if (!searchInput) {
			return;
		}

		if (strings.searchPlaceholder) {
			searchInput.placeholder = strings.searchPlaceholder;
		}

		searchInput.addEventListener(
			'input',
			(() => {
				let timeout = null;
				return function (event) {
					window.clearTimeout(timeout);
					timeout = window.setTimeout(() => {
						productFilter = (event.target.value || '').trim();
						renderProducts();
					}, 150);
				};
			})()
		);
	};

	const renderSections = (terms) => {
		sectionsContainer.innerHTML = '';

		if (!selectedProducts.size) {
			renderMessage(sectionsContainer, strings.selectProducts || 'Select a product to load its sections.');
			return;
		}

		if (isLoading) {
			renderMessage(sectionsContainer, strings.loading || 'Loading sections…');
			return;
		}

		if (lastError) {
			renderMessage(sectionsContainer, strings.error || 'Unable to load sections.');
			return;
		}

		const grouped = groupByProduct(terms, Array.from(selectedProducts));
		if (!grouped.length) {
			renderMessage(sectionsContainer, strings.noSections || 'No sections match the selected products.');
			return;
		}

		grouped.forEach((group) => {
			const groupEl = createElement('div', 'wzkb-classic-sections__group');
			groupEl.appendChild(createElement('h4', 'wzkb-classic-sections__group-title', group.label));
			const treeEl = createElement('div', 'wzkb-classic-sections__tree');
			appendTree(treeEl, group.nodes, 0);
			groupEl.appendChild(treeEl);
			sectionsContainer.appendChild(groupEl);
		});
	};

	const appendTree = (container, nodes, level) => {
		nodes.forEach((node) => {
			const row = createElement('div', 'wzkb-classic-sections__node');
			row.style.marginLeft = `${level * 16}px`;

			const checkbox = createElement('input');
			checkbox.type = 'checkbox';
			checkbox.value = node.id;
			checkbox.checked = selectedSections.has(node.id);
			checkbox.addEventListener('change', (event) => {
				if (event.target.checked) {
					selectedSections.add(node.id);
				} else {
					selectedSections.delete(node.id);
				}
				updateHiddenInputs();
			});

			const label = createElement('span', 'wzkb-classic-sections__node-label', node.name);
			row.appendChild(checkbox);
			row.appendChild(label);
			container.appendChild(row);

			if (node.children && node.children.length) {
				appendTree(container, node.children, level + 1);
			}
		});
	};

	const loadSections = () => {
		const ids = Array.from(selectedProducts);
		if (!ids.length) {
			lastError = null;
			renderSections([]);
			return;
		}

		const key = ids.sort((a, b) => a - b).join(',');
		if (cachedResponses.has(key)) {
			lastError = null;
			renderSections(cachedResponses.get(key));
			return;
		}

		isLoading = true;
		lastError = null;
		inflightKey = key;
		renderSections([]);

		apiFetch({ url: `${config.endpoint}?products=${encodeURIComponent(key)}` })
			.then((terms) => {
				if (inflightKey !== key) {
					return;
				}
				isLoading = false;
				cachedResponses.set(key, Array.isArray(terms) ? terms : []);
				renderSections(cachedResponses.get(key));
			})
			.catch((error) => {
				if (inflightKey !== key) {
					return;
				}
				isLoading = false;
				lastError = error;
				renderSections([]);
			});
	};

	const initialize = () => {
		const initialProducts = normalize(config.meta?.products || []);
		const initialSections = normalize(config.meta?.sections || []);

		selectedProducts.clear();
		initialProducts.forEach((id) => selectedProducts.add(id));
		selectedSections.clear();
		initialSections.forEach((id) => selectedSections.add(id));

		updateHiddenInputs();
		bindSearch();
		renderProducts();
		loadSections();
	};

	initialize();
})(window, document);
