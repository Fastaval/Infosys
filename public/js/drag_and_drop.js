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

// drag and drop script very much inspired/taken from http://www.webreference.com/programming/javascript/mk/column2/

var moving_object = null;
var grid_abs;
var object_org_rel;

var cur_index = null;
var org_index = null;
var gridimages = null;
var gridleft = null;
var gridtop = null;
var gridwidth = null;
var gridheight = null;
var gridboxeswide = null;
var gridboxbasename = null;
var gridtrackername = null;
var gridcontainer = null;

function checkForDeletes(basename)
{

    var counter = 1;
    var deleted = false;
    while (object = document.getElementById(basename + counter))
    {
        if (object.checked == 1)
        {
            deleted = true;
        }
        counter++;
    }

    return deleted;
}

function checkPositions(basename, image_count)
{

    var counter = 1;
    var positions = [];
    while (object = document.getElementById(basename + counter))
    {
        for (i = 0; i < positions.length; i++)
        {
            if (positions[i] == object.value)
            {
                alert ("Der er opst&aring;et et problem med opdatering. Genfrisk venligst siden og pr&oslash;v forfra.");
                return false;
            }
        }
        positions[counter - 1] = object.value;
        counter++;
    }
    if (image_count != (counter - 1))
    {
        alert ("Der er opst&aring;et et problem med opdatering. Genfrisk venligst siden og pr&oslash;v forfra...");
        return false;
    }
    else
    {
        return true;
    }

}

function mouseMove(e)
{

    mouseCoords = getMouseCoords(e);

    if (moving_object != null)
    {
        containerpos = getPosition(document.getElementById(gridcontainer));
        new_x = mouseCoords.x - containerpos.x - 45;
        new_y = mouseCoords.y - containerpos.y - 30;
        moving_object.style.top = new_y + "px";
        moving_object.style.left = new_x + "px";
        var new_position = getPositionInGrid({x:e.pageX, y:e.pageY});
        if (new_position != null && new_position != cur_index)
        {
            updatePositions(cur_index, new_position);
            cur_index = new_position;
        }
    }
}


// event handler for mouseup
function mouseUp(e)
{
    if (moving_object == null)
    {
        return;
    }
    if (getPositionInGrid(getMouseCoords(e)) == null)
    {
        updatePositions(cur_index, org_index);
        moving_object.style.top = object_org_rel.y + "px";
        moving_object.style.left = object_org_rel.x + "px";
    }
    else
    {
        var new_position = getPositionInGrid(getMouseCoords(e));
        positionInGrid(moving_object, new_position);
    }
    moving_object = null;
}

// retrieves the absolute position of an element, relative to the document
function getPosition(object){
	var left = 0;
	var top  = 0;

	while (object.offsetParent){
		left += object.offsetLeft;
		top += object.offsetTop;
		object = object.offsetParent;
	}

	left += object.offsetLeft;
	top  += object.offsetTop;

	return {x:left, y:top};
}

function getPositionInGrid(mouse_coords)
{
    if (moving_object == null)
    {
        return null;
    }
    if ((mouse_coords.x < (gridleft + grid_abs.x)) || (mouse_coords.y < (gridtop + grid_abs.y)))
    {
        return null;
    }
    if (mouse_coords.x > (gridleft + grid_abs.x + (6 * gridwidth)))
    {
        return null;
    }

    var y = Math.floor((mouse_coords.y - (gridtop + grid_abs.y)) / gridheight);
    var x = Math.ceil((mouse_coords.x - (gridleft + grid_abs.x) + 1) / gridwidth);
    var gridcell = (y * gridboxeswide) + x;
    if (gridcell > gridimages)
    {
        return null;
    }
    else
    {
        return gridcell;
    }
    
}

// positions an object within the grid
function positionInGrid(object, position)
{
    var new_y = (Math.floor((position - 1) / gridboxeswide) * gridheight) + gridtop;
    var new_x = (((position - 1) % gridboxeswide) * gridwidth) + gridleft;
    object.style.top = new_y + "px";
    object.style.left = new_x + "px";
    object.childNodes[3].id = gridtrackername + position;
    object.childNodes[3].value = position;
    object.id = gridboxbasename + position;
}

