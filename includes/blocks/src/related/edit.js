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
	RangeControl,
	SelectControl,
	ToggleControl,
	Notice,
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
	const { title, limit, headingLevel, showExcerpt, showThumb, showDate } =
		attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Related Articles Settings', 'knowledgebase')}
					initialOpen={true}
				>
					<Notice status="info" isDismissible={false}>
						{__(
							'This block only displays on single article pages.',
							'knowledgebase'
						)}
					</Notice>
					<PanelRow>
						<TextControl
							label={__('Title', 'knowledgebase')}
							value={title}
							onChange={(value) =>
								setAttributes({ title: value })
							}
							help={__(
								'Section title for related articles',
								'knowledgebase'
							)}
						/>
					</PanelRow>
					<PanelRow>
						<SelectControl
							label={__('Heading Level', 'knowledgebase')}
							value={headingLevel}
							onChange={(value) =>
								setAttributes({ headingLevel: value })
							}
							options={[
								{ label: 'H2', value: 'h2' },
								{ label: 'H3', value: 'h3' },
								{ label: 'H4', value: 'h4' },
								{ label: 'H5', value: 'h5' },
								{ label: 'H6', value: 'h6' },
							]}
							help={__(
								'Choose the HTML heading level for the title',
								'knowledgebase'
							)}
						/>
					</PanelRow>
					<PanelRow>
						<RangeControl
							label={__('Number of Articles', 'knowledgebase')}
							value={limit}
							onChange={(value) =>
								setAttributes({ limit: value })
							}
							min={1}
							max={20}
							help={__(
								'Maximum number of related articles to display',
								'knowledgebase'
							)}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={__('Show Excerpt', 'knowledgebase')}
							checked={showExcerpt}
							onChange={() =>
								setAttributes({ showExcerpt: !showExcerpt })
							}
							help={
								showExcerpt
									? __(
											'Excerpt will be shown',
											'knowledgebase'
										)
									: __(
											'Excerpt will be hidden',
											'knowledgebase'
										)
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={__('Show Thumbnail', 'knowledgebase')}
							checked={showThumb}
							onChange={() =>
								setAttributes({ showThumb: !showThumb })
							}
							help={
								showThumb
									? __(
											'Thumbnail will be shown',
											'knowledgebase'
										)
									: __(
											'Thumbnail will be hidden',
											'knowledgebase'
										)
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={__('Show Date', 'knowledgebase')}
							checked={showDate}
							onChange={() =>
								setAttributes({ showDate: !showDate })
							}
							help={
								showDate
									? __('Date will be shown', 'knowledgebase')
									: __('Date will be hidden', 'knowledgebase')
							}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/related"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
