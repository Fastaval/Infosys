(function ($) {
    'use strict';

    /**
     * displays choice boxes
     *
     * @return void
     */
    function chooseBox() {
        $('.manageSleeping-settings').hide();

        $(this).parent().find('.manageSleeping-settings').show();
    }

    /**
     * posts the chosen settings to the system
     *
     * @return void
     */
    function sendUpdate() {
        $('.manageSleeping').find('.manageSleeping-chooseBox:checked')
            .parent()
            .find('.manageSleeping-settings')
            .submit();
    }

    /**
     * cancel the update and head back
     *
     * @return void
     */
    function cancelUpdate() {
        window.history.back();
    }

    $('.manageSleeping').on('click.boxChoice', '.manageSleeping-chooseBox', chooseBox)
        .on('click.update', '.manageSleeping-update', sendUpdate)
        .on('click.cance', '.manageSleeping-cancel', cancelUpdate);
}(jQuery));
