/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	PanelRow,
	TextControl,
	TextareaControl,
	ToggleControl,
	RangeControl,
	Placeholder,
	ComboboxControl,
	SelectControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { bookIcon } from '../components/icons';
import SectionSelector from '../components/section-selector';
import './editor.scss';

/**
 * Custom hook for handling knowledge base settings
 *
 * @param {Object}   attributes    - The block's attributes.
 * @param {Function} setAttributes - The function to update the attributes.
 */
const useKnowledgeBaseSettings = (attributes, setAttributes) => {
	const processNumber = (input) => {
		return input === undefined ||
			input === 0 ||
			input === '' ||
			isNaN(input)
			? ''
			: parseInt(input);
	};

	const updateAttribute = (name, value) => {
		setAttributes({ [name]: value });
	};

	const toggleAttribute = (name) => {
		setAttributes({ [name]: !attributes[name] });
	};

	return {
		processNumber,
		updateAttribute,
		toggleAttribute,
	};
};

/**
 * Settings Panel Component
 *
 * @param {Object}   props                   - The component props.
 * @param {Object}   props.attributes        - The block's attributes.
 * @param {Function} props.onUpdateAttribute - The function to update the attributes.
 * @param {Function} props.onToggleAttribute - The function to toggle the attributes.
 * @param {Function} props.processNumber     - The function to process the number.
 */
