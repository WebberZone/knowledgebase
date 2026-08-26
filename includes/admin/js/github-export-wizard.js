/**
 * GitHub Export Wizard — preview linked articles, prepare bounded chunks, and create
 * one commit per repository and branch.
 *
 * @package WebberZone\Knowledge_Base\Pro\GitHub
 */

/* global WZKBExportWizard */
jQuery(document).ready(function ($) {
	var data = WZKBExportWizard || {};
	data.strings = data.strings || {};
	var $form = $('#wzkb-github-export-form');
	var $commitWrap = $('#wzkb-export-commit-wrap');
	var $commitBtn = $('#wzkb-export-commit-btn');
	var $retryBtn = $('#wzkb-export-retry-btn');
	var $progress = $('#wzkb-export-progress');
	var $progressBar = $('#wzkb-export-progress-bar');
	var $progressText = $('#wzkb-export-progress-text');
	var $results = $('#wzkb-export-results');
	var $tbody = $('#wzkb-export-results-tbody');
	var $summary = $('#wzkb-export-summary');
	var $listBtn = $('#wzkb_export_submit');
	var currentMapping = '';
	var previewId = '';
	var jobId = '';
	var currentPhase = '';
	var retryAttempts = 0;
	var retryTimer = null;
	var tasksByKey = {};
	var commitUrls = {};

	// A job outlives the page it was started from, so pick up any export still running for this user.
	if (data.job_id) {
		jobId = data.job_id;
		$results.show();
		$progress.show();
		$listBtn.prop('disabled', true);
		resumeJob();
	}

	// An in-flight response for a job the user has walked away from must not write into the new table.
	function isStale(result) {
		return result && result.job_id && result.job_id !== jobId;
	}

	$form.on('submit', function (e) {
		e.preventDefault();

		currentMapping = $('#wzkb_github_export_mapping').val();
		if (!currentMapping) {
			return;
		}

		clearRetry();
		previewId = '';
		jobId = '';
		currentPhase = 'list';
		retryAttempts = 0;
		tasksByKey = {};
		commitUrls = {};
		$commitWrap.hide();
		$progress.show();
		$progressBar.css('width', '10%').attr('aria-valuenow', 10);
		$progressText.text(data.strings.fetching || 'Fetching article list…');
		$results.show();
		$tbody.empty();
		$summary.hide().html('');
		$listBtn.prop('disabled', true);

		listPreview();
	});

	function listPreview() {
		currentPhase = 'list';
		$.ajax({
			url: data.ajax_url,
			type: 'POST',
			timeout: 120000,
			data: {
				action: 'wzkb_github_export_list_articles',
				nonce: data.nonce,
				mapping: currentMapping,
				preview_id: previewId,
			},
			success: function (response) {
				if (!response || !response.success) {
					showError((response && response.data && response.data.message) || 'Failed to get article list.');
					return;
				}

				var result = response.data || {};
				previewId = result.preview_id || previewId;
				appendTasks(result.tasks || []);
				if (!result.done) {
					$progressText.text((data.strings.fetching || 'Fetching article list…') + ' ' + (result.processed_linked || 0) + ' scanned');
					window.setTimeout(listPreview, 0);
					return;
				}

				$listBtn.prop('disabled', false);
				$progressBar.css('width', '100%').attr('aria-valuenow', 100);
				if (!Object.keys(tasksByKey).length) {
					var msg = result.all_up_to_date
						? (data.strings.nothing || 'All articles are already up to date — nothing to push.')
						: (data.strings.no_articles || 'No linked articles found for this mapping.');
					$progressText.text(msg);
					return;
				}

				$progressText.text(Object.keys(tasksByKey).length + ' article' + (Object.keys(tasksByKey).length !== 1 ? 's' : '') + ' ready to push.');
				$commitWrap.show();
				$commitBtn.text(data.strings.commit_btn || 'Push to GitHub').prop('disabled', false);
			},
			error: function () {
				showError('Request failed. Check your connection and try again.');
			}
		});
	}

	$commitBtn.on('click', function () {
		if (!currentMapping || !previewId) {
			return;
		}

		clearRetry();
		retryAttempts = 0;
		$commitBtn.prop('disabled', true).text(data.strings.starting || 'Starting export…');
		$listBtn.prop('disabled', true);
		$progressBar.css('width', '0%').attr('aria-valuenow', 0);
		$progressText.text(data.strings.starting || 'Starting export…');
		$summary.hide().html('');
		startJob();
	});

	$retryBtn.on('click', function () {
		var phase = $retryBtn.data('phase') || currentPhase;
		clearRetry();
		retryAttempts = 0;
		$commitBtn.prop('disabled', true);
		if ('start' === phase) {
			startJob();
		} else {
			resumeJob();
		}
	});

	function startJob() {
		currentPhase = 'start';
		$.ajax({
			url: data.ajax_url,
			type: 'POST',
			data: {
				action: 'wzkb_github_export_start',
				nonce: data.nonce,
				preview_id: previewId,
			},
			success: function (response) {
				if (!response || !response.success) {
					handleServerFailure(response, 'start');
					return;
				}
				jobId = response.data.job_id || jobId;
				if (response.data.tasks) {
					renderTasks(response.data.tasks);
				}
				retryAttempts = 0;
				setProgress(response.data, data.strings.preparing || 'Preparing articles…');
				processChunk();
			},
			error: function () {
				scheduleRetry(null, 'start', 'Request failed. Retrying…');
			}
		});
	}

	function resumeJob() {
		if (!jobId) {
			return;
		}
		currentPhase = 'status';
		$.ajax({
			url: data.ajax_url,
			type: 'POST',
			data: {
				action: 'wzkb_github_export_status',
				nonce: data.nonce,
				job_id: jobId,
			},
			success: function (response) {
				if (!response || !response.success) {
					handleServerFailure(response, 'status');
					return;
				}
				var result = response.data || {};
				if (isStale(result)) {
					return;
				}
				if (result.tasks) {
					renderTasks(result.tasks);
				}
				rememberCommitUrls(result.commit_urls || []);
				setProgress(result, 'complete' === result.status ? (data.strings.done || 'Export complete.') : (data.strings.preparing || 'Resuming export…'));
				if ('paused' === result.status && 'commit' === result.phase && result.commit_attempted) {
					showPaused(result.last_error || data.strings.paused || 'Export paused.');
					return;
				}
				if ('complete' === result.status || 'complete' === result.phase) {
					finishJob();
				} else if ('process' === result.next_action || 'prepare' === result.phase) {
					processChunk();
				} else {
					finalizeJob();
				}
			},
			error: function () {
				scheduleRetry(null, 'status', 'Request failed. Retrying…');
			}
		});
	}

	function processChunk() {
		if (!jobId) {
			return;
		}
		currentPhase = 'process';
		$commitBtn.prop('disabled', true).text(data.strings.preparing || 'Preparing articles…');
		$.ajax({
			url: data.ajax_url,
			type: 'POST',
			timeout: 120000,
			data: {
				action: 'wzkb_github_export_process_chunk',
				nonce: data.nonce,
				job_id: jobId,
			},
			success: function (response) {
				if (!response || !response.success) {
					handleServerFailure(response, 'process');
					return;
				}

				var result = response.data || {};
				if (isStale(result)) {
					return;
				}
				applyResults(result.results || []);
				rememberCommitUrls(result.commit_urls || []);
				setProgress(result, result.ready_to_finalize ? (data.strings.ready || 'Ready to commit…') : (data.strings.preparing || 'Preparing articles…'));

				if (result.retryable) {
					scheduleRetry(result, 'process', result.error || 'Retrying…');
					return;
				}
				if ('paused' === result.status) {
					showPaused(result.error || data.strings.paused || 'Export paused.');
					return;
				}
				retryAttempts = 0;
				if ('finalize' === result.next_action || result.ready_to_finalize) {
					finalizeJob();
				} else if ('complete' === result.status) {
					finishJob();
				} else {
					window.setTimeout(processChunk, 0);
				}
			},
			error: function () {
				scheduleRetry(null, 'process', 'Request failed. Retrying…');
			}
		});
	}

	function finalizeJob() {
		if (!jobId) {
			return;
		}
		currentPhase = 'finalize';
		$commitBtn.prop('disabled', true).text(data.strings.finalizing || 'Creating commit…');
		$progressText.text(data.strings.finalizing || 'Creating commit…');
		$.ajax({
			url: data.ajax_url,
			type: 'POST',
			timeout: 120000,
			data: {
				action: 'wzkb_github_export_finalize',
				nonce: data.nonce,
				job_id: jobId,
			},
			success: function (response) {
				if (!response || !response.success) {
					handleServerFailure(response, 'finalize');
					return;
				}

				var result = response.data || {};
				if (isStale(result)) {
					return;
				}
				if (result.tasks) {
					renderTasks(result.tasks);
				}
				applyResults(result.results || []);
				rememberCommitUrls(result.commit_urls || []);
				if (result.retryable) {
					scheduleRetry(result, 'finalize', result.error || 'Retrying…');
					return;
				}
				if ('paused' === result.status) {
					showPaused(result.error || data.strings.paused || 'Export paused.');
					return;
				}
				retryAttempts = 0;
				if ('process' === result.next_action) {
					processChunk();
				} else if ('metadata' === result.status) {
					window.setTimeout(finalizeJob, 0);
				} else if (result.next_group) {
					window.setTimeout(processChunk, 0);
				} else {
					finishJob();
				}
			},
			error: function () {
				scheduleRetry(null, 'finalize', 'Request failed. Retrying…');
			}
		});
	}

	function applyResults(results) {
		results.forEach(function (result) {
			if (result.task_key && tasksByKey[result.task_key]) {
				tasksByKey[result.task_key].status = result.status;
				tasksByKey[result.task_key].commit_url = result.commit_url || '';
				tasksByKey[result.task_key].error = result.error || '';
			}
			var $row = $tbody.find('tr').filter(function () {
				return String($(this).attr('data-task-key') || '') === String(result.task_key || '');
			});
			var $newRow = buildResultRow(result);
			if ($row.length) {
				$row.replaceWith($newRow);
			} else {
				$tbody.append($newRow);
			}
		});
	}

	function setProgress(result, message) {
		var total = parseInt(result.total, 10) || 0;
		var processed = parseInt(result.processed, 10) || 0;
		var percent = total ? Math.min(99, Math.round((processed / total) * 100)) : 0;
		var scope = result.repository && result.branch ? ' (' + result.repository + ' @ ' + result.branch + ')' : '';
		$progress.show();
		$progressBar.css('width', percent + '%').attr('aria-valuenow', percent);
		$progressText.text(message + scope + (total ? ' ' + processed + ' / ' + total : ''));
	}

	function rememberCommitUrls(urls) {
		urls.forEach(function (url) {
			if (url) {
				commitUrls[url] = true;
			}
		});
	}

	function finishJob() {
		clearRetry();
		currentPhase = '';
		$progressBar.css('width', '100%').attr('aria-valuenow', 100);
		$progressText.text(data.strings.done || 'Export complete.');
		$commitBtn.prop('disabled', !previewId).text(data.strings.commit_btn || 'Push to GitHub');
		$listBtn.prop('disabled', false);
		$retryBtn.hide();

		var pushed = 0;
		var skipped = 0;
		var errors = 0;
		Object.keys(tasksByKey).forEach(function (key) {
			if ('pushed' === tasksByKey[key].status) {
				pushed++;
			} else if ('skipped' === tasksByKey[key].status) {
				skipped++;
			} else if ('error' === tasksByKey[key].status) {
				errors++;
			}
		});

		var summaryHtml = 'Pushed: <strong>' + pushed + '</strong> &nbsp;·&nbsp; Up to date: <strong>' + skipped + '</strong>';
		if (errors) {
			summaryHtml += ' &nbsp;·&nbsp; Errors: <strong>' + errors + '</strong>';
		}
		Object.keys(commitUrls).forEach(function (url) {
			summaryHtml += ' &nbsp;·&nbsp; <a href="' + escAttr(url) + '" target="_blank" rel="noopener">' + escHtml(data.strings.view_commit || 'View commit') + '</a>';
		});
		$summary.show().html(summaryHtml);
	}

	function handleServerFailure(response, phase) {
		var result = response && response.data ? response.data : {};
		if (result.results) {
			applyResults(result.results);
		}
		if (result.retryable) {
			scheduleRetry(result, phase, result.error || result.message || 'Retrying…');
			return;
		}
		if ('start' === phase) {
			// The server still holds a job for this user, so adopt it instead of stranding the export.
			if (result.job_id) {
				jobId = result.job_id;
				previewId = '';
				$commitWrap.show();
				resumeJob();
				return;
			}
			showError(result.error || result.message || 'Export failed.');
			return;
		}
		showPaused(result.error || result.message || 'Export failed.');
	}

	function scheduleRetry(result, phase, message) {
		clearRetry();
		retryAttempts++;
		if (retryAttempts > 3) {
			showRetry(phase, message);
			return;
		}
		var retryAfter = result && parseInt(result.retry_after, 10) ? parseInt(result.retry_after, 10) : 0;
		var delay = retryAfter > 0 ? retryAfter * 1000 : Math.min(30000, 1000 * Math.pow(2, retryAttempts - 1));
		$progressText.text((data.strings.retrying || 'Retrying…') + ' ' + Math.ceil(delay / 1000) + 's');
		retryTimer = window.setTimeout(function () {
			retryTimer = null;
			if ('start' === phase) {
				startJob();
			} else {
				resumeJob();
			}
		}, delay);
	}

	function showPaused(message) {
		$progressText.text(message);
		$commitBtn.prop('disabled', true);
		$listBtn.prop('disabled', false);
		showRetry(currentPhase, message);
	}

	function showRetry(phase, message) {
		$progressText.text(message || data.strings.paused || 'Export paused.');
		$retryBtn.data('phase', phase).text(data.strings.retry_btn || 'Retry export').show();
	}

	function clearRetry() {
		if (retryTimer) {
			window.clearTimeout(retryTimer);
			retryTimer = null;
		}
		$retryBtn.hide();
	}

	function showError(message) {
		currentPhase = '';
		$progressText.text(message);
		$listBtn.prop('disabled', false);
	}

	function appendTasks(tasks) {
		tasks.forEach(function (task) {
			if (!task.task_key || tasksByKey[task.task_key]) {
				return;
			}
			tasksByKey[task.task_key] = task;
			$tbody.append(buildPendingRow(task));
		});
	}

	function renderTasks(tasks) {
		tasksByKey = {};
		$tbody.empty();
		tasks.forEach(function (task) {
			tasksByKey[task.task_key] = task;
			$tbody.append('pending' === (task.status || 'pending') ? buildPendingRow(task) : buildResultRow(task));
		});
	}

	function buildPendingRow(task) {
		var editBase = data.edit_url_base || '';
		var titleCell = (editBase && task.post_id)
			? '<a href="' + editBase + task.post_id + '" target="_blank" rel="noopener">' + escHtml(task.title || '') + '</a>'
			: escHtml(task.title || '');

		return $('<tr>')
			.attr('data-task-key', task.task_key || '')
			.append($('<td>').html('<span style="color:#757575;">&#8212; pending</span>'))
			.append($('<td>').html(titleCell))
			.append($('<td>').html('<code>' + escHtml(task.path || '') + '</code>'))
			.append($('<td>').text('—'))
			.append($('<td>').text('—'));
	}

	function buildResultRow(article) {
		var editBase = data.edit_url_base || '';
		var titleCell = (editBase && article.post_id)
			? '<a href="' + editBase + article.post_id + '" target="_blank" rel="noopener">' + escHtml(article.title || '') + '</a>'
			: escHtml(article.title || '');
		var statusCell = '<span style="color:#757575;">&#8212; ' + escHtml(article.status || 'pending') + '</span>';
		var status = article.status || '';

		if ('pushed' === status) {
			statusCell = '<span style="color:#008a20;font-weight:600;">&#10003; pushed</span>';
		} else if ('skipped' === status) {
			statusCell = '<span style="color:#757575;">&#8212; up to date</span>';
		} else if ('prepared' === status) {
			statusCell = '<span style="color:#2271b1;">&#8226; ready</span>';
		} else if ('error' === status) {
			statusCell = '<span style="color:#b32d2e;font-weight:600;">&#10007; failed</span>';
		}

		var commitCell = article.commit_url
			? '<a href="' + escAttr(article.commit_url) + '" target="_blank" rel="noopener">' + escHtml(data.strings.view_commit || 'View commit') + '</a>'
			: '—';
		var notesCell = article.error ? escHtml(article.error) : '—';
		var $row = $('<tr>').attr('data-task-key', article.task_key || '');
		if ('pushed' !== status && 'error' !== status) {
			$row.css('color', '#757575');
		}
		$row.append($('<td>').html(statusCell));
		$row.append($('<td>').html(titleCell));
		$row.append($('<td>').html('<code>' + escHtml(article.path || '') + '</code>'));
		$row.append($('<td>').html(commitCell));
		$row.append($('<td>').html(notesCell));
		return $row;
	}

	function escHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function escAttr(str) {
		return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
	}
});
