/* global WZKBPluginImporter, jQuery */
( function ( $ ) {
	'use strict';

	var totalPosts   = 0;
	var processedPosts = 0;
	var importedPosts  = 0;
	var skippedPosts   = 0;

	function init() {
		var $checkbox = $( '#wzkb-importer-backup-confirm' );
		var $button   = $( '#wzkb-importer-start' );

		if ( ! $button.length ) {
			return;
		}

		totalPosts = parseInt( $button.data( 'total' ), 10 ) || 0;

		$checkbox.on( 'change', function () {
			$button.prop( 'disabled', ! this.checked );
		} );

		$button.on( 'click', function ( e ) {
			e.preventDefault();
			$button.prop( 'disabled', true );
			runImport( $button.data( 'source' ) );
		} );
	}

	function runImport( source ) {
		// Reset accumulators so a re-run never inherits a previous import's totals.
		importedPosts  = 0;
		skippedPosts   = 0;
		processedPosts = 0;

		// Lock the checkbox so unchecking it cannot re-enable the button mid-import.
		$( '#wzkb-importer-backup-confirm' ).prop( 'disabled', true );

		var updateSlug = $( '#wzkb-importer-update-slug' ).is( ':checked' ) ? 1 : 0;

		$( '#wzkb-importer-progress' ).show();
		setStatus( WZKBPluginImporter.strings.importing_terms );
		appendLog( WZKBPluginImporter.strings.importing_terms );

		runBatch( source, 'terms', 0, updateSlug );
	}

	function runBatch( source, phase, offset, updateSlug ) {
		$.ajax( {
			url:    WZKBPluginImporter.ajax_url,
			method: 'POST',
			data: {
				action:      'wzkb_plugin_import_batch',
				nonce:       WZKBPluginImporter.nonce,
				source:      source,
				phase:       phase,
				offset:      offset,
				update_slug: updateSlug,
			},
			success: function ( response ) {
				if ( ! response.success ) {
					showError( response.data && response.data.message
						? response.data.message
						: WZKBPluginImporter.strings.error );
					return;
				}

				var data = response.data;

				appendLog( data.log );

				if ( 'terms' === data.phase ) {
					// Terms done — start posts phase.
					setStatus( WZKBPluginImporter.strings.importing_posts );
					setProgress( 0, totalPosts );
					runBatch( source, 'posts', 0, updateSlug );
					return;
				}

				// Posts phase.
				importedPosts += data.imported;
				skippedPosts  += data.skipped;
				processedPosts = data.processed;

				setProgress( processedPosts, data.total );
				setStatus(
					WZKBPluginImporter.strings.importing_posts + ' ' +
					processedPosts + ' / ' + data.total
				);

				if ( data.done ) {
					onDone();
				} else {
					runBatch( source, 'posts', processedPosts, updateSlug );
				}
			},
			error: function () {
				showError( WZKBPluginImporter.strings.error );
			},
		} );
	}

	function setProgress( processed, total ) {
		var pct = total > 0 ? Math.round( ( processed / total ) * 100 ) : 100;
		$( '#wzkb-importer-bar' ).css( 'width', pct + '%' );
	}

	function setStatus( text ) {
		$( '#wzkb-importer-status' ).text( text );
	}

	function appendLog( line ) {
		if ( ! line ) {
			return;
		}
		var $log = $( '#wzkb-importer-log' );
		$log.append( '<div>' + $( '<span>' ).text( line ).html() + '</div>' );
		$log.scrollTop( $log[0].scrollHeight );
	}

	function onDone() {
		setProgress( totalPosts, totalPosts );
		setStatus( WZKBPluginImporter.strings.done );

		var summary = WZKBPluginImporter.strings.done + ' ' +
			WZKBPluginImporter.strings.summary
				.replace( '%1$d', importedPosts )
				.replace( '%2$d', skippedPosts );

		$( '#wzkb-importer-summary' ).text( summary );
		$( '#wzkb-importer-done' ).show();
	}

	function showError( message ) {
		appendLog( '⚠ ' + message );
		setStatus( message );
		$( '#wzkb-importer-start' ).prop( 'disabled', false );
	}

	$( document ).ready( init );
} )( jQuery );