const KnowledgeBaseSettings = ({
	attributes,
	onUpdateAttribute,
	onToggleAttribute,
	processNumber,
}) => {
	const {
		category,
		productId,
		limit,
		showArticleCount,
		showExcerpt,
		hasClickableSection,
		showEmptySections,
		title,
		showHeading,
		linkHeading,
		headingLevel,
		other_attributes,
	} = attributes;

	const pluginSettings = window.wzkbKB?.settings || {};
	const effectiveShowArticleCount =
		showArticleCount ?? !!pluginSettings.show_article_count;
	const effectiveShowExcerpt = showExcerpt ?? !!pluginSettings.show_excerpt;
	const effectiveHasClickableSection =
		hasClickableSection ?? !!pluginSettings.clickable_section;
	const effectiveShowEmptySections =
		showEmptySections ?? !!pluginSettings.show_empty_sections;
	const parsedLimit = parseInt(pluginSettings.limit);
	const effectiveLimit = limit ?? (isNaN(parsedLimit) ? 5 : parsedLimit);

	const sectionSelectorProps = {
		label: __('Filter by Section', 'knowledgebase'),
		value: parseInt(category) || 0,
		onChange: (value) => onUpdateAttribute('category', value.toString()),
		taxonomy: 'wzkb_category',
		includeEmptyLabel: __('Select a section', 'knowledgebase'),
		className: 'wzkb-products-selector',
		wrapperClass: 'wzkb-products-selector-wrapper',
		help: __('Search and select a knowledge base section', 'knowledgebase'),
	};

	const filterSectionsByProduct = productId
		? {
				filterTerm: (term) =>
					term.kb_product && term.kb_product.term_id === productId,
			}
		: {};

	const productSelectorProps = {
		taxonomy: 'wzkb_product',
		label: __('Filter by Product', 'knowledgebase'),
		value: productId,
		onChange: (value) => {
			onUpdateAttribute('productId', value);
			onUpdateAttribute('category', '');
		},
		includeEmptyLabel: __('Select a product', 'knowledgebase'),
		formatLabel: (term) => term.name,
		className: 'wzkb-products-selector',
		wrapperClass: 'wzkb-products-selector-wrapper',
		help: __('Search and select a product', 'knowledgebase'),
	};

	return (
		<PanelBody
			title={__('Knowledge Base Settings', 'knowledgebase')}
			initialOpen={true}
		>
			<PanelRow>
				<SectionSelector {...productSelectorProps} />
			</PanelRow>
			<strong>{__('OR', 'knowledgebase')}</strong>
			<PanelRow>
				<SectionSelector
					{...sectionSelectorProps}
					{...filterSectionsByProduct}
				/>
			</PanelRow>

			<PanelRow>
				<RangeControl
					label={__('Max articles per section', 'knowledgebase')}
					value={effectiveLimit}
					onChange={(value) => onUpdateAttribute('limit', value)}
					min={-1}
					max={20}
					help={__(
						'-1 for unlimited articles. After this limit is reached, the footer is displayed with the more link to view the category.',
						'knowledgebase'
					)}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show article count', 'knowledgebase')}
					checked={effectiveShowArticleCount}
					onChange={() =>
						onUpdateAttribute(
							'showArticleCount',
							!effectiveShowArticleCount
						)
					}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show excerpt', 'knowledgebase')}
					checked={effectiveShowExcerpt}
					onChange={() =>
						onUpdateAttribute('showExcerpt', !effectiveShowExcerpt)
					}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Link section title', 'knowledgebase')}
					help={
						effectiveHasClickableSection
							? __(
									'Section title links to the section page',
									'knowledgebase'
								)
							: __('Section title is plain text', 'knowledgebase')
					}
					checked={effectiveHasClickableSection}
					onChange={() =>
						onUpdateAttribute(
							'hasClickableSection',
							!effectiveHasClickableSection
						)
					}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show empty sections', 'knowledgebase')}
					checked={effectiveShowEmptySections}
					onChange={() =>
						onUpdateAttribute(
							'showEmptySections',
							!effectiveShowEmptySections
						)
					}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show heading', 'knowledgebase')}
					checked={showHeading}
					onChange={() => onToggleAttribute('showHeading')}
				/>
			</PanelRow>
			{showHeading && (
				<PanelRow>
					<ToggleControl
						label={__('Link heading to selection', 'knowledgebase')}
						checked={linkHeading}
						onChange={() => onToggleAttribute('linkHeading')}
						help={
							linkHeading
								? __(
										'Heading will link to the selected product or section.',
										'knowledgebase'
									)
								: __(
										'Heading will remain plain text.',
										'knowledgebase'
									)
						}
					/>
				</PanelRow>
			)}
			{showHeading && (
				<PanelRow>
					<SelectControl
						label={__('Heading Level', 'knowledgebase')}
						value={headingLevel}
						onChange={(value) =>
							onUpdateAttribute('headingLevel', value)
						}
						options={[
							{ label: 'H1', value: 'h1' },
							{ label: 'H2', value: 'h2' },
							{ label: 'H3', value: 'h3' },
							{ label: 'H4', value: 'h4' },
							{ label: 'H5', value: 'h5' },
							{ label: 'H6', value: 'h6' },
							{ label: 'Paragraph', value: 'p' },
						]}
					/>
				</PanelRow>
			)}

			<PanelRow>
				<TextareaControl
					label={__('Other attributes', 'knowledgebase')}
					value={other_attributes}
					onChange={(value) =>
						onUpdateAttribute('other_attributes', value || '')
					}
					help={__(
						'Enter other attributes in a URL-style string-query.',
						'knowledgebase'
					)}
				/>
			</PanelRow>
		</PanelBody>
	);
};

/**
 * Edit component for the Knowledge Base block
 *
 * @param {Object}   attributes    - The block's attributes.
 * @param {Function} setAttributes - The function to update the attributes.
 */
export default function Edit({ attributes, setAttributes }) {
	const { category, productId } = attributes;
	const blockProps = useBlockProps();
	const { processNumber, updateAttribute, toggleAttribute } =
		useKnowledgeBaseSettings(attributes, setAttributes);

	const renderInspectorControls = () => (
		<InspectorControls>
			<KnowledgeBaseSettings
				attributes={attributes}
				onUpdateAttribute={updateAttribute}
				onToggleAttribute={toggleAttribute}
				processNumber={processNumber}
			/>
		</InspectorControls>
	);

	return (
		<>
			{renderInspectorControls()}

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/knowledgebase"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
