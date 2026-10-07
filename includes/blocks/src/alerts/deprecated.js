import { useBlockProps, RichText } from '@wordpress/block-editor';

export default [
	{
		attributes: {
			content: { type: 'string', source: 'html', selector: 'div' },
			align: { type: 'string', default: 'none' },
		},
		supports: { html: false },
		migrate: ( attributes ) => ( {
			...attributes,
			align: attributes.align === 'none' ? undefined : attributes.align,
		} ),
		save( { attributes: { content, align } } ) {
			const blockProps = useBlockProps.save( {
				className: 'wzkb-alert',
			} );
			return (
				<RichText.Content
					{ ...blockProps }
					tagName="div"
					value={ content }
					style={ { textAlign: align } }
				/>
			);
		},
	},
];
