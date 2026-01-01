import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	Disabled,
	PanelBody,
	PanelRow,
	TextControl,
	RangeControl,
} from '@wordpress/components';
import { bookIcon } from '../components/icons';
import SectionSelector from '../components/section-selector';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { title, termID, depth, beforeLiItem, afterLiItem } = attributes;
	const blockProps = useBlockProps();

	// Function to render Inspector Controls
	const renderInspectorControls = () => (
		<InspectorControls>
			<PanelBody
				title={__('Knowledge Base Sections Settings', 'knowledgebase')}
				initialOpen={true}
			>
				<PanelRow>
					<TextControl
						label={__('Title', 'knowledgebase')}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
					/>
				</PanelRow>
				<PanelRow>
					<SectionSelector
						label={__('Select Knowledge Base Section', 'knowledgebase')}
						value={termID}
						onChange={(value) => setAttributes({ termID: value })}
						help={__(
							'Search and select a knowledge base section',
							'knowledgebase'
						)}
					/>
				</PanelRow>
				<PanelRow>
					<RangeControl
						label={__('Max Depth', 'knowledgebase')}
						value={depth}
						onChange={(value) => setAttributes({ depth: value })}
						min={-1}
						max={10}
						help={__('-1 for unlimited depth, 0 for current section only', 'knowledgebase')}
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
	);

	return (
		<>
			{renderInspectorControls()}

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
