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

function object(o){
    function F(){}
    F.prototype = o;
    return new F();
}


function CheckPositions(FormName, InputName){
    var CheckArray = new Array();
    var Counter = 0;
    for (i = 0; i < document.getElementById(FormName).length; i++) {
        if (document.getElementById(FormName).elements[i].name == InputName) {
            CheckArray[Counter] = document.getElementById(FormName).elements[i].value;
            Counter++;
        }
    }
    var Hit = true;
    for (i = 0; i < CheckArray.length; i++){
        for (ii = (i+1); ii < CheckArray.length; ii++){
            if (CheckArray[i] == CheckArray[ii]){
                Hit = false;
            }
        }
    }
    if (Hit == false){
        alert("Nogle af positionerne i input-felterne er identiske.");
    }
    return Hit;
}

function NormalisePositions(FormName, InputName){
    var CheckArray = new Array();
    var Counter = 0;
    for (i = 0; i < document.getElementById(FormName).length; i++) {
        if (document.getElementById(FormName).elements[i].name == InputName) {
            CheckArray[Counter] = i;
            Counter++;
        }
    }
    for (i = 0; i < CheckArray.length; i++){
        x = CheckArray[i];
        document.getElementById(FormName).elements[x].value = i + 1;
    }
}
