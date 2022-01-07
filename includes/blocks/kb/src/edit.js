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
	ToggleControl,
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
	function processNumber(input) {
		const output =
			undefined === input || 0 === input || '' === input || isNaN(input)
				? ''
				: parseInt(input);
		return output;
	}

	const {
		category,
		limit,
		showArticleCount,
		showExcerpt,
		hasClickableSection,
		showEmptySections,
		columns,
		other_attributes,
	} = attributes;
	console.log(attributes);
	const blockProps = useBlockProps();
	const onChangeCategory = (newCategory) => {
		setAttributes({
			category: undefined === newCategory ? '' : newCategory,
		});
	};
	const onChangeLimit = (newLimit) => {
		setAttributes({ limit: processNumber(newLimit) });
	};
	const toggleShowArticleCount = () => {
		setAttributes({ showArticleCount: !showArticleCount });
	};
	const toggleShowExcerpt = () => {
		setAttributes({ showExcerpt: !showExcerpt });
	};
	const toggleClickableSection = () => {
		setAttributes({ hasClickableSection: !hasClickableSection });
	};
	const toggleShowEmptySections = () => {
		setAttributes({ showEmptySections: !showEmptySections });
	};
	const onChangeColumns = (newColumns) => {
		setAttributes({ columns: processNumber(newColumns) });
	};
	const onChangeOtherAttributes = (newOtherAttributes) => {
		setAttributes({
			other_attributes:
				undefined === newOtherAttributes ? '' : newOtherAttributes,
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
							<TextControl
								label={__('Max articles per section', 'knowledgebase')}
								value={limit}
								onChange={onChangeLimit}
								help={__(
									'After this limit is reached, the footer is displayed with the more link to view the category.',
									'knowledgebase'
								)}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show article count', 'knowledgebase')}
								help={
									showArticleCount
										? __('Article count displayed', 'knowledgebase')
										: __('No article count displayed', 'knowledgebase')
								}
								checked={showArticleCount}
								onChange={toggleShowArticleCount}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show excerpt', 'knowledgebase')}
								help={
									showExcerpt
										? __('Excerpt displayed', 'knowledgebase')
										: __('No excerpt', 'knowledgebase')
								}
								checked={showExcerpt}
								onChange={toggleShowExcerpt}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show clickable section', 'knowledgebase')}
								help={
									hasClickableSection
										? __('Section headers are linked', 'knowledgebase')
										: __('Section headers not linked', 'knowledgebase')
								}
								checked={hasClickableSection}
								onChange={toggleClickableSection}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<ToggleControl
								label={__('Show empty sections', 'knowledgebase')}
								help={
									showEmptySections
										? __('Empty sections displayed', 'knowledgebase')
										: __('Empty sections hidden', 'knowledgebase')
								}
								checked={showEmptySections}
								onChange={toggleShowEmptySections}
							/>
						</fieldset>
					</PanelRow>
					<PanelRow>
						<fieldset>
							<TextControl
								label={__('Number of columns', 'knowledgebase')}
								value={columns}
								onChange={onChangeColumns}
								help={__(
									'Only works when inbuilt styles are enabled in the Settings page',
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
