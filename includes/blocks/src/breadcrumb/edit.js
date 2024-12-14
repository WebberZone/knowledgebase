import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	Disabled,
	PanelBody,
	PanelRow,
	TextControl,
} from '@wordpress/components';

import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { separator } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Breadcrumb Settings', 'knowledgebase')}
					initialOpen={true}
				>
					<PanelRow>
						<TextControl
							label={__('Separator', 'knowledgebase')}
							value={separator}
							onChange={(value) =>
								setAttributes({ separator: value })
							}
							help={__(
								'Enter the separator character or Unicode (e.g. »)',
								'knowledgebase'
							)}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/breadcrumb"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
