/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	RangeControl,
	Placeholder,
	Button,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { Disabled } from '@wordpress/components';

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
	const productOptions = products
		? [
				{ value: 0, label: __('Select a product', 'knowledgebase') },
				...products.map((product) => ({
					value: product.id,
					label: product.name,
				})),
			]
		: [{ value: 0, label: __('Loading...', 'knowledgebase') }];

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
						<SelectControl
							label={__('Product', 'knowledgebase')}
							value={productId}
							options={productOptions}
							onChange={(value) =>
								setAttributes({ productId: parseInt(value) })
							}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<Placeholder
						icon="products"
						label={__('Knowledge Base Products', 'knowledgebase')}
						instructions={__(
							'Select a product to display its sections.',
							'knowledgebase'
						)}
					>
						<SelectControl
							value={productId}
							options={productOptions}
							onChange={(value) =>
								setAttributes({ productId: parseInt(value) })
							}
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
					<SelectControl
						label={__('Product', 'knowledgebase')}
						value={productId}
						options={productOptions}
						onChange={(value) =>
							setAttributes({ productId: parseInt(value) })
						}
					/>
					<RangeControl
						label={__('Max Depth', 'knowledgebase')}
						value={depth}
						onChange={(value) => setAttributes({ depth: value })}
						min={0}
						max={10}
						help={__('0 for unlimited depth', 'knowledgebase')}
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
