/**
 * Copyright (C) 2009-2012 Peter Lind
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
 * @category  Javascript
 * @package   Javascript
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

var activity_object = {
    public_uri: null,

    dnd_obj: {
        start_box: null,
        schedule_id: null,
        current_box: null,
        stylish: null,
        dnd_element: null
    },

    bounding_boxes: [],
    update_groups: [],

    setupHoldlaegning: function(){
        var that = this;
        $('.afviklingsbox').on('click', '.afviklingsbox-group-add', function() {
            that.addGroup($(this));
        }).on('click', '.afviklingsbox-group-slet', function() {
            that.removeGroup($(this));
        });

        var fieldsets = document.getElementsByTagName('fieldset');
        for (var i = 0; i < fieldsets.length; i++)
        {
            if ($(fieldsets[i]).hasClass('afviklingsbox-signups'))
            {
                this.recordBoundingBox(fieldsets[i]);
                var rows = fieldsets[i].getElementsByTagName('tr');
                for (var ii = 0; ii < rows.length; ii++)
                {
                    $(rows[ii]).mousedown(function(e){
                        that.mouseDownRow(e);
                    });

                }
            }
            if ($(fieldsets[i]).hasClass('afviklingsbox-group'))
            {
                this.recordBoundingBox(fieldsets[i]);
                var rows = fieldsets[i].getElementsByTagName('tr');
                for (var ii = 0; ii < rows.length; ii++)
                {
                    $(rows[ii]).mousedown(function(e){
                        that.mouseDownRow(e);
                    });

                }
            }
        }

        $(document.body).mouseup(function(e){
            that.mouseUpHandler(e);
        });

        $(document.body).mousemove( function(e){
            that.trackMouseMove(e);
        });

        // loop over all sign ups, attach drag events
        // loop over sign up boxes and group boxes, attach events
        // set up stuff for general drag and drop
    },

    recordBoundingBox: function(elem){
        var pos = $(elem).cumulativeOffset();
        var height = $(elem).getHeight();
        var width = $(elem).getWidth();
        var schedule_id = ((elem.parentNode.parentNode.tagName == 'FIELDSET') ? $(elem.parentNode.parentNode.parentNode).firstDescendant('input').value : $(elem.parentNode.parentNode).firstDescendant('input').value);
        var the_class = elem.className;
        var obj = {left: pos.left, 'top': pos.top, right: (pos.left + width), bottom: (pos.top + height), 'element': elem, schedule_id: schedule_id, 'the_class': the_class};
        this.bounding_boxes[this.bounding_boxes.length] = obj;
    },

    mouseUpHandler: function(e){
        if (this.dnd_obj.dnd_element) {
            var moved = $(this.dnd_obj.dnd_element).remove();
            moved.style.position = 'static';
            moved.style.border = 'none';
            var can_add = this.checkGroupTypeStatus(moved, this.dnd_obj.current_box);
            var box = ((!can_add) ? this.dnd_obj.start_box : this.dnd_obj.current_box);
            var tab = box.getElementsByTagName('table');
            var tab = tab[0].getElementsByTagName('tbody');
            tab[0].appendChild(moved);
            moved.style.backgroundColor = 'transparent';
            if (this.dnd_obj.current_box && can_add)
            {
                this.dnd_obj.current_box.style.backgroundColor = 'transparent';
                if ($(this.dnd_obj.start_box).hasClass('afviklingsbox-group'))
                {
                    this.removeFromGroup(moved, this.dnd_obj.start_box);
                }
                if ($(this.dnd_obj.current_box).hasClass('afviklingsbox-group'))
                {
                    this.addToGroup(moved, this.dnd_obj.current_box, this.dnd_obj.start_box);
                }
                this.updateBoundingBoxes();
            }
            else if (!can_add && this.dnd_obj.current_box)
            {
                this.dnd_obj.current_box.style.backgroundColor = 'transparent';
            }
        }

        this.dnd_obj.current_box = null;
        this.dnd_obj.start_box = null;
        this.dnd_obj.schedule_id = null;
        this.dnd_obj.dnd_element = null;
    },

    updateBoundingBoxes: function(){
        for (var i = 0; i < this.bounding_boxes.length; i++) {
            if (!this.bounding_boxes[i].element) {
                var new_array = [].concat(this.bounding_boxes.slice(0,i),this.bounding_boxes.slice(i+1));
                this.bounding_boxes = new_array;
                this.updateBoundingBoxes();
                return;
            } else {
                var pos = $(this.bounding_boxes[i].element).cumulativeOffset();
                this.bounding_boxes[i].left = pos.left;
                this.bounding_boxes[i].top = pos.top;
                this.bounding_boxes[i].right = pos.left + $(this.bounding_boxes[i].element).getWidth();
                this.bounding_boxes[i].bottom = pos.top + $(this.bounding_boxes[i].element).getHeight();
            }
        }
    },

    checkGroupTypeStatus: function(moved, box){
        if (box == null)
        {
            return false;
        }
        if ($(box).hasClass('afviklingsbox-signups'))
        {
            return true;
        }
        var spans = $(box).getElementsByTagName('legend');
        var spans = spans[0].getElementsByTagName('span');
        var inputs = moved.getElementsByTagName('input');
        var pladstype = 0;
        for (var i = 0; i < inputs.length; i++)
        {
            if (inputs[i].hasClass('afviklingsbox-pladstype'))
            {
                pladstype = inputs[i].value;
            }
        }
        if (!pladstype)
        {
            return false;
        }
        if ((pladstype == 'S' && $(spans[1]).hasClass('gamer-status-good')) || (pladstype == 'SL' && $(spans[2]).hasClass('gm-status-good')))
        {
            return false;
        }
        else
        {
            return true;
        }
    },

    removeFromGroup: function(row, box){
        this.update_groups[this.update_groups.length] = box;
        var leg = box.getElementsByTagName('legend');
        var hold_id = $(leg[0]).next('input').next('input').value;
        var inputs = row.getElementsByTagName('input');
        var deltager_id = 0;
        for (var i = 0; i < inputs.length; i++)
        {
            if (inputs[i].hasClass('afviklingsbox-deltagerid'))
            {
                deltager_id = inputs[i].value;
            }
        }
        if (deltager_id && hold_id)
        {
            var ajax = new Ajax.Request(this.public_uri + 'hold/ajaxremoveparticipant/' + deltager_id + '/' + hold_id, {
                method: 'get',
                onSuccess: function(transport){
                    try {
                        var result = transport.responseText.evalJSON(true);
                        if (result.status == 'fail') {
                            var tab = box.getElementsByTagName('table');
                            var tab = tab[0].getElementsByTagName('tbody');
                            tab[0].appendChild(row.remove());
                            alert("Kunne ikke fjerne personen fra holdet");
                        } else {
                            activity_object.updateGroupStatus(result);
                        }
                    } catch (e) {
                        var tab = box.getElementsByTagName('table');
                        var tab = tab[0].getElementsByTagName('tbody');
                        tab[0].appendChild(row.remove());
                        alert("Noget gik galt - reload venligst siden");
                    }
                }});
        }
        this.handleSignuppers('show',deltager_id, box, this.getSignupBoxes());
        
    },

    addToGroup: function(row, box, startbox){
        this.update_groups[this.update_groups.length] = box;
        var leg = box.getElementsByTagName('legend');
        var hold_id = $(leg[0]).next('input').next('input').value;
        var inputs = row.getElementsByTagName('input');
        var pladstype = 0;
        var deltager_id = 0;
        for (var i = 0; i < inputs.length; i++)
        {
            if (inputs[i].hasClass('afviklingsbox-pladstype'))
            {
                pladstype = inputs[i].value;
            }
            if (inputs[i].hasClass('afviklingsbox-deltagerid'))
            {
                deltager_id = inputs[i].value;
            }
        }
        if (pladstype && deltager_id && hold_id)
        {
            var ajax = new Ajax.Request(this.public_uri + 'hold/ajaxaddparticipant/' + pladstype + '/' + deltager_id + '/' + hold_id, {
                method: 'get',
                onSuccess: function(transport){
                    try {
                        var result = transport.responseText.evalJSON(true);
                        if (result.status == 'fail') {
                            var tab = startbox.getElementsByTagName('table');
                            var tab = tab[0].getElementsByTagName('tbody');
                            tab[0].appendChild(row.remove());
                            alert("Kunne ikke føje personen til holdet");
                        } else {
                            activity_object.updateGroupStatus(result);
                        }
                    } catch (e) {
                        var tab = startbox.getElementsByTagName('table');
                        var tab = tab[0].getElementsByTagName('tbody');
                        tab[0].appendChild(row.remove());
                        alert("Noget gik galt - reload venligst siden");
                    }
                }
            });
        }
        this.handleSignuppers('hide',deltager_id, box, this.getSignupBoxes());
    },

    handleSignuppers: function(state, deltager_id, box, signup_boxes){
        var current_afv = box.parentNode.parentNode.parentNode.firstDescendant('input').value;
        for (var i = 0; i < signup_boxes.length; i++)
        {
            var afv_id = signup_boxes[i].parentNode.parentNode.firstDescendant('input').value;
            if (afv_id != current_afv)
            {
                if (document.getElementById('afv' + afv_id + '-' + deltager_id))
                {
                    var do_it = false;
                    var inputs = $('afv' + afv_id + '-' + deltager_id).cells[0].childElements();
                    for (var ii = 0; ii < inputs.length ; ii++)
                    {
                        if (inputs[ii].hasClass('afviklingsbox-pladstype') && inputs[ii].value == 'S')
                        {
                            do_it = true;
                        }
                    }

                    if (do_it)
                    {
                        if (state == 'hide')
                        {
                            $('afv' + afv_id + '-' + deltager_id).hide();
                        }
                        else
                        {
                            $('afv' + afv_id + '-' + deltager_id).show();
                        }
                    }
                }
            }
        }

    },

    getSignupBoxes: function(){
        var to_return = [];
        var count = 0;
        for (var i = 0; i < this.bounding_boxes.length; i++)
        {
            if (this.bounding_boxes[i].the_class == 'afviklingsbox-signups')
            {
                to_return[count] = this.bounding_boxes[i].element;
                count++;
            }
        }
        return to_return;
    },

    updateGroupStatus: function(result){
        if (result.status == 'fail')
        {
            alert('Kunne ikke føje/fjerne spilleren til/fra holdet');
        }
        else
        {
            if (this.update_groups[0])
            {
                var box = this.update_groups.shift();
                box.firstDescendant().value = parseInt(result.is_full);
                var leg = box.getElementsByTagName('legend');
                var gamer_status = ((result.gamer_status == 'true') ? 'gamer-status-needs' : 'gamer-status-good');
                var gm_status = ((result.gm_status == 'true') ? 'gm-status-needs' : 'gm-status-good');
                var spans = leg[0].getElementsByTagName('span');
                for (var i = 0; i < spans.length; i++)
                {
                    if ($(spans[i]).hasClass('gamer-status-good') || $(spans[i]).hasClass('gamer-status-needs'))
                    {
                        spans[i].setAttribute('class', gamer_status);
                        spans[i].setAttribute('className', gamer_status);
                    }
                    if ($(spans[i]).hasClass('gm-status-good') || $(spans[i]).hasClass('gm-status-needs'))
                    {
                        spans[i].setAttribute('class', gm_status);
                        spans[i].setAttribute('className', gm_status);
                    }
                }
            }
        }
    },

    trackMouseMove: function(e){
        var elem = e || window.event;
        if (this.dnd_obj.dnd_element)
        {
            this.dnd_obj.dnd_element.style.left = (e.pageX - 80) + 'px';
            this.dnd_obj.dnd_element.style.top = (e.pageY - 10) + 'px';
            this.checkForMouseOver(e.pageX, e.pageY);
        }
    },

    checkForMouseOver: function(x,y){
        var over_box = false;
        for (var i = 0; i < this.bounding_boxes.length; i++) {
            if (x > this.bounding_boxes[i].left && x < this.bounding_boxes[i].right && y > this.bounding_boxes[i].top && y < this.bounding_boxes[i].bottom) {
                if (this.bounding_boxes[i].element != this.dnd_obj.start_box && this.bounding_boxes[i].schedule_id == this.dnd_obj.schedule_id && ($(this.bounding_boxes[i].element).firstDescendant('input').value == 0 || this.bounding_boxes[i].the_class == 'afviklingsbox-signups')) {
                    this.bounding_boxes[i].element.style.backgroundColor = '#f0f0f0';
                    this.dnd_obj.current_box = this.bounding_boxes[i].element;
                    over_box = true;
                }
            }
        }
        if (!over_box) {
            for (var i = 0; i < this.bounding_boxes.length; ++i) {
                this.bounding_boxes[i].element.style.backgroundColor = 'transparent';
            }
            this.dnd_obj.current_box = null;
        } else {
            for (var i = 0; i < this.bounding_boxes.length; ++i) {
                if (this.bounding_boxes[i].element != this.dnd_obj.current_box) {
                    this.bounding_boxes[i].element.style.backgroundColor = 'transparent';
                }
            }
        }
    },

    mouseDownRow: function(e){
        var elem = e || window.event;
        var curtar = e.target || e.srcElement;
		if (curtar.tagName == 'A')
		{
			return;
		}
        while (curtar.tagName != 'TR')
        {
            curtar = curtar.parentNode;
        }
        if (!$(curtar).hasClass('afviklingsbox-deltagerbusy'))
        {
            var targ = e.target || e.srcElement;
            while (targ.tagName != 'FIELDSET')
            {
                targ = targ.parentNode;
            }
            this.dnd_obj.start_box = targ;
            this.dnd_obj.schedule_id = ((targ.parentNode.parentNode.tagName == 'FIELDSET') ? targ.parentNode.parentNode.parentNode.firstDescendant('input').value : targ.parentNode.parentNode.firstDescendant('input').value);
            var moved = $(curtar).remove();
            this.dnd_obj.dnd_element = moved;
            moved.style.position = 'absolute';
            moved.style.left = (e.pageX - 80) + 'px';
            moved.style.top = (e.pageY - 10) + 'px';
            moved.style.border = '1px solid #000000';
            moved.style.backgroundColor = '#ffffff';
            document.body.appendChild(moved);
            Event.stop(e);
        }

    },

    addGroup: function(self){
        var that   = this,
            afv_id = self.next('input').val();

        $.ajax({
            url: this.public_uri + 'hold/ajaxcreategroup/' + afv_id,
            type: 'GET',
            success: function(data) {
                var result = $.parseJSON(data),
                    clone;

                if (result.status == 'work') {
                    clone = $('.afviklingsbox-group.template').clone();
                    // create all html elements

                    clone.find('.show-team').text('Hold ' + result.holdnummer);
                    clone.find('.group-id').val(result.id);
                    self.closest('fieldset').find('.afviklingsbox-groupbox').append(clone);
                    clone.removeClass('hidden').removeClass('template');

                    that.recordBoundingBox(field);
                } else {
                    alert('Kunne ikke oprette hold');
                }
            }
        });
    },
    removeGroup: function(self){
        var hold_id = self.next('input').val();

        if (confirm('Er du sikker på du vil slette dette hold?')) {
            $.ajax({
                url: this.public_uri + 'hold/ajaxdeletegroup/' + hold_id,
                type: 'GET',
                success: function(data) {
                    try {
                        var result = $.parseJSON(data),
                            schedule_box,
                            signup_box;
                        if (result.status == 'work') {
                            schedule_box = self.closest('div.afviklingsbox');
                            console.log(schedule_box);

                            self.closest('fieldset').remove();
                            activity_object.updateBoundingBoxes();
                        } else {
                            alert('Kunne ikke slette hold');
                        }
                    } catch (e) {
                    }
                }
            });
        }
    }
};


$('#popup_creator_link').click(function(e){
    $('#popup_creator').css('display', 'block');
});


$('#popup_creator_close').click(function(e){
    $('#popup_creator').css('display', 'none');
    $('#day').val(0);
    $('#start').val(0);
    $('#end').val(0);
});

