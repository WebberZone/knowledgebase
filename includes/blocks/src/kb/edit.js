/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	Disabled,
	TextControl,
	TextareaControl,
	ToggleControl,
	PanelBody,
	PanelRow,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
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
		limit,
		showArticleCount,
		showExcerpt,
		hasClickableSection,
		showEmptySections,
		columns,
		other_attributes,
	} = attributes;

	return (
		<PanelBody
			title={__('Knowledge Base Settings', 'knowledgebase')}
			initialOpen={true}
		>
			<PanelRow>
				<TextControl
					label={__('Category ID', 'knowledgebase')}
					value={category}
					onChange={(value) =>
						onUpdateAttribute('category', value || '')
					}
					help={__(
						'Enter a single category/section ID to display its knowledge base or leave blank to display the full knowledge base. You can find this under Knowledge Base > Sections.',
						'knowledgebase'
					)}
				/>
			</PanelRow>

			<PanelRow>
				<TextControl
					label={__('Max articles per section', 'knowledgebase')}
					value={limit}
					onChange={(value) =>
						onUpdateAttribute('limit', processNumber(value))
					}
					help={__(
						'After this limit is reached, the footer is displayed with the more link to view the category.',
						'knowledgebase'
					)}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show article count', 'knowledgebase')}
					help={
						showArticleCount
							? __('Article count displayed', 'knowledgebase')
							: __('No article count displayed', 'knowledgebase')
					}
					checked={showArticleCount}
					onChange={() => onToggleAttribute('showArticleCount')}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show excerpt', 'knowledgebase')}
					help={
						showExcerpt
							? __('Excerpt displayed', 'knowledgebase')
							: __('No excerpt', 'knowledgebase')
					}
					checked={showExcerpt}
					onChange={() => onToggleAttribute('showExcerpt')}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show clickable section', 'knowledgebase')}
					help={
						hasClickableSection
							? __('Section headers are linked', 'knowledgebase')
							: __('Section headers not linked', 'knowledgebase')
					}
					checked={hasClickableSection}
					onChange={() => onToggleAttribute('hasClickableSection')}
				/>
			</PanelRow>

			<PanelRow>
				<ToggleControl
					label={__('Show empty sections', 'knowledgebase')}
					help={
						showEmptySections
							? __('Empty sections displayed', 'knowledgebase')
							: __('Empty sections hidden', 'knowledgebase')
					}
					checked={showEmptySections}
					onChange={() => onToggleAttribute('showEmptySections')}
				/>
			</PanelRow>

			<PanelRow>
				<TextControl
					label={__('Number of columns', 'knowledgebase')}
					value={columns}
					onChange={(value) =>
						onUpdateAttribute('columns', processNumber(value))
					}
					help={__(
						'Only works when inbuilt styles are enabled in the Settings page',
						'knowledgebase'
					)}
				/>
			</PanelRow>

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
	const blockProps = useBlockProps();
	const { processNumber, updateAttribute, toggleAttribute } =
		useKnowledgeBaseSettings(attributes, setAttributes);

	return (
		<>
			<InspectorControls>
				<KnowledgeBaseSettings
					attributes={attributes}
					onUpdateAttribute={updateAttribute}
					onToggleAttribute={toggleAttribute}
					processNumber={processNumber}
				/>
			</InspectorControls>

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
