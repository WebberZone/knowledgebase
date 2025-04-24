jQuery(document).ready(
	function ($) {
		$('button[name="wzkb_cache_clear"]').on('click', function () {
			if (confirm(WZKBAdminData.strings.confirm_message)) {
				var $button = $(this);
				$button.prop('disabled', true).append(' <span class="spinner is-active"></span>');
				clearCache($button);
			}
		});

		// Function to clear the cache.
		function clearCache($button) {
			$.post(WZKBAdminData.ajax_url, {
				action: 'wzkb_clear_cache',
				security: WZKBAdminData.security
			}, function (response) {
				if (response.success) {
					alert(WZKBAdminData.strings.success_message);
				} else {
					alert(WZKBAdminData.strings.fail_message);
				}
			}).fail(function (jqXHR, textStatus) {
				alert(WZKBAdminData.strings.fail_message);
				console.log(WZKBAdminData.strings.request_fail_message + textStatus);
			}).always(function () {
				$button.prop('disabled', false).find('.spinner').remove();
			});
		}

		// Prompt the user when they leave the page without saving the form.
		var formmodified = 0;

		function confirmFormChange() {
			formmodified = 1;
		}

		function confirmExit() {
			if (formmodified == 1) {
				return true;
			}
		}

		function formNotModified() {
			formmodified = 0;
		}

		$('form *').change(confirmFormChange);

		window.onbeforeunload = confirmExit;

		$("input[name='submit']").click(formNotModified);
		$("input[id='search-submit']").click(formNotModified);
		$("input[id='doaction']").click(formNotModified);
		$("input[id='doaction2']").click(formNotModified);
		$("input[name='filter_action']").click(formNotModified);

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
