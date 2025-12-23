/**
 * Gutenberg product-aware sections panel.
 *
 * @package WebberZone\Knowledge_Base
 */

/* global WZKBEditorSections */

let wzkbSectionsNonceMiddlewareAdded = false;

(function () {
    if (
        !window.wp ||
        !window.wp.data ||
        !window.wp.components ||
        !window.wp.element ||
        !window.wp.hooks
    ) {
        return;
    }

    const config = window.WZKBEditorSections || {};
    if (!config.endpoint) {
        return;
    }

    const strings = config.strings || {};
    const {
        components: { Spinner, Notice, CheckboxControl },
        element: { createElement: el, useEffect, useMemo, useState },
        data: { useSelect, useDispatch },
        hooks: { addFilter },
    } = window.wp;
    const apiFetch = window.wp.apiFetch;

    if (!addFilter || !CheckboxControl || !useSelect || !apiFetch) {
        return;
    }

    if (config.nonce && apiFetch.createNonceMiddleware && !wzkbSectionsNonceMiddlewareAdded) {
        apiFetch.use(apiFetch.createNonceMiddleware(config.nonce));
        wzkbSectionsNonceMiddlewareAdded = true;
    }

    const normalizeIds = (values) => {
        if (!Array.isArray(values)) {
            return [];
        }

        return values
            .map((value) => parseInt(value, 10))
            .filter((value) => Number.isFinite(value) && value > 0);
    };

    const sortNumeric = (values) => {
        return values.slice().sort((a, b) => a - b);
    };

    const arraysEqual = (a, b) => {
        if (a.length !== b.length) {
            return false;
        }

        const sortedA = sortNumeric(a);
        const sortedB = sortNumeric(b);

        for (let index = 0; index < sortedA.length; index += 1) {
            if (sortedA[index] !== sortedB[index]) {
                return false;
            }
        }

        return true;
    };

    const buildTree = (terms) => {
        if (!Array.isArray(terms) || !terms.length) {
            return [];
        }

        const byId = new Map();

        terms.forEach((term) => {
            const node = {
                id: term.id,
                name: term.name,
                parent: term.parent,
                children: [],
            };
            byId.set(node.id, node);
        });

        const roots = [];
        byId.forEach((node) => {
            if (node.parent && byId.has(node.parent)) {
                byId.get(node.parent).children.push(node);
            } else {
                roots.push(node);
            }
        });

        const sortRecursive = (nodes) => {
            return nodes
                .sort((a, b) => a.name.localeCompare(b.name))
                .map((node) => ({
                    ...node,
                    children: sortRecursive(node.children),
                }));
        };

        return sortRecursive(roots);
    };

    const useSections = (products) => {
        const [state, setState] = useState({
            items: [],
            isLoading: false,
            error: null,
        });

        const productKey = sortNumeric(products).join(',');

        useEffect(() => {
            if (!products.length) {
                setState({ items: [], isLoading: false, error: null });
                return;
            }

            let isCancelled = false;

            setState((prev) => ({
                ...prev,
                isLoading: true,
                error: null,
            }));

            const query = encodeURIComponent(productKey);

            apiFetch({ url: config.endpoint + '?products=' + query })
                .then((terms) => {
                    if (isCancelled) {
                        return;
                    }

                    setState({
                        items: Array.isArray(terms) ? terms : [],
                        isLoading: false,
                        error: null,
                    });
                })
                .catch((error) => {
                    if (isCancelled) {
                        return;
                    }

                    setState({
                        items: [],
                        isLoading: false,
                        error,
                    });
                });

            return () => {
                isCancelled = true;
            };
        }, [productKey]);

        return state;
    };

    const getLatestMeta = () => {
        if (!window.wp || !window.wp.data) {
            return {};
        }

        return window.wp.data.select('core/editor').getEditedPostAttribute('meta') || {};
    };

    const formatProductTitle = (productId) => {
        const productName =
            (productId && config.products && config.products[productId]) || `Product ${productId}`;
        const template = strings.productHeading || '%s sections';
        return template.replace('%s', productName);
    };

    const groupSectionsByProduct = (terms, selectedProducts) => {
        if (!Array.isArray(terms) || !terms.length) {
            return [];
        }

        const groups = new Map();

        terms.forEach((term) => {
            const productId = Number.isFinite(parseInt(term.product, 10))
                ? parseInt(term.product, 10)
                : 0;

            if (productId > 0 && !selectedProducts.includes(productId)) {
                return;
            }

            const bucket = productId > 0 ? productId : 0;

            if (!groups.has(bucket)) {
                groups.set(bucket, []);
            }

            groups.get(bucket).push(term);
        });

        const orderedGroups = [];

        selectedProducts.forEach((productId) => {
            if (groups.has(productId)) {
                orderedGroups.push({
                    productId,
                    label: formatProductTitle(productId),
                    nodes: buildTree(groups.get(productId)),
                });
                groups.delete(productId);
            }
        });

        if (groups.has(0)) {
            orderedGroups.push({
                productId: 0,
                label: strings.unassigned || 'Sections without a product',
                nodes: buildTree(groups.get(0)),
            });
            groups.delete(0);
        }

        groups.forEach((bucketTerms, productId) => {
            orderedGroups.push({
                productId,
                label: formatProductTitle(productId),
                nodes: buildTree(bucketTerms),
            });
        });

        return orderedGroups.filter((group) => group.nodes.length);
    };

    const SectionTree = ({ nodes, selectedIds, onToggle, level }) => {
        if (!nodes || !nodes.length) {
            return null;
        }

        return nodes.map((node) => {
            const key = 'section-node-' + node.id;
            const indentation = level > 0 ? level * 24 : 0;
            const className =
                'wzkb-editor-sections__node' + (level > 0 ? ' wzkb-editor-sections__node--child' : '');

            return el(
                'div',
                { key, className, style: { marginLeft: indentation } },
                el(CheckboxControl, {
                    label: node.name,
                    checked: selectedIds.has(node.id),
                    onChange: (checked) => onToggle(node.id, checked),
                }),
                node.children && node.children.length
                    ? el(SectionTree, {
                        nodes: node.children,
                        selectedIds,
                        onToggle,
                        level: level + 1,
                    })
                    : null
            );
        });
    };

    const SectionsPanel = () => {
        const meta = useSelect((select) => select('core/editor').getEditedPostAttribute('meta') || {}, []);
        const productMeta = normalizeIds(meta._wzkb_product_ids || []);
        const sectionMeta = normalizeIds(meta._wzkb_section_ids || []);

        const taxonomyProducts = normalizeIds(useSelect((select) => select('core/editor').getEditedPostAttribute('wzkb_product') || [], []));
        const taxonomySections = normalizeIds(useSelect((select) => select('core/editor').getEditedPostAttribute('wzkb_category') || [], []));

        const { editPost } = useDispatch('core/editor');

        useEffect(() => {
            if (arraysEqual(productMeta, taxonomyProducts)) {
                return;
            }

            const latestMeta = getLatestMeta();
            editPost({
                meta: {
                    ...latestMeta,
                    _wzkb_product_ids: taxonomyProducts,
                },
            });
        }, [productMeta.join(','), taxonomyProducts.join(','), editPost]);

        useEffect(() => {
            if (arraysEqual(taxonomySections, sectionMeta)) {
                return;
            }

            editPost({
                wzkb_category: sectionMeta,
            });
        }, [taxonomySections.join(','), sectionMeta.join(','), editPost]);

        const { items: sectionTerms, isLoading, error } = useSections(productMeta);
        const groupedSections = useMemo(
            () => groupSectionsByProduct(sectionTerms, productMeta),
            [sectionTerms, productMeta.join(',')]
        );
        const selectedIds = useMemo(() => new Set(sectionMeta), [sectionMeta.join(',')]);

        const toggleSection = (sectionId, isChecked) => {
            const updated = new Set(selectedIds);
            if (isChecked) {
                updated.add(sectionId);
            } else {
                updated.delete(sectionId);
            }

            const nextSections = Array.from(updated).sort((a, b) => a - b);
            const latestMeta = getLatestMeta();

            editPost({
                meta: {
                    ...latestMeta,
                    _wzkb_section_ids: nextSections,
                },
                wzkb_category: nextSections,
            });
        };

        const panelContents = () => {
            if (!productMeta.length) {
                return el('p', { className: 'wzkb-editor-sections__message' }, strings.selectProducts || 'Select a product to load its sections.');
            }

            if (error) {
                return el(
                    Notice,
                    { status: 'error', isDismissible: false, className: 'wzkb-editor-sections__notice' },
                    strings.error || 'Unable to load sections. Please try again.'
                );
            }

            if (isLoading) {
                return el(
                    'div',
                    { className: 'wzkb-editor-sections__loading' },
                    el(Spinner, null),
                    el('p', null, strings.loading || 'Loading sections…')
                );
            }

            if (!groupedSections.length) {
                return el(
                    'p',
                    { className: 'wzkb-editor-sections__empty' },
                    strings.noSections || 'No sections match the selected products.'
                );
            }

            return groupedSections.map((group) =>
                el(
                    'div',
                    { key: 'group-' + group.productId, className: 'wzkb-editor-sections__group' },
                    el('h4', { className: 'wzkb-editor-sections__group-title' }, group.label),
                    el(
                        'div',
                        { className: 'wzkb-editor-sections__tree' },
                        el(SectionTree, {
                            nodes: group.nodes,
                            selectedIds,
                            onToggle: toggleSection,
                            level: 0,
                        })
                    )
                )
            );
        };

        const contents = panelContents();

        return el('div', { className: 'wzkb-editor-sections__container' }, contents);
    };

    addFilter('editor.PostTaxonomyType', 'wzkb/custom-sections-panel', (OriginalComponent) => {
        return function WrappedComponent(props) {
            if (!props || props.slug !== 'wzkb_category') {
                return el(OriginalComponent, props);
            }

            return el(SectionsPanel, { label: props.label });
        };
    });
})();
