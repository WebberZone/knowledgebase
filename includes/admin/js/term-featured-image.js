/* global wp, WZKBTermImage */
( function ( $ ) {
	'use strict';

	$( function () {
		$( '.wzkb-term-image-wrap' ).each( function () {
			var $wrap    = $( this );
			var $button  = $wrap.find( '.wzkb-term-image-button' );
			var $remove  = $wrap.find( '.wzkb-term-image-remove' );
			var $preview = $wrap.find( '.wzkb-term-image-preview' );
			var $input   = $wrap.find( '.wzkb-term-image-id' );
			var frame;

			$button.on( 'click', function ( e ) {
				e.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title:    WZKBTermImage.selectTitle,
					button:   { text: WZKBTermImage.useImageText },
					multiple: false,
					library:  { type: 'image' },
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					var url = attachment.sizes && attachment.sizes.thumbnail
						? attachment.sizes.thumbnail.url
						: attachment.url;

					$input.val( attachment.id );
					$preview.html( '<img src="' + url + '" alt="" />' ).removeClass( 'hidden' );
					$remove.removeClass( 'hidden' );
				} );

				frame.open();
			} );

			$remove.on( 'click', function ( e ) {
				e.preventDefault();
				$input.val( '' );
				$preview.html( '' ).addClass( 'hidden' );
				$remove.addClass( 'hidden' );
			} );
		} );
	} );
} )( jQuery );
