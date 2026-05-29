/**
 * GitHub Export Wizard — two-phase bulk push.
 *
 * Phase 1 (List): submit the form → AJAX fetches linked articles → show table.
 * Phase 2 (Commit): click "Push to GitHub" → single AJAX call creates one
 *                   commit per repo containing only the changed files.
 *
 * Localised as WZKBExportWizard = {
 *   ajax_url, nonce, edit_url_base,
 *   strings: { fetching, committing, done, no_articles, nothing, commit_btn, view_commit }
 * }.
 *
 * @package WebberZone\Knowledge_Base\Pro\GitHub
 */

/* global WZKBExportWizard */
jQuery( document ).ready( function ( $ ) {
	var data          = WZKBExportWizard || {};
	var $form         = $( '#wzkb-github-export-form' );
	var $commitWrap   = $( '#wzkb-export-commit-wrap' );
	var $commitBtn    = $( '#wzkb-export-commit-btn' );
	var $progress     = $( '#wzkb-export-progress' );
	var $progressBar  = $( '#wzkb-export-progress-bar' );
	var $progressText = $( '#wzkb-export-progress-text' );
	var $results      = $( '#wzkb-export-results' );
	var $tbody        = $( '#wzkb-export-results-tbody' );
	var $summary      = $( '#wzkb-export-summary' );
	var $listBtn      = $( '#wzkb_export_submit' );
	var currentMapping = '';

	// ── Phase 1: list articles ────────────────────────────────────────────────

	$form.on( 'submit', function ( e ) {
		e.preventDefault();

		currentMapping = $( '#wzkb_github_export_mapping' ).val();
		if ( ! currentMapping ) {
			return;
		}

		// Reset.
		$commitWrap.hide();
		$progress.show();
		$progressBar.css( 'width', '10%' );
		$progressText.text( data.strings.fetching || 'Fetching article list…' );
		$results.show();
		$tbody.empty();
		$summary.hide().html( '' );
		$listBtn.prop( 'disabled', true );

		$.ajax( {
			url:  data.ajax_url,
			type: 'POST',
			data: {
				action:  'wzkb_github_export_list_articles',
				nonce:   data.nonce,
				mapping: currentMapping,
			},
			success: function ( response ) {
				$progressBar.css( 'width', '100%' );
				$listBtn.prop( 'disabled', false );

				if ( ! response || ! response.success ) {
					$progressText.text( ( response && response.data && response.data.message ) || 'Failed to get article list.' );
					return;
				}

				var tasks = response.data.tasks || [];
				if ( ! tasks.length ) {
					var msg = response.data.all_up_to_date
						? ( data.strings.nothing || 'All articles are already up to date — nothing to push.' )
						: ( data.strings.no_articles || 'No linked articles found for this mapping.' );
					$progressText.text( msg );
					return;
				}

				tasks.forEach( function ( task ) {
					$tbody.append( buildPendingRow( task ) );
				} );

				$progressText.text( tasks.length + ' article' + ( tasks.length !== 1 ? 's' : '' ) + ' ready to push.' );
				$commitWrap.show();
				$commitBtn.text( data.strings.commit_btn || 'Push to GitHub' ).prop( 'disabled', false );
			},
			error: function () {
				$progressText.text( 'Request failed. Check your connection and try again.' );
				$listBtn.prop( 'disabled', false );
			},
		} );
	} );

	// ── Phase 2: single-commit push ───────────────────────────────────────────

	$commitBtn.on( 'click', function () {
		if ( ! currentMapping ) {
			return;
		}

		$commitBtn.prop( 'disabled', true ).text( data.strings.committing || 'Creating commit…' );
		$progressBar.css( 'width', '20%' );
		$progressText.text( data.strings.committing || 'Creating commit…' );
		$tbody.empty();
		$summary.hide().html( '' );

		$.ajax( {
			url:     data.ajax_url,
			type:    'POST',
			timeout: 300000, // 5 min — large repos may take a while.
			data:    {
				action:  'wzkb_github_export_commit_all',
				nonce:   data.nonce,
				mapping: currentMapping,
			},
			success: function ( response ) {
				$progressBar.css( 'width', '100%' );
				$commitBtn.prop( 'disabled', false ).text( data.strings.commit_btn || 'Push to GitHub' );

				if ( ! response || ! response.success ) {
					$progressText.text( ( response && response.data && response.data.message ) || 'Commit failed.' );
					return;
				}

				var result        = response.data;
				var articles      = result.articles || [];
				var pushedCount   = result.pushed_count  || 0;
				var skippedCount  = result.skipped_count || 0;
				var commitUrls    = result.commit_urls   || [];

				articles.forEach( function ( article ) {
					$tbody.append( buildResultRow( article ) );
				} );

				if ( pushedCount === 0 ) {
					$progressText.text( data.strings.nothing || 'All articles are already up to date — nothing to push.' );
				} else {
					$progressText.text( data.strings.done || 'Export complete.' );
				}

				var summaryHtml = 'Pushed: <strong>' + pushedCount + '</strong> &nbsp;·&nbsp; Up to date: <strong>' + skippedCount + '</strong>';
				commitUrls.forEach( function ( url ) {
					summaryHtml += ' &nbsp;·&nbsp; <a href="' + escAttr( url ) + '" target="_blank" rel="noopener">' + ( data.strings.view_commit || 'View commit' ) + '</a>';
				} );
				$summary.show().html( summaryHtml );
			},
			error: function () {
				$progressText.text( 'Request failed. Check your connection and try again.' );
				$commitBtn.prop( 'disabled', false ).text( data.strings.commit_btn || 'Push to GitHub' );
			},
		} );
	} );

	// ── Row builders ─────────────────────────────────────────────────────────

	function buildPendingRow( task ) {
		var editBase  = data.edit_url_base || '';
		var titleCell = ( editBase && task.post_id )
			? '<a href="' + editBase + task.post_id + '" target="_blank" rel="noopener">' + escHtml( task.title || '' ) + '</a>'
			: escHtml( task.title || '' );

		return $( '<tr>' )
			.append( $( '<td>' ).html( '<span style="color:#757575;">&#8212; pending</span>' ) )
			.append( $( '<td>' ).html( titleCell ) )
			.append( $( '<td>' ).html( '<code>' + escHtml( task.path || '' ) + '</code>' ) )
			.append( $( '<td>' ).text( '—' ) );
	}

	function buildResultRow( article ) {
		var editBase   = data.edit_url_base || '';
		var titleCell  = ( editBase && article.post_id )
			? '<a href="' + editBase + article.post_id + '" target="_blank" rel="noopener">' + escHtml( article.title || '' ) + '</a>'
			: escHtml( article.title || '' );

		var statusCell, commitCell;

		if ( 'pushed' === article.status ) {
			statusCell = '<span style="color:#008a20;font-weight:600;">&#10003; pushed</span>';
			commitCell = article.commit_url
				? '<a href="' + escAttr( article.commit_url ) + '" target="_blank" rel="noopener">' + escHtml( data.strings.view_commit || 'View commit' ) + '</a>'
				: '—';
		} else {
			statusCell = '<span style="color:#757575;">&#8212; up to date</span>';
			commitCell = '—';
		}

		var $row = $( '<tr>' );
		if ( 'pushed' !== article.status ) {
			$row.css( 'color', '#757575' );
		}
		$row.append( $( '<td>' ).html( statusCell ) );
		$row.append( $( '<td>' ).html( titleCell ) );
		$row.append( $( '<td>' ).html( '<code>' + escHtml( article.path || '' ) + '</code>' ) );
		$row.append( $( '<td>' ).html( commitCell ) );
		return $row;
	}

	// ── Utilities ─────────────────────────────────────────────────────────────

	function escHtml( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function escAttr( str ) {
		return String( str ).replace( /"/g, '&quot;' ).replace( /'/g, '&#039;' );
	}
} );
