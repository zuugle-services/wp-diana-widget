jQuery(document).ready(function ($) {
	$('#diana-verify-credentials').on('click', function () {
		var button = $(this);
		var resultDiv = $('#diana-verification-result');
		var originalText = button.text();
		var clientId = $('#DIANA_GREENCONNECT_client_id').val();
		var clientSecret = $('#DIANA_GREENCONNECT_client_secret').val();

		button.text(diana_greenconnect_ajax.testing_text).prop('disabled', true);
		resultDiv.hide();

		$.ajax({
			url: diana_greenconnect_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'diana_verify_credentials',
				nonce: diana_greenconnect_ajax.nonce,
				client_id: clientId,
				client_secret: clientSecret,
			},
			success: function (response) {
				if (response.success) {
					resultDiv.removeClass('error').addClass('success').text(response.data).show();
				} else {
					resultDiv.removeClass('success').addClass('error').text(response.data).show();
				}
			},
			error: function () {
				resultDiv.removeClass('success').addClass('error').text('An unknown error occurred.').show();
			},
			complete: function () {
				button.text(originalText).prop('disabled', false);
			}
		});
	});
});
