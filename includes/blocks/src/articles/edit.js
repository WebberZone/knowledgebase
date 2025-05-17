import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import {
	Disabled,
	ComboboxControl,
	ToggleControl,
	PanelBody,
	PanelRow,
	Spinner,
	TextControl,
	Notice,
	Placeholder,
	SelectControl,
} from '@wordpress/components';

import { bookIcon } from '../components/icons';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { termID, limit, showExcerpt, showHeading, headingLevel } =
		attributes;

	const blockProps = useBlockProps();

	const { terms, hasResolved, error } = useSelect((select) => {
		const query = { per_page: -1 };
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

	// Function to render Inspector Controls
	const renderInspectorControls = () => (
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
				title={__('Knowledge Base Articles Settings', 'knowledgebase')}
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
						label={__('Limit', 'knowledgebase')}
						value={limit}
						type="number"
						min="1"
						onChange={(value) => setAttributes({ limit: value })}
						help={__(
							'Enter the maximum number of articles to display',
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
								? __('Excerpt will be shown', 'knowledgebase')
								: __('Excerpt will be hidden', 'knowledgebase')
						}
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={__('Show Heading', 'knowledgebase')}
						checked={showHeading}
						onChange={() =>
							setAttributes({ showHeading: !showHeading })
						}
						help={
							showHeading
								? __(
										'Section heading will be shown',
										'knowledgebase'
									)
								: __(
										'Section heading will be hidden',
										'knowledgebase'
									)
						}
					/>
				</PanelRow>
				{showHeading && (
					<PanelRow>
						<SelectControl
							label={__('Heading Level', 'knowledgebase')}
							value={headingLevel}
							onChange={(value) =>
								setAttributes({ headingLevel: value })
							}
							options={[
								{ label: 'H1', value: 'h1' },
								{ label: 'H2', value: 'h2' },
								{ label: 'H3', value: 'h3' },
								{ label: 'H4', value: 'h4' },
								{ label: 'H5', value: 'h5' },
								{ label: 'H6', value: 'h6' },
								{ label: 'Paragraph', value: 'p' },
							]}
							help={__(
								'Select the heading level or paragraph for the section title.',
								'knowledgebase'
							)}
						/>
					</PanelRow>
				)}
			</PanelBody>
		</InspectorControls>
	);

	// If no term is selected, show the placeholder
	if (!termID) {
		return (
			<>
				{renderInspectorControls()}

				<div {...blockProps}>
					<Placeholder
						icon={bookIcon}
						label={__('Knowledge Base Articles', 'knowledgebase')}
						instructions={__(
							'Select a section to display its articles.',
							'knowledgebase'
						)}
					>
						{!hasResolved ? (
							<Spinner />
						) : (
							<ComboboxControl
								value={termID}
								onChange={(value) =>
									setAttributes({ termID: value })
								}
								options={termOptions}
							/>
						)}
					</Placeholder>
				</div>
			</>
		);
	}

	return (
		<>
			{renderInspectorControls()}

			<div {...blockProps}>
				<Disabled>
					<ServerSideRender
						block="knowledgebase/articles"
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
}
