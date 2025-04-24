/* global ajaxurl, wzkbProductMigrator */
(function ($) {
	'use strict';

	var step = 0;

	var migrationState = {};
	var lastTopSectionIndex = -1;

	/**
	 * Updates the migration progress bar.
	 *
	 * @param {number} percent - The percentage of completion.
	 * @param {string} message - The message to display.
	 */
	function updateProgressBar(percent, message) {
		$('#wzkb-migration-progress-bar').css('width', percent + '%');
		$('#wzkb-migration-progress-bar').html(percent + '%');
		$('#wzkb-migration-progress-text').html(message);
	}

	/**
	 * Displays an error message.
	 *
	 * @param {string} message - The error message.
	 */
	function showError(message) {
		$('#wzkb-migration-errors').append('<li>' + message + '</li>');
	}

	/**
	 * Appends a log message.
	 *
	 * @param {string} html - The log message HTML.
	 */
	function appendToLog(html) {
		if (html) {
			var timestamp = new Date().toLocaleString();
			var $log = $('#wzkb-migration-log');
			$log.append('<p><strong>[' + timestamp + ']</strong> ' + html + '</p>');
			// Scroll to bottom
			$log.scrollTop($log[0].scrollHeight);
			// Force render
			setTimeout(function () { $log[0].offsetHeight; }, 0);
		}
	}

	/**
	 * Proceeds to the next migration step.
	 */
	function nextMigrationStep() {
		var stateToSend = JSON.parse(JSON.stringify(migrationState));
		// Track consecutive identical indices
		if (stateToSend.current_top_section_index !== undefined && stateToSend.current_top_section_index === lastTopSectionIndex) {
			if (!window.wzkbMigrationLoopCount) {
				window.wzkbMigrationLoopCount = 0;
			}
			window.wzkbMigrationLoopCount++;
			if (window.wzkbMigrationLoopCount >= 5) {
				console.warn('Warning: current_top_section_index has not changed after 5 attempts:', stateToSend.current_top_section_index);
				window.wzkbMigrationLoopCount = 0; // Reset to avoid spamming.
			}
		} else {
			window.wzkbMigrationLoopCount = 0; // Reset on change.
		}
		lastTopSectionIndex = stateToSend.current_top_section_index;

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wzkb_product_migration_batch',
				_nonce: wzkbProductMigrator.nonce,
				step: step,
				dry_run: $('#wzkb-dry-run').is(':checked') ? 1 : 0,
				state: stateToSend
			},
			success: function (response) {
				if (!response.success) {
					showError(response.data || wzkbProductMigrator.strings.unknown_error);
					updateProgressBar(100, wzkbProductMigrator.strings.migration_failed);
					$('#wzkb-migration-start').prop('disabled', false);
					return;
				}

				if (response.data.log && Array.isArray(response.data.log)) {
					response.data.log.forEach(function (line) { appendToLog(line); });
				}

				if (response.data.message) {
					appendToLog(response.data.message);
				}

				if (response.data.progress) {
					updateProgressBar(response.data.progress, response.data.message);
				}

				if (response.data.errors && response.data.errors.length) {
					response.data.errors.forEach(showError);
				}

				if (response.data.dry_run && response.data.done) {
					updateProgressBar(100, response.data.message);
					$('#wzkb-migration-start').prop('disabled', false);
					return;
				}

				if (response.data.done) {
					var completionMessage = response.data.message || wzkbProductMigrator.strings.migration_complete;
					updateProgressBar(100, completionMessage);
					$('#wzkb-migration-start').prop('disabled', false);
					return;
				}

				step = response.data.next_step;
				migrationState = JSON.parse(JSON.stringify(response.data.state));

				setTimeout(nextMigrationStep, 100);
			},
			error: function (xhr, status, error) {
				console.error('AJAX error:', status, error);
				showError(wzkbProductMigrator.strings.ajax_error + ': ' + error);
				updateProgressBar(100, wzkbProductMigrator.strings.migration_failed);
				$('#wzkb-migration-start').prop('disabled', false);
			}
		});
	}

	$(document).ready(function () {
		$('#wzkb-migration-start').prop('disabled', true);
		$('#wzkb-backup-confirm').on('change', function () {
			$('#wzkb-migration-start').prop('disabled', !this.checked);
		});

		$('#wzkb-migration-start').on('click', function (e) {
			e.preventDefault();
			step = 0;
			migrationState = {};
			lastTopSectionIndex = -1;
			$('#wzkb-migration-progress-bar').css('width', '0%');
			$('#wzkb-migration-progress-text').html('');
			$('#wzkb-migration-errors').empty();
			$('#wzkb-migration-log').empty();
			$(this).prop('disabled', true);
			updateProgressBar(0, wzkbProductMigrator.strings.starting_migration);

			nextMigrationStep();
		});

		// Copy log to clipboard functionality
		$('#wzkb-copy-log').on('click', function () {
			var $button = $(this);
			var $log = $('#wzkb-migration-log');
			var logText = '';

			// Extract text from each paragraph with line breaks
			$log.find('p').each(function () {
				logText += $(this).text() + '\n\n';
			});

			// Trim extra line breaks at the end
			logText = logText.trim();

			if (!logText) {
				$button.html('<span class="dashicons dashicons-warning"></span> Empty Log');
				setTimeout(function () {
					$button.html('<span class="dashicons dashicons-clipboard" style="margin-right:5px;"></span> Copy Log');
				}, 2000);
				return;
			}

			// Modern approach using Clipboard API
			navigator.clipboard.writeText(logText).then(function () {
				$button.html('<span class="dashicons dashicons-yes"></span> Copied!');
				setTimeout(function () {
					$button.html('<span class="dashicons dashicons-clipboard" style="margin-right:5px;"></span> Copy Log');
				}, 2000);
			}).catch(function (err) {
				console.error('Failed to copy: ', err);
				$button.html('<span class="dashicons dashicons-no"></span> Failed!');
				setTimeout(function () {
					$button.html('<span class="dashicons dashicons-clipboard" style="margin-right:5px;"></span> Copy Log');
				}, 2000);
			});
		});
	});

})(jQuery);