// updates the grid, moving objects around to fit the dragged object
function updatePositions(old_index, new_index)
{
    if (new_index > old_index)
    {
        for (i = (old_index + 1); i <= new_index; i++)
        {
            positionInGrid(document.getElementById(gridboxbasename + i), (i - 1));
        }
    
    }
    else
    {
        for (i = (old_index - 1); i >= new_index; i--)
        {
            positionInGrid(document.getElementById(gridboxbasename + i), i +1);
        }
    }

}


// initialize vars - unnecessary extra step, but I prefer having all the code in one place
function setupGrid(imagecount, gridx, gridy, boxwidth, boxheight, boxeswide, boxname, trackername, maincontainer){
    gridimages = imagecount;
    gridleft = gridx;
    gridtop = gridy;
    gridwidth = boxwidth;
    gridheight = boxheight;
    gridboxeswide = boxeswide;
    gridboxbasename = boxname;
    gridcontainer = maincontainer;
    gridtrackername = trackername;
    grid_abs = getPosition(document.getElementById(maincontainer));
}


var DragNDrop = {

    items: [],
    containerId: '',
    formId: '',
    addButtonId: '',
    newElementHTML: '',
    newElementBaseName: '',

    movingState: false,
    movingObject: null,
    movingStart: null,
    movingObjectNum: null,
    lastPosition: null,

    grid: {
        absX: null,
        absY: null,
        topX: null,
        topY: null,
        bottomX: null,
        bottomY: null,
        columns: null,
        rows: null,
        columnWidth: null,
        rowHeight: null,
        squareWidth: null,
        squareHeight: null,
    },

    // used for resetting things
    backupGrid: null,
    tempGrid: null,

    validatePositions: function(e){
        this.fixPosition();
        var problems = false;

        var check = [];
        for (var i = 0; i < this.items.length; i++)
        {
            var children = $(this.items[i].id).childElements();
            for (var iii = 0; children.length; iii++)
            {
                if (children[iii].name == 'Position[]')
                {
                    var theValue = children[iii].value;
                    break;
                }
            }
            for (var ii = 0; ii < check.length; ii++)
            {
                if (check[ii] == theValue)
                {
                    alert('problem');
                    Event.stop(e);
                    return false;
                }
            }
            check[check.length] = theValue;
        }
    },

    init: function(){
        var children = $(this.containerId).childElements();
        var ii = 0;
        for (var i = 0; i < children.length; i++)
        {
            if (children[i].hasClassName('dragdrop'))
            {
                var temp = {};
                temp.id = children[i].id;
                this.items[ii] = temp;
                ii++;
            }
        }

        this.determineGrid();
        this.backupGrid = this.grid;

        var that = this;

        $(this.addButtonId).observe('click', function(e){
            var newdiv = document.createElement('div');
            var newdivindex = that.items.length + 1;
            var newdivid = that.newElementBaseName + newdivindex;
            that.items[newdivindex - 1] = {id: newdivid};
            var newdivtopy = (Math.floor((newdivindex - 1) / that.grid.columns) * that.grid.squareHeight) + that.grid.topY;
            var newdivtopx = (Math.floor((newdivindex - 1) % that.grid.columns) * that.grid.squareWidth) + that.grid.topX;
            that.items[newdivindex - 1].topY = newdivtopy;
            that.items[newdivindex - 1].topX = newdivtopx;
            newdiv.setAttribute('id', that.newElementBaseName + newdivindex);
            newdiv.setAttribute('class', that.newElementClass);
            newdiv.innerHTML = that.newElementHTML;
            newdiv.setAttribute('style', 'top: ' + newdivtopy + 'px; left: ' + newdivtopx + 'px;');
            if (that.grid.bottomY <= newdivtopy)
            {
                newheight = $(that.containerId).getHeight() + that.grid.squareHeight;
                $(that.containerId).style.height = newheight + 'px';
                that.grid.rows++;
                that.grid.bottomY = newdivtopy + that.grid.rowHeight;
            }
            $(that.containerId).appendChild(newdiv);
            $(newdivid).observe('mousedown', function(e){
                that.mouseDownHandler(e);
            });
        });

        this.fixPosition();

        // mousedown handlers
        for (var i = 0; i < this.items.length; i++)
        {
            $(this.items[i].id).observe('mousedown', function(e){
                that.mouseDownHandler(e);
            });
        }

        // mouseup handler
        Element.extend(document.body).observe('mouseup', function(e){
            if (that.movingState == true)
            {
                that.settleMovingObject();
                that.movingState = false;
                that.movingObject = null;
                that.tempGrid = null;
                that.movingObjectNum = null;
            }
        });

        //mousemove handler
        Element.extend(document.body).observe('mousemove', function(e){
            if (that.movingState == true)
            {
                var coords = that.getMouseCoords(e);
                $(that.movingObject).style.top = ((coords.y - that.movingStart.y) + that.items[that.movingObjectNum].topY) + 'px';
                $(that.movingObject).style.left = ((coords.x - that.movingStart.x) + that.items[that.movingObjectNum].topX) + 'px';
                var posLast = that.getGridPosition(that.lastPosition);
                var posNow = that.getGridPosition(coords);
                if (posNow != false && posNow != posLast)
                {
                    that.updateGridPositions(posNow, posLast);
                    that.lastPosition = coords;
                }
            }
        });

        $(this.formId).observe('submit',function(e){
            that.validatePositions(e);
        });

    },

    mouseDownHandler: function(e){
        if (e.target.tagName == 'INPUT' || e.target.tagName == 'SELECT' || e.target.tagName == 'OPTION')
        {
            return;
        }

        this.movingState = true;
        this.movingObject = e.currentTarget.id;
        this.tempGrid = this.grid;
        this.movingStart = this.getMouseCoords(e);
        this.lastPosition = this.movingStart;
        for (var i = 0; i < this.items.length; i++)
        {
            if (this.items[i].id == e.currentTarget.id)
            {
                this.movingObjectNum = i;
                break;
            }
        }
    },

    settleMovingObject: function(){
        var position = this.getGridPosition(this.lastPosition);
        var newX = ((position - 1) % this.grid.columns) * this.grid.columnWidth + this.grid.topX;
        var newY = Math.floor((position - 1) / this.grid.columns) * this.grid.rowHeight + this.grid.topY;
        this.items[this.movingObjectNum].topX = newX;
        this.items[this.movingObjectNum].topY = newY;
        $(this.items[this.movingObjectNum].id).style.left = newX + 'px';
        $(this.items[this.movingObjectNum].id).style.top = newY + 'px';
        this.setPosition($(this.items[this.movingObjectNum].id));
    },

    getGridPosition: function(coords){
        if (coords.x < this.grid.absX || coords.y < this.grid.absY)
        {
            return false;
        }
        
        var relX = coords.x - this.grid.absX;
        var relY = coords.y - this.grid.absY;
        if (relX > this.grid.bottomX || relY > this.grid.bottomY)
        {
            return false;
        }

        var position = Math.floor((relX - this.grid.topX) / this.grid.columnWidth) + (Math.floor((relY - this.grid.topY) / this.grid.rowHeight) * this.grid.columns) + 1;

        if (position > this.items.length)
        {
            position = this.items.length;
        }

        return position;
    },

    updateGridPositions: function(posNow, posLast){
        if (posNow > posLast)
        {
            var newPos = -1;
        }
        else
        {
            var newPos = 1;
        }

        for (var i = 0; i < this.items.length; i++)
        {
            if (this.movingObject == this.items[i].id)
            {
                continue;
            }
            var itemPos = this.getGridPosition({x: this.items[i].topX + this.grid.absX, y: this.items[i].topY + this.grid.absY});
            if ((posNow > posLast && itemPos <= posNow && itemPos > posLast) || (posNow < posLast && itemPos >= posNow && itemPos < posLast))
            {
                itemPos += newPos;
                var newX = ((itemPos - 1) % this.grid.columns) * this.grid.columnWidth + this.grid.topX;
                var newY = Math.floor((itemPos - 1) / this.grid.columns) * this.grid.rowHeight + this.grid.topY;
                this.items[i].topX = newX;
                this.items[i].topY = newY;
                $(this.items[i].id).style.left = newX + 'px';
                $(this.items[i].id).style.top = newY + 'px';
            }
        }
        this.fixPosition();
    },

    determineGrid: function(){
        this.grid.topX = this.grid.topY = this.grid.bottomX = this.grid.bottomY = this.grid.columns = this.grid.rows = this.grid.absX = this.grid.absY = 0;

        var absPos = $(this.containerId).cumulativeOffset();
        this.grid.absX = absPos[0];
        this.grid.absY = absPos[1];

        for (var i = 0; i < this.items.length; i++)
        {
            var topleft = $(this.items[i].id).positionedOffset();
            var bottomright = [topleft[0] + $(this.items[i].id).getWidth(), topleft[1] + $(this.items[i].id).getHeight()];
            if (this.grid.columns == 0 && this.grid.rows == 0)
            {
                this.grid.topX = topleft[0];
                this.grid.topY = topleft[1];
                this.grid.bottomX = bottomright[0];
                this.grid.bottomY = bottomright[1];
                this.grid.columns++;
                this.grid.rows++;
                this.grid.squareWidth = bottomright[0] - topleft[0];
                this.grid.squareHeight = bottomright[1] - topleft[1];
                this.items[i].topX = topleft[0];
                this.items[i].topY = topleft[1];
                continue;
            }
            if (topleft[0] < this.grid.topX)
            {
                this.grid.topX = topleft[0];
                this.grid.columns++;
            }
            if (topleft[1] < this.grid.topY)
            {
                this.grid.topX = topleft[1];
                this.grid.rows++;
            }
            if (bottomright[0] > this.grid.bottomX)
            {
                this.grid.bottomX = bottomright[0];
                this.grid.columns++;
            }
            if (bottomright[1] > this.grid.bottomY)
            {
                this.grid.bottomY = bottomright[1];
                this.grid.rows++;
            }

            this.items[i].topX = topleft[0];
            this.items[i].topY = topleft[1];
        }
        this.grid.rowHeight = Math.floor((this.grid.bottomY - this.grid.topY) / this.grid.rows);
        this.grid.columnWidth = Math.floor((this.grid.bottomX - this.grid.topX) / this.grid.columns);
    },

    // sets an hidden input for a dragable
    // element to the current position of the element
    fixPosition: function(){
        for (var i = 0; i < this.items.length; i++)
        {
            if (this.items[i].id == this.movingObject)
            {
                continue;
            }
            var element = $(this.items[i].id);
            this.setPosition(element);
        }
    },

    setPosition: function(element){
        var offset = element.positionedOffset();
        var position = Math.floor((offset[0] - this.grid.topX) / this.grid.columnWidth) + Math.floor((offset[1] - this.grid.topY) / this.grid.rowHeight) * this.grid.columns + 1;

        var children = element.childElements();
        var found = false;
        for (var ii = 0; ii < children.length; ii++)
        {
            if (children[ii].name == 'Position[]')
            {
                children[ii].value = position;
                found = true;
                break;
            }
        }

        if (!found)
        {
            var newinput = document.createElement('input');
            newinput.setAttribute('type', 'hidden');
            newinput.setAttribute('name', 'Position[]');
            newinput.value = position;
            element.appendChild(newinput);
        }
    },

    // get x and y of mouse, relative to document
    getMouseCoords: function(e){
        if (!e)
        {
            var e = window.event;
        }
        if (e.pageX || e.pageY)
        {
            var left = e.pageX;
            var top = e.pageY;
        }
        else
        {
		    var left = e.clientX + document.body.scrollLeft - document.body.clientLeft;
		    var top = e.clientY + document.body.scrollTop - document.body.clientTop;
        }

        return {x:left, y:top};

    },


};



