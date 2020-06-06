( function( blocks, i18n, element, components, editor, blockEditor ) {
	var el = element.createElement;
	const {registerBlockType} = blocks;
	const {__} = i18n; //translation functions
	var ServerSideRender = wp.serverSideRender;

	const { RichText, InspectorControls } = blockEditor;
	const {
		TextControl,
		CheckboxControl,
		RadioControl,
		SelectControl,
		TextareaControl,
		ToggleControl,
		RangeControl,
		Panel,
		PanelBody,
		PanelRow,
	} = components;

	registerBlockType( 'knowledgebase/knowledgebase', {
		title: __( 'Knowledge Base', 'knowledgebase' ),
		description: __( 'Display the Knowledge Base', 'knowledgebase' ),
		category: 'widgets',
		icon: 'book-alt',
		keywords: [ __( 'knowledgebase', 'knowledgebase' ), __( 'kb', 'knowledgebase' ), __( 'faq', 'knowledgebase' ) ],

		attributes: {
			category: {
				type: 'string',
				default: '',
			},
			other_attributes: {
				type: 'string',
				default: '',
			},
		},

		supports: {
			html: false,
		},

		example: { },

		edit: function( props ) {
			const attributes =  props.attributes;
			const setAttributes =  props.setAttributes;

			if(props.isSelected){
	      	//	console.debug(props.attributes);
    		};

			function changeCategory(category){
				setAttributes({category});
			}

			function changeOtherAttributes(other_attributes){
				setAttributes({other_attributes});
			}

			return [
				/**
				 * Server side render
				 */
				el("div", { className: props.className },
					el( ServerSideRender, {
					  block: 'knowledgebase/knowledgebase',
					  attributes: attributes
					} )
				),

				/**
				 * Inspector
				 */
				el( InspectorControls, {},
					el( PanelBody, { title: 'Knowledge Base Settings', initialOpen: true },

						el( TextControl, {
							label: __( 'Category ID', 'knowledgebase' ),
							help: __( 'Enter a single category/section ID to display its knowledge base or leave back to display the full knowledge base. You can find this under Knowledge Base > Sections', 'knowledgebase' ),
							value: attributes.category,
							onChange: changeCategory
						} ),
						el( TextareaControl, {
							label: __( 'Other attributes', 'knowledgebase' ),
							help: __( 'Enter other attributes in a URL-style string-query. e.g. post_types=post,page&link_nofollow=1&exclude_post_ids=5,6', 'knowledgebase' ),
							value: attributes.other_attributes,
							onChange: changeOtherAttributes
						} )
					),
				),
			]
		},

		save(){
			return null;//save has to exist. This all we need
		}
	} );
} )(
	window.wp.blocks,
	window.wp.i18n,
	window.wp.element,
	window.wp.components,
	window.wp.editor,
	window.wp.blockEditor,
	window.wp.serverSideRender
);
