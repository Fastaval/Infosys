var Ajax = (function($) {
    function Ajax(test_mode, use_success) {
        this.test_mode   = !!test_mode;
        this.use_success = !!use_success;
    };

    Ajax.prototype.request = function(url, data, success, failure, is_post) {
        if (this.test_mode) {
            console.log(data);
            window.setTimeout(this.use_success ? success : failure, 50);
            return;
        }

        $.ajax({
            url:     url,
            type:    is_post ? 'POST' : 'GET',
            data:    data,
            success: success,
            error:   failure
        });
    };

    return Ajax;
})(jQuery);
