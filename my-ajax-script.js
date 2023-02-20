jQuery(document).ready(function($) {
    // Handler for .ready() called.
    $('#duty-form').on('submit', function(event) {
      event.preventDefault();
      var onDuty = $('#on-duty').val();
      var offDuty = $('#off-duty').val();
      $.ajax({
        url: myAjax.ajaxurl,
        type: 'POST',
        data: {
          action: 'discord_duty',
          on_duty: onDuty,
          off_duty: offDuty
        },
        success: function(data) {
          console.log('Data sent: ' + data);
        },
        error: function(errorThrown) {
          console.log(errorThrown);
        }
      });
    });
  });