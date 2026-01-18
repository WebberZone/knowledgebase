/* global wp */

/**
 * Admin Snippets JavaScript.
 *
 * @package WebberZone\Snippetz
 */

( function( wp ) {
	const { createElement, useState } = wp.element;
	const { ToggleControl } = wp.components;
	const { createRoot } = wp.element;
	const { __ } = wp.i18n;
	const { VisuallyHidden } = wp.components;

	/**
	 * SnippetToggle Component
	 *
	 * @param {Object} props Component props.
	 * @return {WPElement} Element to render.
	 */
	function SnippetToggle( props ) {
		const [isDisabled, setIsDisabled] = useState( props.initialDisabled || false );
		const [isUpdating, setIsUpdating] = useState( false );

		const toggleSnippet = () => {
			setIsUpdating( true );

			const formData = new FormData();
			formData.append( 'action', 'ata_toggle_snippet' );
			formData.append( 'post_id', props.postId );
			formData.append( 'nonce', props.nonce );

			fetch( props.ajaxurl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			} )
			.then( response => {
				console.log('Response:', response);
				return response.json();
			} )
			.then( data => {
				console.log('Data:', data);
				if ( data.success ) {
					console.log('Setting disabled to:', data.data.disabled);
					setIsDisabled( data.data.disabled );
				} else {
					console.error( 'Error toggling snippet:', data.data.message );
				}
			} )
			.finally( () => {
				setIsUpdating( false );
			} );
		};

		return createElement(
			'div',
			{},
			[
				createElement( VisuallyHidden, {},
					isDisabled ? __( 'Snippet is disabled', 'add-to-all' ) : __( 'Snippet is active', 'add-to-all' )
				),
				createElement( ToggleControl, {
					checked: !isDisabled,
					onChange: toggleSnippet,
					disabled: isUpdating,
					className: 'ata-snippet-toggle'
				} )
			]
		);
	}

	// Initialize all toggle buttons
	document.addEventListener( 'DOMContentLoaded', function() {
		const toggleWrappers = document.querySelectorAll( '.ata-snippet-toggle-wrapper' );
		console.log('Found toggle wrappers:', toggleWrappers.length);

		toggleWrappers.forEach( function( wrapper ) {
			const postId = wrapper.dataset.postId;
			const nonce = wrapper.dataset.nonce;
			const data = window['ataSnippetData_' + postId] || { disabled: false };

			console.log('Initializing toggle for post:', postId, 'with data:', data);

			const root = createRoot( wrapper );
			root.render(
				createElement( SnippetToggle, {
					postId: postId,
					nonce: nonce,
					initialDisabled: data.disabled,
					ajaxurl: data.ajaxurl
				} )
			);
		} );
	} );
} )( wp );
