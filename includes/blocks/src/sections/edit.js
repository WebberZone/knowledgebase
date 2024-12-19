import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import {
	Disabled,
	ComboboxControl,
	PanelBody,
	PanelRow,
	Spinner,
	TextControl,
	Notice,
} from '@wordpress/components';

import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { termID, depth, beforeLiItem, afterLiItem } = attributes;

	const blockProps = useBlockProps();

	const { terms, hasResolved, error } = useSelect((select) => {
		const query = {
			per_page: -1,
			hide_empty: 1,
		};
		const selectorArgs = ['taxonomy', 'wzkb_category', query];

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
	}, []);

	const termOptions =
		terms?.map((term) => ({
			label: `${term.name} (#${term.id})`,
			value: term.id.toString(),
		})) || [];

	return (
		<>
			<InspectorControls>
				{error && (
					<Notice status="error" isDismissible={false}>
						{__(
							'Error loading categories. Please try again.',
							'knowledgebase'
						)}
					</Notice>
				)}

				<PanelBody
					title={__(
						'Knowledge Base Sections Settings',
						'knowledgebase'
					)}
					initialOpen={true}
				>
					<PanelRow>
						{!hasResolved ? (
							<Spinner />
						) : (
							<ComboboxControl
								label={__(
									'Select Knowledge Base Section',
									'knowledgebase'
								)}
								value={termID}
								onChange={(value) =>
									setAttributes({ termID: value })
								}
								options={termOptions}
								help={__(
									'Search and select a knowledge base section',
									'knowledgebase'
								)}
							/>
						)}
					</PanelRow>
					<PanelRow>
						<TextControl
							label={__('Depth', 'knowledgebase')}
							value={depth}
							type="number"
							min="0"
							onChange={(value) =>
								setAttributes({ depth: value })
							}
							help={__(
								'Enter the depth of sections to display (0 for all)',
								'knowledgebase'
							)}
						/>
					</PanelRow>
					<PanelRow>
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
					</PanelRow>
					<PanelRow>
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
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/sections"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
