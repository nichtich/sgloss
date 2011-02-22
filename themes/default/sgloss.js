$(document).ready(function() {
  /* Messages */
  $('#msg-container div').each(function() {
    var msg = $(this);
    var close = $('<span class="msg-close">×</span>');
    close.click(function() { msg.fadeOut(); });
    msg.prepend(close);
  });
});