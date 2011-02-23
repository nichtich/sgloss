$(document).ready(function() {
  /* Messages */
  $('#msg-container div').each(function() {
    var msg = $(this);
    var close = $('<span class="msg-close">&#x2716;</span>');
    close.click(function() { msg.fadeOut(); });
    msg.prepend(close);
  });
});
