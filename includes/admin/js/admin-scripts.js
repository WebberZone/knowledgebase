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
			$.post(ajaxurl, {
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
			$.post(ajaxurl, {
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
