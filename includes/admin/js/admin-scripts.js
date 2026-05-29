jQuery(document).ready(
	function ($) {
		$('button[name="wzkb_cache_clear"]').on('click', function () {
			if (confirm(WZKBAdminData.strings.confirm_message)) {
				var $button = $(this);
				$button.prop('disabled', true).append(' <span class="spinner is-active"></span>');
				clearCache($button);
			}
		});

		$('button[name="wzkb_flush_permalinks"]').on('click', function (e) {
			e.preventDefault();
			var $button = $(this);
			var nonce = $button.data('nonce');
			$button.prop('disabled', true).append(' <span class="spinner is-active"></span>');
			flushPermalinks($button, nonce);
		});

		// Function to clear the cache.
		function clearCache($button) {
			$.post(WZKBAdminData.ajax_url, {
				action: 'wzkb_clear_cache',
				security: WZKBAdminData.security
			}, function (response) {
				if (response.success) {
					// Use WordPress admin notice instead of alert().
					showAdminNotice(WZKBAdminData.strings.success_message, 'success');
				} else {
					showAdminNotice(WZKBAdminData.strings.fail_message, 'error');
				}
			}).fail(function (jqXHR, textStatus) {
				showAdminNotice(WZKBAdminData.strings.fail_message, 'error');
				console.log(WZKBAdminData.strings.request_fail_message + textStatus);
			}).always(function () {
				$button.prop('disabled', false).find('.spinner').remove();
			});
		}

		// Function to flush permalinks.
		function flushPermalinks($button, nonce) {
			$.post(WZKBAdminData.ajax_url, {
				action: 'wzkb_flush_permalinks',
				nonce: nonce
			}, function (response) {
				if (response.success) {
					showAdminNotice(response.data.message, 'success');
				} else {
					showAdminNotice(WZKBAdminData.strings.fail_message, 'error');
				}
			}).fail(function (jqXHR, textStatus) {
				showAdminNotice(WZKBAdminData.strings.fail_message, 'error');
				console.log(WZKBAdminData.strings.request_fail_message + textStatus);
			}).always(function () {
				$button.prop('disabled', false).text(WZKBAdminData.strings.flush_permalinks_text || 'Flush Permalinks');
			});
		}

		// Function to show WordPress admin notices.
		function showAdminNotice(message, type) {
			var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
			var $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');

			// Insert notice after the first h1 or h2 in the page.
			if ($('.wrap > h1, .wrap > h2').length) {
				$('.wrap > h1, .wrap > h2').first().after($notice);
			} else {
				$('.wrap').prepend($notice);
			}

			// Scroll to notice.
			$('html, body').animate({ scrollTop: $notice.offset().top - 100 }, 300);

			// Auto-dismiss after 5 seconds.
			setTimeout(function () {
				$notice.fadeOut(300, function () {
					$(this).remove();
				});
			}, 5000);
		}

		// Verify GitHub PAT via REST API.
		function setStatus($node, ok, message) {
			$node
				.text(message)
				.attr('title', message)
				.css('color', ok ? '#008a20' : '#a60000');
		}

		$(document).on('click', '.wzkb-clear-github-repos-cache', function (e) {
			e.preventDefault();
			var $button = $(this);
			var $status = $button.siblings('.wzkb-clear-repos-cache-status');
			var data = WZKBAdminData || {};
			var strings = data.strings || {};

			// If inside a repeater row, require a non-blank PAT.
			var $repeaterItem = $button.closest('.wz-repeater-item');
			if ($repeaterItem.length) {
				var $patInput = $repeaterItem.find('input[type="text"], input[type="password"]').filter('[name*="[pat]"]').first();
				if (!$patInput.length || !$patInput.val().trim()) {
					setStatus($status, false, strings.pat_required || '');
					return;
				}
			}

			$button.prop('disabled', true);
			$status.html('<span class="spinner is-active" style="float:none;margin:0;"></span>');
			$.ajax({
				url: data.ajax_url || ajaxurl,
				type: 'POST',
				data: {
					action: 'wzkb_clear_github_repos_cache',
					nonce: data.security || ''
				},
				success: function (response) {
					if (response && response.success) {
						setStatus($status, true, (response.data && response.data.message) || strings.repos_refreshed || '');
					} else {
						setStatus($status, false, (response && response.data && response.data.message) || strings.repos_refresh_failed || '');
					}
				},
				error: function () {
					setStatus($status, false, strings.repos_refresh_failed || '');
				},
				complete: function () {
					$button.prop('disabled', false);
				}
			});
		});

		$(document).on('click', '.wzkb-verify-github-pat', function (e) {
			e.preventDefault();
			var $button = $(this);
			var $status = $button.siblings('.wzkb-github-pat-status');
			var data = WZKBAdminData || {};
			var strings = data.strings || {};
			var postData = {
				action: 'wzkb_verify_github_pat',
				nonce: data.security || ''
			};

			// The input is a direct child of the td/container; the button is inside a <p> sibling.
			var $input = $button.closest('p').siblings('input[type="text"],input[type="password"]').first();
			if (!$input.length) {
				$input = $button.closest('td, .repeater-item-content').find('input[type="text"],input[type="password"]').first();
			}
			postData.pat = ($input.val() || '').trim();

			if ($button.hasClass('wzkb-verify-mapping-pat')) {
				var $row = $button.closest('.wz-repeater-item');
				postData.mapping_row = $row.data('row-id') || '';
			}

			$button.prop('disabled', true);
			$status.html('<span class="spinner is-active" style="float:none;margin:0;"></span>');

			function permLine(state, label) {
				if (state === null) {
					return '<span style="color:#666;">? ' + label + ' (' + (strings.no_repo_to_test || '') + ')</span>';
				}
				var color = state ? '#008a20' : '#a60000';
				var icon = state ? '✓' : '✗';
				return '<span style="color:' + color + ';">' + icon + ' ' + label + '</span>';
			}

			$.ajax({
				url: data.ajax_url || ajaxurl,
				type: 'POST',
				data: postData,
				success: function (response) {
					if (response && response.success) {
						var validMsg = response.data.message || strings.token_valid || '';
						var msg = $('<span>').text(validMsg).prop('outerHTML');
						var perms = response.data.permissions;
						if (perms) {
							msg += '<br>';
							msg += permLine(perms.contents_read, strings.contents_read_label || 'contents:read');
							msg += '&ensp;';
							msg += permLine(perms.contents_write, strings.contents_write_label || 'contents:write');
						}
						$status.html(msg).css('color', '#008a20').attr('title', validMsg);
					} else {
						setStatus($status, false, (response && response.data && response.data.message) || strings.pat_verify_error || '');
					}
				},
				error: function () {
					setStatus($status, false, strings.pat_verify_error || '');
				},
				complete: function () {
					$button.prop('disabled', false);
				}
			});
		});

		$(
			function () {
				$("#post-body-content").tabs(
					{
						create: function (event, ui) {
							$(ui.tab.find("a")).addClass("nav-tab-active");
						},
						activate: function (event, ui) {
							$(ui.oldTab.find("a")).removeClass("nav-tab-active");
							$(ui.newTab.find("a")).addClass("nav-tab-active");
						}
					}
				);
			}
		);
	}
);
