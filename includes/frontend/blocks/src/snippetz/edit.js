/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Placeholder, PanelBody } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { Disabled } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import SnippetSelector from './components/SnippetSelector';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param {Object} props Block props.
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { snippetId } = attributes;
	const blockProps = useBlockProps();

	const { snippet, isLoading } = useSelect(
		(select) => {
			if (!snippetId) {
				return { snippet: null, isLoading: false };
			}

			const { getEntityRecord, isResolving } = select(coreDataStore);
			return {
				snippet: getEntityRecord(
					'postType',
					'ata_snippets',
					snippetId,
					{
						_fields: ['id', 'title', 'meta'],
					}
				),
				isLoading: isResolving('getEntityRecord', [
					'postType',
					'ata_snippets',
					snippetId,
					{ _fields: ['id', 'title', 'meta'] },
				]),
			};
		},
		[snippetId]
	);

	const renderSnippetContent = () => {
		if (!snippetId) {
			return (
				<Placeholder
					icon="shortcode"
					label={__('WebberZone Snippetz', 'add-to-all')}
					instructions={__(
						'Select a snippet from the block settings sidebar.',
						'add-to-all'
					)}
				>
					<SnippetSelector
						value={snippetId}
						onChange={(value) =>
							setAttributes({ snippetId: value })
						}
					/>
				</Placeholder>
			);
		}

		if (isLoading || !snippet) {
			return (
				<Placeholder
					icon="shortcode"
					label={__('Loading...', 'add-to-all')}
				/>
			);
		}

		const snippetType = snippet.meta._ata_snippet_type || 'html';
		const title = snippet.title.rendered || __('(No title)', 'add-to-all');

		if (snippetType === 'js' || snippetType === 'css') {
			return (
				<Placeholder
					icon="shortcode"
					label={__('WebberZone Snippetz', 'add-to-all')}
					instructions={sprintf(
						/* translators: 1: Snippet type, 2: Snippet title */
						__(
							'This is a placeholder for a %1$s snippet with the title: %2$s',
							'add-to-all'
						),
						snippetType.toUpperCase(),
						title
					)}
				/>
			);
		}

		return (
			<Disabled>
				<ServerSideRender
					block="webberzone/snippetz"
					attributes={attributes}
				/>
			</Disabled>
		);
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Snippet Settings', 'add-to-all')}
					initialOpen={true}
				>
					<SnippetSelector
						value={snippetId}
						onChange={(value) =>
							setAttributes({ snippetId: value })
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>{renderSnippetContent()}</div>
		</>
	);
}
