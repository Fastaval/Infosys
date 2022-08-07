    /**
     * Copyright (C) 2009  Peter Lind
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

var food_object = {
    setup: function () {
        var that = this;
        $('#foodtimes').on('click', 'input.remove-foodtime', function () {
            $(this).closest('tr').remove();
        });

        $('#add-foodtime').click(function () {
            that.addDate($(this));
        });
    },

    addDate: function (self) {
        var row = self.closest('tr')[0],
            dato = $('#foodtime').val(),
            cell1 = document.createElement('td'),
            cell2 = document.createElement('td'),
            input1 = document.createElement('input'),
            input2 = document.createElement('input'),
            input5 = document.createElement('input'),
            new_row = document.createElement('tr');

        input1.name = 'foodtime_id[]';
        input1.type = 'hidden';
        input1.value = '0';
        input2.name = 'foodtime_date[]';
        input2.type = 'text';
        input2.value = dato;
        input5.type = 'button';
        input5.value = 'Slet';
        input5.className = 'remove-foodtime';
        cell1.appendChild(input1);
        cell1.appendChild(input2);
        cell2.appendChild(input5);
        new_row.appendChild(cell1);
        new_row.appendChild(cell2);
        row.parentNode.insertBefore(new_row, row);
        $('#foodtime').val('');
    },

};

food_object.setup();
