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

var dragndrop = object(DragNDrop);
dragndrop.containerId = 'pricescontainer';
dragndrop.formId = 'prices_editform';
dragndrop.addButtonId = 'addpricebutton';
dragndrop.newElementHTML = '<div class="box1"><img src="/public/img/drag-icon.gif" alt="flyt" /></div><div class="box2"><input type="hidden" name="PricesId[]" value="0"/><select name="PricesType[]"><option selected="" value="1">Beskrivelse</option><option value="4">Beskrivelse, h&oslash;jrestillet</option><option value="5"><i>Overskrift, italic</i></option><option value="2">Tomt felt</option><option value="3">Linie</option></select></div><div class="box3"><input value="" name="PricesDescription[]"/></div><div class="box4"><input value="" name="PricesPrice[]"/></div><div class="box5"><input type="checkbox" value="0" name="PricesDelete[]"/> Slet?</div>';
dragndrop.newElementBaseName = 'pricerow';
dragndrop.newElementClass = 'moveablerow dragdrop';
dragndrop.init();


