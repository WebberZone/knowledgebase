import { __ } from '@wordpress/i18n';
import { ComboboxControl, Notice, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

const SectionSelector = ({
    value,
    onChange,
    label,
    help,
    taxonomy = 'wzkb_category',
    includeEmptyLabel = '',
    formatLabel,
    className = '',
    wrapperClass = '',
    queryArgs = {},
    filterTerm,
}) => {
    const { terms, hasResolved, error } = useSelect(
        (select) => {
            const query = {
                per_page: -1,
                hide_empty: 1,
            };

            const selectorArgs = [
                'taxonomy',
                taxonomy,
                {
                    ...query,
                    ...queryArgs,
                },
            ];

            try {
                return {
                    terms: select(coreStore).getEntityRecords(...selectorArgs),
                    hasResolved: select(coreStore).hasFinishedResolution(
                        'getEntityRecords',
                        selectorArgs
                    ),
                    error: null,
                };
            } catch (fetchError) {
                return {
                    terms: [],
                    hasResolved: true,
                    error: fetchError,
                };
            }
        },
        [taxonomy]
    );

    const defaultLabel = (term) => {
        const baseLabel = `#${term.id} - ${term.name}`;
        if ('wzkb_category' === taxonomy && term.kb_product?.name) {
            return `${baseLabel} (${term.kb_product.name})`;
        }
        return baseLabel;
    };

    const filteredTerms =
        typeof filterTerm === 'function' ? terms?.filter(filterTerm) : terms;

    const options =
        filteredTerms?.map((term) => ({
            label: (formatLabel || defaultLabel)(term),
            value: term.id.toString(),
        })) || [];

    if (includeEmptyLabel) {
        options.unshift({ value: '', label: includeEmptyLabel });
    }

    const inputValue = value && value > 0 ? value.toString() : '';

    return (
        <>
            {error && (
                <Notice status="error" isDismissible={false}>
                    {__(
                        'Error loading categories. Please try again.',
                        'knowledgebase'
                    )}
                </Notice>
            )}

            {!hasResolved ? (
                <Spinner />
            ) : (
                <div className={wrapperClass}>
                    <ComboboxControl
                        className={className}
                        label={label}
                        value={inputValue}
                        onChange={(selected) =>
                            onChange(parseInt(selected, 10) || 0)
                        }
                        options={options}
                        placeholder={includeEmptyLabel}
                        help={help}
                    />
                </div>
            )}
        </>
    );
};

export default SectionSelector;
