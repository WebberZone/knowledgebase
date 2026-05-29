jQuery(document).ready(function ($) {
	var data = WZKBImportWizard || {};
	var $form = $('#wzkb-github-import-form');
	var $progress = $('#wzkb-import-progress');
	var $progressBar = $('#wzkb-progress-bar-inner');
	var $progressText = $('#wzkb-progress-text');
	var $resultsSection = $('#wzkb-import-results');
	var $tbody = $('#wzkb-results-tbody');
	var $summary = $('#wzkb-import-summary');
	var $submitBtn = $('#wzkb_import_submit');

	$form.on('submit', function (e) {
		e.preventDefault();

		var mappingVal = $('#wzkb_github_mapping').val();
		var ref = $('#wzkb_github_ref').val();
		var force = $('#wzkb_github_force').is(':checked') ? 1 : 0;

		if (!mappingVal) {
			return;
		}

		// Reset UI.
		$progress.show();
		$progressBar.css('width', '0%');
		$progressText.text(data.strings.fetching || 'Fetching file list…');
		$resultsSection.show();
		$tbody.empty();
		$summary.hide().text('');
		$submitBtn.prop('disabled', true);

		$.ajax({
			url: data.ajax_url,
			type: 'POST',
			data: {
				action: 'wzkb_github_import_list_files',
				nonce: data.nonce,
				mapping: mappingVal,
				ref: ref,
				force: force
			},
			success: function (response) {
				if (!response || !response.success) {
					showError((response && response.data && response.data.message) || 'Failed to get file list.');
					return;
				}
				var tasks = response.data.tasks || [];
				if (!tasks.length) {
					showError(data.strings.no_files || 'No files found to import.');
					return;
				}
				processFiles(tasks, 0, { processed: 0, created: 0, updated: 0, skipped: 0, errors: 0 });
			},
			error: function () {
				showError('Request failed. Check your connection and try again.');
			}
		});
	});

	function processFiles(tasks, index, counts) {
		if (index >= tasks.length) {
			$progressBar.css('width', '100%');
			$progressText.text(data.strings.done || 'Import complete.');
			$submitBtn.prop('disabled', false);
			$summary.show().text(
				'Processed: ' + counts.processed +
				' • Created: ' + counts.created +
				' • Updated: ' + counts.updated +
				' • Skipped: ' + counts.skipped +
				' • Errors: ' + counts.errors
			);
			return;
		}

		var pct = Math.round((index / tasks.length) * 100);
		$progressBar.css('width', pct + '%');
		$progressText.text(
			(data.strings.processing || 'Processing') + ' ' + (index + 1) + ' / ' + tasks.length + ': ' + tasks[index].file_path
		);

		var task = tasks[index];

		// If SHA matched server-side, skip without an extra API call.
		if (task.pre_skip) {
			var ps = task.pre_skip;
			appendRow({
				action: 'skipped',
				post_id: ps.post_id,
				title: ps.title,
				permalink: ps.permalink,
				product: ps.product,
				categories: ps.categories,
				tags: ps.tags,
				error: '',
				warning: ''
			});
			counts.processed++;
			counts.skipped++;
			processFiles(tasks, index + 1, counts);
			return;
		}

		$.ajax({
			url: data.ajax_url,
			type: 'POST',
			data: {
				action: 'wzkb_github_import_process_one',
				nonce: data.nonce,
				task: task
			},
			success: function (response) {
				if (response && response.success && response.data) {
					var result = response.data;
					appendRow(result);
					counts.processed++;
					if ('created' === result.action) { counts.created++; }
					else if ('updated' === result.action) { counts.updated++; }
					else if ('skipped' === result.action) { counts.skipped++; }
					else if ('error' === result.action) { counts.errors++; }
				} else {
					appendErrorRow(task.file_path, (response && response.data && response.data.message) || 'Error processing file.');
					counts.processed++;
					counts.errors++;
				}
				processFiles(tasks, index + 1, counts);
			},
			error: function () {
				appendErrorRow(task.file_path, 'Request failed.');
				counts.processed++;
				counts.errors++;
				processFiles(tasks, index + 1, counts);
			}
		});
	}

	function appendRow(result) {
		var postIdCell, titleCell;
		var editBase = data.edit_url_base || '';

		if (result.post_id) {
			postIdCell = '<a href="' + editBase + result.post_id + '" target="_blank">' + result.post_id + '</a>';
		} else {
			postIdCell = '—';
		}

		if (result.post_id && result.permalink) {
			titleCell = '<a href="' + escAttr(result.permalink) + '" target="_blank">' + escHtml(result.title || '') + '</a>';
		} else {
			titleCell = escHtml(result.title || '');
		}

		var notes = result.error || result.warning || '';
		var notesCell;
		if (notes) {
			notesCell = escHtml(notes);
		} else {
			notesCell = '<span style="color:#008a20;font-weight:600;">OK</span>';
		}
		var $row = $('<tr></tr>');
		if ('error' === result.action) {
			$row.css('background-color', '#fce8e8');
		}
		$row.append('<td>' + escHtml(result.action || '') + '</td>');
		$row.append('<td>' + titleCell + '</td>');
		$row.append('<td>' + postIdCell + '</td>');
		$row.append('<td>' + escHtml(result.product || '') + '</td>');
		$row.append('<td>' + escHtml(result.categories || '') + '</td>');
		$row.append('<td>' + escHtml(result.tags || '') + '</td>');
		$row.append('<td>' + notesCell + '</td>');
		$tbody.append($row);
	}

	function appendErrorRow(filePath, message) {
		var $row = $('<tr></tr>').css('background-color', '#fce8e8');
		$row.append('<td>error</td>');
		$row.append('<td>' + escHtml(filePath) + '</td>');
		$row.append('<td>—</td><td></td><td></td><td></td>');
		$row.append('<td>' + escHtml(message) + '</td>');
		$tbody.append($row);
	}

	function showError(message) {
		$progressText.text(message);
		$submitBtn.prop('disabled', false);
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
