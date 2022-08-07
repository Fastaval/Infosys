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

function adminObserve (id)
{
    $(id).observe('click', function(e)
    {
        var current_loc = new String(window.location);
        window.location = '/update/modify/' + id;
    });

    $(id).observe('mouseover', function(e)
    {
        $(id).style.border = '2px solid #ffff44';
        $(id).style.margin = '-2px 0px 0px -2px';
    });

    $(id).observe('mouseout', function(e)
    {
        $(id).style.border = 'none';
        $(id).style.margin = '0px';
    });
   
}


