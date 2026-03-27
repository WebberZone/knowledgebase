/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	PanelBody,
	TextControl,
	RangeControl,
	Placeholder,
	Disabled,
} from '@wordpress/components';
import { bookIcon } from '../components/icons';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import SectionSelector from '../components/section-selector';

/**
 * Edit function for the Knowledge Base Products block.
 *
 * @param {Object} props               Block props.
 * @param {Object} props.attributes    Block attributes.
 * @param {Object} props.setAttributes Block setAttributes function.
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const { title, productId, depth, beforeLiItem, afterLiItem } = attributes;

	// Get products using the REST API
	const { products } = useSelect((select) => {
		const { getEntityRecords } = select('core');
		return {
			products: getEntityRecords('taxonomy', 'wzkb_product', {
				per_page: -1,
				orderby: 'name',
				order: 'asc',
				_fields: ['id', 'name'],
			}),
		};
	}, []);

	// Create options array for SelectControl
	const formatProductLabel = (term) => term.name;

	const sectionSelectorProps = {
		taxonomy: 'wzkb_product',
		label: __('Product', 'knowledgebase'),
		help: __('Search and select a product', 'knowledgebase'),
		includeEmptyLabel: __('Select a product', 'knowledgebase'),
		formatLabel: formatProductLabel,
	};

	// If no product is selected, show the placeholder
	if (!productId) {
		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Settings', 'knowledgebase')}>
						<TextControl
							label={__('Title', 'knowledgebase')}
							value={title}
							onChange={(value) =>
								setAttributes({ title: value })
							}
						/>
						<SectionSelector
							{...sectionSelectorProps}
							value={productId}
							onChange={(value) =>
								setAttributes({ productId: value })
							}
							className="wzkb-products-selector"
							wrapperClass="wzkb-products-selector-wrapper"
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<Placeholder
						icon={bookIcon}
						label={__('Knowledge Base Products', 'knowledgebase')}
						instructions={__(
							'Select a product to display its sections.',
							'knowledgebase'
						)}
					>
						<SectionSelector
							taxonomy="wzkb_product"
							label={__('Product', 'knowledgebase')}
							value={productId}
							onChange={(value) =>
								setAttributes({ productId: value })
							}
							help={__(
								'Search and select a product',
								'knowledgebase'
							)}
							includeEmptyLabel={__(
								'Select a product',
								'knowledgebase'
							)}
							formatLabel={(term) => term.name}
							className="wzkb-products-selector"
							wrapperClass="wzkb-products-selector-wrapper"
						/>
					</Placeholder>
				</div>
			</>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'knowledgebase')}>
					<TextControl
						label={__('Title', 'knowledgebase')}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
					<SectionSelector
						{...sectionSelectorProps}
						value={productId}
						onChange={(value) =>
							setAttributes({ productId: value })
						}
						className="wzkb-products-selector"
						wrapperClass="wzkb-products-selector-wrapper"
					/>
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
					<TextControl
						label={__('Before list item', 'knowledgebase')}
						value={beforeLiItem}
						onChange={(value) =>
							setAttributes({ beforeLiItem: value })
						}
						help={__(
							'HTML/text to add before each list item',
							'knowledgebase'
						)}
					/>
					<TextControl
						label={__('After list item', 'knowledgebase')}
						value={afterLiItem}
						onChange={(value) =>
							setAttributes({ afterLiItem: value })
						}
						help={__(
							'HTML/text to add after each list item',
							'knowledgebase'
						)}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/products"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
