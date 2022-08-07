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

var sortorder_display = {
    setup: function(){
        var that = this;
        var obj = $('sortorder_select');
        common.buttonExpander('sortorder_switch', 'sortorder_select');
        var children = obj.childElements();
        if (children.length)
        {
           for (var i = 0; i < children.length; i++)
           {
                if ($(children[i]).hasClassName('sort_input'))
                {
                    var anchors = children[i].getElementsByTagName('a');
                    $(anchors[0]).observe('click', function(e){that.moveUp(e)});
                    $(anchors[1]).observe('click', function(e){that.moveDown(e)});
                }
           }

        }

    },

    moveUp: function(e){
        var e = e || window.event;
        var elem = e.currentTarget || e.srcElement.parentNode;
        var elem = $(elem.parentNode);
        if ($(elem).previous('span'))
        {
            var prev = elem.previous('span');
            var old = elem.remove();
            prev.parentNode.insertBefore(old, prev);
        }
        Event.stop(e);
    },

    moveDown: function(e){
        var e = e || window.event;
        var elem = e.currentTarget || e.srcElement.parentNode;
        var elem = $(elem.parentNode);
        if ($(elem).next('span'))
        {
            var next = elem.next('span');
            var old = elem.remove();
            if (next.next('span'))
            {
                var before = next.next('span');
                before.parentNode.insertBefore(old, before);
            }
            else if (next.next())
            {
                var before = next.next();
                before.parentNode.insertBefore(old, before);
            }
            else
            {
                next.parentNode.appendChild(old);
            }
        }
        Event.stop(e);
    }
};

var search_display = {
    switchit: function (){
        var obj = $('deltager-search-box-html');
        if (obj.style.display != 'block'){
            obj.style.display = 'block';
        } else {
            obj.style.display = 'none';
        }
    },

    emptyForm: function(){
        var tables = ['search-participant','search-indgang','search-food'];
        for (var t = 0; t < tables.length; t++)
        {
            var inputs = $(tables[t]).getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++)
            {
                switch (inputs[i].type)
                {
                    case 'text':
                        inputs[i].value = '';
                        break;
                    case 'checkbox':
                        inputs[i].checked = false;
                        break;
                }
            }
            var selects = $(tables[t]).getElementsByTagName('select');
            for (var i = 0; i < selects.length; i++)
            {
                selects[i].selectedIndex = 0;
            }
        }
    }
};

