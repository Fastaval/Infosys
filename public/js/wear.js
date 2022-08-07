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

var wear_object = {
    setup: function(){
        var that = this;

        this.select = $('#category-select');

        $('#wear-priser').on('click', 'input.remove-wearprice', function() {
            that.removePrice($(this));
        }).
            on('click', '#add-wearprice', function() {
                that.addPrice($(this));
            });

        this.sortCategories();
    },

    sortCategories: function() {
        this.select.find('option').detach().sort(function(a, b) {
            return a.text < b.text ? -1 : 1;
        }).appendTo(this.select);
    },

    removePrice: function(self) {
        var select = $('#category-select'),
            row    = self.closest('tr'),
            price  = row.find('input[name="wearprice_price[]"]'),
            name   = row.find('td').eq(0);

        select.append('<option value="' + price.val() + '">' + name.text() + '</option>');
        row.remove();
        this.sortCategories();
    },

    addPrice: function(button){
        var add_row  = button.closest('tr'),
            select   = $('#category-select'),
            selected = select.find('option:selected'),
            price    = $('#category-price'),
            table    = add_row.closest('table');

        if (select.val()) {
            table.find('tbody').append('<tr><td>' + selected.text() + '</td><td><input name="wearpriceid[]" type="hidden" value="0"/><input name="wearprice_category[]" type="hidden" value="' + select.val() + '"/><input name="wearprice_price[]" type="text" value="' + price.val() + '"/><input type="hidden" name="wearprice_wearid[]" value="' + $('#wear-id').val() + '"/></td><td><input type="button" class="remove-wearprice" value="Slet"/></td></tr>');

            selected.remove();

            price.val('');
        }
    }
};

wear_object.setup();
