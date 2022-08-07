(function() {
    var query_input = jQuery('#search');
    var result_container = jQuery('#result-container');
    query_input.focus();

    function queryServer() {
        var query = query_input.val();
        if (query == '') {
            alert('Ikke noget ID eller navn at tjekke');
        } else {
            jQuery.ajax({
                url: '/deltager/payment/ajax',
                type: 'get',
                data: {query: query},
                success: function(data) {
                    query_input.val('');
                    var converted = jQuery.parseJSON(data);
                    if (converted.length) {
                        result_container.html('');
                        jQuery(converted).each(function(idx, item) {
                            makeParticipantBox(item);
                        });
                    } else {
                        result_container.html('<p><em>Kunne ikke finde nogen deltagere ud fra de oplysninger</em></p>');
                    }
                }, error: function(XHR) {

                }
            });
        }
    }

    function makeParticipantBox(participant_info) {
        var box = jQuery("<div class='participant" + (participant_info.paid > 0 ? ' paid' : '') + "'><p>ID: " + participant_info.id + ", navn: " + participant_info.name + "</p><p><label>Beløb:</label> <input type='text' class='amount' value='" + participant_info.paid + "'/></p><p><label>Note:</label> <textarea>" + participant_info.paid_note + "</textarea></p><input type='button' value='Opdater' class='update'/><input type='hidden' class='participant-id' value='" + participant_info.id + "'/></div>");
        result_container.append(box);
        addUpdateListeners(box);
    }

    function addUpdateListeners(box) {
        jQuery(box).find('input.amount').keydown(function(event) {
            if (event.keyCode == 13) updateParticipant(this);
        });
        jQuery(box).find('input.update').click(function() {
            updateParticipant(this);
        });
    }

    function updateParticipant(element) {
        var div = jQuery(element).closest('div');
        var amount = div.find('input.amount').val();
        var note = div.find('textarea').val();
        var id = div.find('input.participant-id').val();
        query_input.focus();
        if (amount) {
            jQuery.ajax({
                url: '/deltager/payment/ajax',
                type: 'post',
                data: {participant: id, amount: amount, note: note},
                success: function() {
                    div.addClass('paid');
                }, error: function(XHR) {
                    alert("Kunne ikke opdatere deltagerens betaling");
                }
            });
        } else {
            alert("Der er ikke indtastet noget beløb for deltageren");
        }
    }

    query_input.keydown(function(event) {
        if (event.keyCode == 13) {
            queryServer();
        }
    });
    jQuery('div#input-container input[type=button]').click(function() {
        queryServer();
    });
})();
