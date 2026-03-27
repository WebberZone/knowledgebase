import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	Disabled,
	ToggleControl,
	PanelBody,
	PanelRow,
	TextControl,
	Placeholder,
	SelectControl,
	RangeControl,
} from '@wordpress/components';

import { bookIcon } from '../components/icons';
import SectionSelector from '../components/section-selector';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const {
		termID,
		limit,
		showExcerpt,
		showHeading,
		linkHeading,
		headingLevel,
		productId,
		depth,
	} = attributes;

	const blockProps = useBlockProps();

	const sectionSelectorProps = {
		label: __('Filter by Section', 'knowledgebase'),
		value: termID,
		onChange: (value) => setAttributes({ termID: value }),
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
		onChange: (value) => setAttributes({ productId: value, termID: 0 }),
		includeEmptyLabel: __('Select a product', 'knowledgebase'),
		formatLabel: (term) => term.name,
		className: 'wzkb-products-selector',
		wrapperClass: 'wzkb-products-selector-wrapper',
		help: __('Search and select a product', 'knowledgebase'),
	};

	// Function to render Inspector Controls
	const renderInspectorControls = () => (
		<InspectorControls>
			<PanelBody
				title={__('Knowledge Base Articles Settings', 'knowledgebase')}
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
						label={__('Limit', 'knowledgebase')}
						value={limit}
						onChange={(value) => setAttributes({ limit: value })}
						min={-1}
						max={10}
						help={__('-1 for unlimited articles', 'knowledgebase')}
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={__('Show Excerpt', 'knowledgebase')}
						checked={showExcerpt}
						onChange={() =>
							setAttributes({ showExcerpt: !showExcerpt })
						}
						help={
							showExcerpt
								? __('Excerpt will be shown', 'knowledgebase')
								: __('Excerpt will be hidden', 'knowledgebase')
						}
					/>
				</PanelRow>
				<PanelRow>
					<RangeControl
						label={__('Max Depth', 'knowledgebase')}
						value={depth}
						onChange={(value) => setAttributes({ depth: value })}
						min={-1}
						max={10}
						help={__(
							'-1 for unlimited depth, 0 for current section only',
							'knowledgebase'
						)}
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={
							productId
								? __('Show product heading', 'knowledgebase')
								: __('Show section heading', 'knowledgebase')
						}
						checked={showHeading}
						onChange={() =>
							setAttributes({ showHeading: !showHeading })
						}
						help={
							showHeading
								? productId
									? __(
											'Product heading will be shown',
											'knowledgebase'
										)
									: __(
											'Section heading will be shown',
											'knowledgebase'
										)
								: productId
									? __(
											'Product heading will be hidden',
											'knowledgebase'
										)
									: __(
											'Section heading will be hidden',
											'knowledgebase'
										)
						}
					/>
				</PanelRow>
				{showHeading && (
					<PanelRow>
						<ToggleControl
							label={__(
								'Link heading to selection',
								'knowledgebase'
							)}
							checked={linkHeading}
							onChange={() =>
								setAttributes({ linkHeading: !linkHeading })
							}
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
								setAttributes({ headingLevel: value })
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
							help={__(
								'Select the heading level or paragraph for the section title.',
								'knowledgebase'
							)}
						/>
					</PanelRow>
				)}
			</PanelBody>
		</InspectorControls>
	);

	// If no term or product is selected, show the placeholder
	if (!termID && !productId) {
		return (
			<>
				{renderInspectorControls()}
				<div {...blockProps}>
					<Placeholder
						icon={bookIcon}
						label={__('Knowledge Base Articles', 'knowledgebase')}
						instructions={__(
							'Select a product or section to display its articles.',
							'knowledgebase'
						)}
					>
						<div className="wzkb-articles-placeholder-grid">
							<div className="wzkb-articles-selector-column">
								<span className="wzkb-articles-selector-heading">
									{__('Filter by product', 'knowledgebase')}
								</span>
								<SectionSelector
									{...productSelectorProps}
									label=""
								/>
							</div>
							<div className="wzkb-articles-selector-column">
								<span className="wzkb-articles-selector-heading">
									{__('Filter by section', 'knowledgebase')}
								</span>
								<SectionSelector
									{...sectionSelectorProps}
									{...filterSectionsByProduct}
									label=""
								/>
							</div>
						</div>
					</Placeholder>
				</div>
			</>
		);
	}

	return (
		<>
			{renderInspectorControls()}

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/articles"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
