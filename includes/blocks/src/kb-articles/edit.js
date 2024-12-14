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
} from '@wordpress/components';

import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { termID, limit, showExcerpt } = attributes;

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

	const handleLimitChange = (newLimit) => {
		const parsedLimit = parseInt(newLimit, 10);
		setAttributes({
			limit: isNaN(parsedLimit) || parsedLimit <= 0 ? 5 : parsedLimit,
		});
	};

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
						'Knowledge Base Articles Settings',
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
							label={__('Number of posts', 'knowledgebase')}
							value={limit}
							type="number"
							min="1"
							onChange={handleLimitChange}
							help={__(
								'Enter the number of posts to display (default: 5)',
								'knowledgebase'
							)}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={__('Show excerpt', 'knowledgebase')}
							help={
								showExcerpt
									? __(
											'Excerpt is displayed',
											'knowledgebase'
										)
									: __('No excerpt shown', 'knowledgebase')
							}
							checked={showExcerpt}
							onChange={() =>
								setAttributes({
									showExcerpt: !showExcerpt,
								})
							}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

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
