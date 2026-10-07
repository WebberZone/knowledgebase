import { createBlock, hasBlockSupport } from '@wordpress/blocks';

export default {
	from: [
		{
			type: 'block',
			blocks: [ 'core/paragraph' ],
			transform: ( { content, align, style } ) =>
				createBlock( 'knowledgebase/alerts', {
					content: String( content ?? '' ),
					align: hasBlockSupport(
						'core/paragraph',
						'typography.textAlign'
					)
						? style?.typography?.textAlign
						: align,
				} ),
		},
	],
	to: [
		{
			type: 'block',
			blocks: [ 'core/paragraph' ],
			transform: ( { content, align } ) => {
				const textAlign = align === 'none' ? undefined : align;
				return createBlock( 'core/paragraph', {
					content,
					...( hasBlockSupport(
						'core/paragraph',
						'typography.textAlign'
					)
						? {
								style: textAlign
									? { typography: { textAlign } }
									: undefined,
						  }
						: { align: textAlign } ),
				} );
			},
		},
	],
};
