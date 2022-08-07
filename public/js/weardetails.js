    /**
     * Copyright (C) 2010  Peter Lind
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
     *
     * PHP version 5
     *
     * @package   Javascript
     * @author    Peter Lind <peter.e.lind@gmail.com>
     * @copyright 2009 Peter Lind
     * @license   http://www.gnu.org/licenses/gpl.html GPL 3
     * @link      http://www.github.com/Fake51/Infosys
     */
(function(){
    var inputs = $('input.wear-hand-out');

    inputs.each(function () {
        $(this).click(function () {
            changeStatus(this);
        });
    });

    var changeStatus = function(elem) {
        var self           = $(elem),
            current_status = self.hasClass('handed-out'),
            status_parts   = elem.id.split('-');

        $.ajax({
            url: '/wear/detailed/ajax/',
            type: 'POST',
            data: {deltager_id: status_parts[0], wearpris_id: status_parts[1]},
            success: function (data) {
                if (current_status) {
                    self.removeClass('handed-out');
                    elem.value = 'Marker udleveret';
                    elem.parentNode.parentNode.cells[4].innerHTML = 'Status: Ikke udleveret';
                } else {
                    self.addClass('handed-out');
                    elem.value = 'Fortryd udlevering';
                    elem.parentNode.parentNode.cells[4].innerHTML = 'Status: Udleveret';
                }
            },
            error: function () {
                alert('Kunne ikke ændre markeringen');
            }
        });
    };
})()
