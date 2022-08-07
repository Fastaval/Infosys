var food_handout = {

    // url for ajax calls
    ajax_uri: '',

    // user id input box element
    input_box: null,

    // mark received button element
    done_button: null,

    // stats container div element
    stats_container: null,

    // id of last user served
    last_user: null,

    // whether we're currently processing an undo request
    undo_in_progress: false,

    // whether we're currently processing a checkin request
    handout_in_progress: false,

    oh_no: null,

    wait: null,

    // IDs of the food items currently served
    food_item_ids: [],

    setup: function() {
        if (window.ajax_uri) {
            this.ajax_uri = window.ajax_uri;
        }
        if (this.input_box = jQuery('div#food-handout-container input#user-id')) {
            this.done_button = jQuery('div#food-handout-container input#mark-done');
            this.undo_container = jQuery('div#undo-container');
            this.undo_button = jQuery('div#undo-container input');
            this.registration_container = jQuery('div#registration-container');
            this.registration_result = jQuery('div#registration-container #registration-result');
            this.stats_container = jQuery('#stats-container');
            var that = this;
            this.input_box.keyup(function(event){
                if (event.keyCode == 13) {
                    that.done_button.click();
                }
            });
            this.done_button.click(function() {
                that.mark_food_delivered();
            });
            this.undo_button.click(function() {
                that.undo_last_register();
            });
            this.get_food_item_ids();
            this.get_food_stats();
            this.input_box.focus();

            this.oh_no = document.getElementById('oh-no');
            this.wait  = document.getElementById('wait');
        }
    },

    soundWait: function () {
        if (this.wait) {
            this.wait.play();
        }
    },

    soundError: function () {
        if (this.oh_no) {
            this.oh_no.play();
        }
    },

    update_result_box: function(msg, type) {
        switch (type) {
            case 'success':
            case 'error':
                var content = "<div class='" + type + "'>" + msg + "</div>";
                break;
        }
        this.registration_container.removeClass('hidden');
        this.registration_result.html(content);
    },

    hide_undo_box: function() {
        this.undo_container.addClass('hidden');
    },

    show_undo_box: function() {
        this.undo_container.removeClass('hidden');
    },

    get_food_item_ids: function() {
        var ids = [];
        var that = this;
        jQuery('.fooditem_id').each(function(idx, item) {
            that.food_item_ids.push(jQuery(item).val());
        });
    },

    mark_food_delivered: function() {
        if (this.undo_in_progress || this.handout_in_progress) {
            this.soundWait();
            return;
        }

        this.last_user = this.input_box.val();
        var that = this;
        this.input_box.val('').focus();

        if (this.last_user == '') {
            this.hide_undo_box();
            this.update_result_box("<strong>Fejl:</strong> Intet id indtastet", 'error');

            this.soundError();
        } else {
            this.registration_result.html('<img src="/img/spinner.gif" alt="Spinner">');

            this.handout_in_progress = true;

            jQuery.ajax(this.ajax_uri, {
                type: 'post',
                data: {action: 'mark-received', user_id: that.last_user, fooditem_ids: that.food_item_ids},
                timeout: 3000,
                success: function(data, textStatus, jqXHR) {
                    that.show_undo_box();
                    that.update_result_box(data, 'success');
                    that.get_food_stats();

                    that.handout_in_progress = false;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    that.hide_undo_box();
                    that.update_result_box(jqXHR.responseText, 'error');

                    that.soundError();
                    that.handout_in_progress = false;
                }
            });
        }
    },

    update_stats: function(data) {
        var description_parts = [];
        var content = '';
        var content_parts = [];
        var total_width = this.stats_container.width();
        jQuery(data).each(function(idx, item) {
            var temp = "<b>" + item.name + "</b>: ";
            var inner_temp = [];
            var temp_count = 0;
            content_parts[idx] = {'name': item.name, 'totalcount': 0, 'counts': []};
            var index = 1;
            for (var m in item.stats) {
                content_parts[idx].counts[index] = item.stats[m];
                temp_count += parseInt(item.stats[m]);
                inner_temp.push("<span class='color" + index++ + "'>" + m + ": " + item.stats[m] + "</span>");
            }
            content_parts[idx].count = temp_count;
            temp += inner_temp.join(', ');
            description_parts.push(temp);
        });
        description = "<p>" + description_parts.join(' - ') + "</p>";

        var len = content_parts.length;
        var total_counts = [0, 0, 0];
        for (var i = 0; i < len; ++i) {
            content += "<div id='stats-boxes'><p style='padding: 0px; margin: 0px;'><strong style='padding: 0px; margin: 0px;'>" + content_parts[i].name + "</strong></p>";
            for (var ii = 1; ii < content_parts[i].counts.length; ++ii) {
                total_counts[ii] += parseInt(content_parts[i].counts[ii]);
                var width = Math.round(content_parts[i].counts[ii] / content_parts[i].count * total_width * 0.95);
                content += "<div style='width: " + width + "px; margin: 0px; padding: 0px;' class='stats-box color" + ii + "'>" + content_parts[i].counts[ii] + "</div>";
            }
            content += "</div><div style='clear: both'></div>";
        }
        var overall_count = total_counts[1] + total_counts[2];
        content += "<div id='stats-boxes'><p style='padding: 0px; margin: 0px;'><strong style='padding: 0px; margin: 0px;'>I alt</strong></p>";
        for (var ii = 1; ii < total_counts.length; ++ii) {
            var width = Math.round(total_counts[ii] / overall_count * total_width * 0.95);
            content += "<div style='width: " + width + "px; margin: 0px; padding: 0px;' class='stats-box color" + ii + "'>" + total_counts[ii] + "</div>";
            }
            content += "</div><div style='clear: both'></div>";

        this.stats_container.html(description + content);
    },

    get_food_stats: function() {
        var that = this;
        jQuery.ajax(this.ajax_uri, {
            type: 'post',
            data: {action: 'get-food-stats', fooditem_ids: that.food_item_ids},
            success: function(data, textStatus, jqXHR) {
                that.update_stats(jQuery.parseJSON(data));
            },
            error: function(jqXHR, textStatus, errorThrown) {
                that.stats_container.html(jqXHR.reponseText);
            }
        });
    },

    undo_last_register: function() {
        if (this.undo_in_progress || this.handout_in_progress) {
            this.soundWait();
            return;
        }

        this.undo_in_progress = true;
        var that = this;
        var to_undo = that.last_user;
        this.hide_undo_box();
        this.input_box.val('').focus();
        this.registration_result.html('<img src="/img/spinner.gif" alt="Spinner">');

        jQuery.ajax(this.ajax_uri, {
            type: 'post',
            data: {action: 'undo-received', user_id: that.last_user, fooditem_ids: that.food_item_ids},
            timeout: 3000,
            success: function(data, textStatus, jqXHR) {
                that.undo_in_progress = false;
                that.update_result_box("Registrering for ID: " + that.last_user + " fjernet", 'success');
                that.get_food_stats();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                that.undo_in_progress = false;
                that.update_result_box("<strong>Fejl:</strong> Kunne ikke fjerne sidste registrering", 'error');

                that.soundError();
            }
        });
    }
}
