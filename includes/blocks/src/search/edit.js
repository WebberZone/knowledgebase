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
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { placeholder, buttonText } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Search Settings', 'knowledgebase')}
					initialOpen={true}
				>
					<PanelRow>
						<TextControl
							label={__('Placeholder Text', 'knowledgebase')}
							value={placeholder}
							onChange={(value) =>
								setAttributes({ placeholder: value })
							}
							help={__(
								'Text shown in the search input field',
								'knowledgebase'
							)}
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							label={__('Button Text', 'knowledgebase')}
							value={buttonText}
							onChange={(value) =>
								setAttributes({ buttonText: value })
							}
							help={__(
								'Text shown on the search button',
								'knowledgebase'
							)}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/search"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
