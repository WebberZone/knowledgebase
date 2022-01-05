/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

import ServerSideRender from '@wordpress/server-side-render';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	TextControl,
	TextareaControl,
	PanelBody,
	PanelRow,
} from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
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
	const { category, other_attributes } = attributes;
	const blockProps = useBlockProps();
	const onChangeCategory = (newCategory) => {
		setAttributes({
			category: newCategory === undefined ? '' : newCategory,
		});
	};
	const onChangeOtherAttributes = (newOtherAttributes) => {
		setAttributes({
			other_attributes:
				newOtherAttributes === undefined ? '' : newOtherAttributes,
		});
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Knowledge Base Settings', 'knowledgebase')}
					initialOpen={true}
				>
					<PanelRow>
						<fieldset>
							<TextControl
								label={__('Category ID', 'knowledgebase')}
								value={category}
								onChange={onChangeCategory}
								help={__(
									'Enter a single category/section ID to display its knowledge base or leave back to display the full knowledge base. You can find this under Knowledge Base > Sections.',
									'knowledgebase'
								)}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<TextareaControl
								label={__('Other attributes', 'knowledgebase')}
								value={other_attributes}
								onChange={onChangeOtherAttributes}
								help={__(
									'Enter other attributes in a URL-style string-query.',
									'knowledgebase'
								)}
							/>
						</fieldset>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="knowledgebase/knowledgebase"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
