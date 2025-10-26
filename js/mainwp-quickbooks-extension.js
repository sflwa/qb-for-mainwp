jQuery(document).ready(function ($) {

    console.log('MainWP QuickBooks Extension JS Loaded. Initializing form handler.'); // <-- LOOK FOR THIS MESSAGE IN YOUR CONSOLE

    // Handle the submission of the QBO Credentials form via AJAX
    $('#mainwp-quickbooks-credentials-form').on('submit', function (e) {
        e.preventDefault(); // CRITICAL: Prevents the standard form submission that caused the WSOD

        const form = $(this);
        const button = form.find('button[type="submit"]');

        // Collect form data
        const data = {
            action: 'mainwp_quickbooks_save_credentials', // The AJAX action defined in class-mainwp-quickbooks-ajax.php
            security: form.find('input[name="security"]').val(), // Nonce field name is 'security'
            client_id: form.find('input[name="client_id"]').val(),
            client_secret: form.find('input[name="client_secret"]').val(),
            redirect_uri: form.find('input[name="redirect_uri"]').val()
        };

        button.addClass('loading').prop('disabled', true);
        $('.ui.message.saved-status').remove(); // Clear previous status messages

        // Perform the AJAX call
        $.post(ajaxurl, data, function (response) {
            button.removeClass('loading').prop('disabled', false);

            const status_message = $('<div>').addClass('ui message saved-status');

            if (response.error) {
                status_message.addClass('error').html('<strong>ERROR:</strong> ' + response.error.message);
            } else if (response.success) {
                status_message.addClass('success').html('<strong>Success:</strong> ' + response.success.message);
            }

            // Append the message after the form
            form.after(status_message);

        }, 'json').fail(function(xhr, status, error) {
             const status_message = $('<div>').addClass('ui message saved-status error').html('<strong>AJAX ERROR:</strong> Failed to communicate with the server. Status: ' + status);
             form.after(status_message);
             button.removeClass('loading').prop('disabled', false);
        });
    });

});
