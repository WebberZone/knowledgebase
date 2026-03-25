/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	AlignmentControl,
	BlockControls,
} from '@wordpress/block-editor';

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
	const blockProps = useBlockProps({
		className: 'wzkb-alert',
	});
	const { content, align } = attributes;
	const onChangeContent = (newContent) => {
		setAttributes({ content: newContent });
	};
	const onChangeAlign = (newAlign) => {
		setAttributes({
			align: newAlign === undefined ? 'none' : newAlign,
		});
	};

	return (
		<>
			<BlockControls>
				<AlignmentControl value={align} onChange={onChangeAlign} />
			</BlockControls>
			<RichText
				{...blockProps}
				tagName="div"
				value={content}
				onChange={onChangeContent}
				placeholder={__('Enter the alert text...', 'knowledgebase')}
				style={{ textAlign: align }}
			/>
		</>
	);
}
