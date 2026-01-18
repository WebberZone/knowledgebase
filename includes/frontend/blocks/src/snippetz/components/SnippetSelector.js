/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { ComboboxControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';
import { useState } from '@wordpress/element';

/**
 * Get formatted label with snippet type
 *
 * @param {Object} snippet Snippet object with title and meta
 * @return {string} Formatted label
 */
const getFormattedLabel = (snippet) => {
	const title = snippet.title.rendered || __('(No title)', 'add-to-all');
	const type = snippet.meta._ata_snippet_type || 'html';
	return `${title} (${type.toUpperCase()})`;
};

/**
 * SnippetSelector component for selecting a snippet from the available snippets.
 *
 * @param {Object} props Component props.
 * @param {number} props.value Currently selected snippet ID.
 * @param {Function} props.onChange Callback function when selection changes.
 * @return {Element} Component to render.
 */
export default function SnippetSelector({ value, onChange }) {
	const [searchInput, setSearchInput] = useState('');

	const { snippets, selectedSnippet, isLoading } = useSelect(
		(select) => {
			const { getEntityRecords, getEntityRecord } = select(coreDataStore);
			const query = {
				per_page: 20,
				_fields: ['id', 'title', 'meta'],
				orderby: 'title',
				order: 'asc',
				search: searchInput || undefined,
			};

			return {
				snippets: getEntityRecords('postType', 'ata_snippets', query),
				selectedSnippet: value
					? getEntityRecord('postType', 'ata_snippets', value, {
							_fields: ['id', 'title', 'meta'],
						})
					: null,
				isLoading: select(coreDataStore).isResolving(
					'getEntityRecords',
					['postType', 'ata_snippets', query]
				),
			};
		},
		[searchInput, value]
	);

	const options = (snippets || []).map((snippet) => ({
		value: snippet.id,
		label: getFormattedLabel(snippet),
	}));

	// If we have a selected value but it's not in the current options,
	// add it to the options list
	if (selectedSnippet && !options.find((option) => option.value === value)) {
		options.unshift({
			value: selectedSnippet.id,
			label: getFormattedLabel(selectedSnippet),
		});
	}

	return (
		<ComboboxControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={__('Choose Snippet', 'add-to-all')}
			value={value || 0}
			onChange={(newValue) =>
				onChange(newValue ? parseInt(newValue, 10) : 0)
			}
			options={options}
			onFilterValueChange={(inputValue) => setSearchInput(inputValue)}
		/>
	);
}
