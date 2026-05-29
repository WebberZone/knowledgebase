/**
 * GitHub Push/Pull — meta box button handlers.
 *
 * Localised as WZKBPush = {
 *   ajaxurl, pushNonce, pullNonce,
 *   i18n: { pushButton, pushing, pushOk, unchanged, view, pushError,
 *           pullButton, pulling, pullOk, pullOkReload, pullError }
 * }.
 *
 * @package WebberZone\Knowledge_Base\Pro\GitHub
 */

/* global WZKBPush */
( function ( $ ) {
	'use strict';

	function esc( text ) {
		return $( '<span>' ).text( text ).html();
	}

	// Push to GitHub.
	$( document ).on( 'click', '#wzkb-github-push-btn', function () {
		var $btn    = $( this );
		var $status = $( '#wzkb-github-push-status' );
		var postId  = $btn.data( 'post-id' );

		$btn.prop( 'disabled', true ).text( WZKBPush.i18n.pushing );
		$status.html( '' );

		$.post(
			WZKBPush.ajaxurl,
			{
				action:  'wzkb_github_push_post',
				nonce:   WZKBPush.pushNonce,
				post_id: postId,
			},
			function ( response ) {
				$btn.prop( 'disabled', false ).text( WZKBPush.i18n.pushButton );

				if ( response.success ) {
					var msg;
					if ( response.data && response.data.unchanged ) {
						msg = '<span style="color:#757575;">&#8212; ' + WZKBPush.i18n.unchanged + '</span>';
					} else {
						msg = '<span style="color:#00a32a;">&#10003; ' + WZKBPush.i18n.pushOk + '</span>';
						if ( response.data && response.data.commit_url ) {
							msg += ' &mdash; <a href="' + response.data.commit_url + '" target="_blank" rel="noopener">' + WZKBPush.i18n.view + '</a>';
						}
					}
					$status.html( msg );
				} else {
					var errMsg = ( response.data && response.data.message ) ? response.data.message : '';
					$status.html( '<span style="color:#d63638;">&#10007; ' + WZKBPush.i18n.pushError + ' ' + esc( errMsg ) + '</span>' );
				}
			}
		).fail( function () {
			$btn.prop( 'disabled', false ).text( WZKBPush.i18n.pushButton );
			$status.html( '<span style="color:#d63638;">&#10007; ' + WZKBPush.i18n.pushError + '</span>' );
		} );
	} );

	// Pull from GitHub. Overwrites the post with the remote file, then reloads
	// so both the Classic and Block editors pick up the fresh content.
	$( document ).on( 'click', '#wzkb-github-pull-btn', function () {
		var $btn    = $( this );
		var $status = $( '#wzkb-github-push-status' );
		var postId  = $btn.data( 'post-id' );

		$btn.prop( 'disabled', true ).text( WZKBPush.i18n.pulling );
		$status.html( '' );

		$.post(
			WZKBPush.ajaxurl,
			{
				action:  'wzkb_github_pull_post',
				nonce:   WZKBPush.pullNonce,
				post_id: postId,
			},
			function ( response ) {
				if ( response.success ) {
					$status.html( '<span style="color:#00a32a;">&#10003; ' + WZKBPush.i18n.pullOkReload + '</span>' );
					window.location.reload();
				} else {
					$btn.prop( 'disabled', false ).text( WZKBPush.i18n.pullButton );
					var errMsg = ( response.data && response.data.message ) ? response.data.message : '';
					$status.html( '<span style="color:#d63638;">&#10007; ' + WZKBPush.i18n.pullError + ' ' + esc( errMsg ) + '</span>' );
				}
			}
		).fail( function () {
			$btn.prop( 'disabled', false ).text( WZKBPush.i18n.pullButton );
			$status.html( '<span style="color:#d63638;">&#10007; ' + WZKBPush.i18n.pullError + '</span>' );
		} );
	} );
} )( jQuery );
