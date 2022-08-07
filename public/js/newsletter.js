if (jQuery('div#newsletter-view')) {
    jQuery('#edit').click(function() {
        document.location.href = jQuery('#edit-url').val();
    });

    jQuery('#send-test').click(function() {
        var self = jQuery(this);
        var url = self.prev().val();
        self.addClass('hidden').next().removeClass('hidden');

        jQuery.ajax({
            url: url,
            type: 'get',
            data: {email: jQuery('#address').val()},
            success: function(data) {
                self.closest('fieldset').append('<p class="msg">Test email blev sendt</p>');
            },
            error: function(jqXHR) {
                self.closest('fieldset').append('<p class="msg">Test email kunne ikke sendes</p>');
            },
            complete: function() {
                self.removeClass('hidden').next().addClass('hidden');
                window.setTimeout(function() {jQuery('p.msg').fadeOut();}, 3000);
            }
        });
    });

    jQuery('#send').click(function() {
        var self = jQuery(this);
        var url = self.prev().val();
        self.addClass('hidden').next().removeClass('hidden');

        jQuery.ajax({
            url: url,
            type: 'get',
            success: function(data) {
                self.closest('p').replaceWith('<p class="msg">Nyhedsbrevet blev sendt</p>');
            },
            error: function(jqXHR) {
                self.closest('p').replaceWith('<p class="msg">Nyhedsbrevet kunne ikke sendes</p>');
            }
        });
    });
}
