jQuery(document).ready(function($) {
  $('#send-emails-button').click(function(e) {
    e.preventDefault();

    var data = {
      action: 'send_emails',
      security: sendEmailsAjax.security
    };

    $('#send-emails-result').html('Sending emails...');

    $.post(sendEmailsAjax.ajaxurl, data, function(response) {
      $('#send-emails-result').html(response.data);
    });
  });
});
