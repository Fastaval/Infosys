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
        $('#food-details').on('click', 'input.remove-foodtime', function () {
            $(this).closest('table').remove();
        });

        $('#add-foodtime').click(function () {
            that.addDate($(this));
        });
    },

    addDate: function (self) {
        let table = jQuery('<table></table>');
        let tbody = jQuery('<tbody></tbody>');
        table.append(tbody);

        tbody.append(`
        <tr>
            <td><span class='label'>Dato:</span></td>
            <td>
                <input name='foodtime_id[]' value='' type='hidden'/>
                <input type='text' value='' placeholder='YYYY-MM-DD HH:MM:SS' name='foodtime_date[]'/>
            </td>
        </tr>
        <tr>
            <td><span class='label'>Beskrivelse:</span></td>
            <td><input type='text' value='' name='foodtime_desc_da[]'/></td>
        </tr>
        <tr>
            <td><span class='label'>Engelsk:</span></td>
            <td><input type='text' value='' name='foodtime_desc_en[]'/></td>
        </tr>
        <tr>
            <td><input type='button' value='Slet' class='remove-foodtime' /></td>
        </tr>
        `)

        self.before(table);
    },

};

food_object.setup();