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

var todo_js = {
    public_uri: null,
    role: null,

    updateCounters: function(){
        var noterows = $$('tr.note-row');
        $('todo-count').replaceChild(document.createTextNode(noterows.length), $('todo-count').firstChild);
        var done_notes = $$('p.done');
        $('todo-done-count').replaceChild(document.createTextNode(done_notes.length), $('todo-done-count').firstChild);
        $('todo-notdone-count').replaceChild(document.createTextNode(noterows.length - done_notes.length), $('todo-notdone-count').firstChild);
    },

    updateNote: function(e){
        var el = this.getRow(e);
        var note_text = el.cells[3].getElementsByTagName('input')[0].value;
        var note_body = el.cells[3].getElementsByTagName('textarea')[0].value;
        var note_status = ((el.cells[3].getElementsByTagName('input')[1].checked) ? 'ja' : 'nej');
        var note_id = el.cells[0].getElementsByTagName('input')[0].value;
        var that = this;
        var ajax = new Ajax.Request(this.public_uri + 'todo/ajaxupdatenote/' + this.role,{
            method: 'post',
            parameters: {'note': note_text, 'note_body': note_body, 'status': note_status, 'note_id': note_id},
            onSuccess: function(transport){
                var ret = transport.responseText.evalJSON(true);
                that.completeUpdate(el,ret);
            },
            onFailure: function(transport){
                alert('Kunne ikke opdatere note');
                that.regretEdit(e);
            }
        });
    },

    completeUpdate: function(el,ret){
        for (var i = 0; i < 3; i++)
        {
            if (Prototype.Browser.IE)
            {
                el.cells[i].style.display = 'block';
            }
            else
            {
                el.cells[i].style.display = 'table-cell';
            }
        }
        while (el.cells[1])
        {
            $(el.cells[1]).remove();
        }
        var new_title = document.createElement('p');
        var new_body = document.createElement('p');
        new_title.className = ((ret.status =='ja') ? 'done' : 'todo');
        new_body.className = 'note-body';
        new_title.appendChild(document.createTextNode(ret.todo_text));
        new_body.appendChild(document.createTextNode(ret.body_text));
        el.cells[0].replaceChild(new_title, el.cells[0].getElementsByTagName('p')[0]);
        if (ret.body_text == '')
        {
            if (el.cells[0].getElementsByTagName('p').length > 1)
            {
                $(el.cells[0].getElementsByTagName('p')[1]).remove();
            }
        }
        else
        {
            if (el.cells[0].getElementsByTagName('p').length > 1)
            {
                el.cells[0].replaceChild(new_body, el.cells[0].getElementsByTagName('p')[1]);
            }
            else
            {
                el.cells[0].appendChild(new_body);
            }
        }
        el.appendChild(document.createElement('td'));
        el.appendChild(document.createElement('td'));
        el.cells[0].className='note-td';
        el.cells[1].className='todo-time';
        el.cells[2].className='todo-time';
        el.cells[1].appendChild(document.createTextNode(ret.created));
        el.cells[2].appendChild(document.createTextNode(ret.updated));
        el.className = 'note-row';
        this.updateCounters();
    },

    addNote: function(note){
        var row = document.createElement('tr');
        for (var i = 0; i < 3; i++)
        {
            row.appendChild(document.createElement('td'));
        }
        $('add_row').parentNode.insertBefore(row,$('add_row'));
        row.className = 'note-row';
        row.cells[0].className='note-td';
        row.cells[1].className='todo-time';
        row.cells[2].className='todo-time';
        var note_id = document.createElement('input');
        note_id.setAttribute('type','hidden');
        note_id.value = note.id;
        var note_title = document.createElement('p');
        note_title.className = 'todo';
        note_title.appendChild(document.createTextNode(note.todo_text));
        row.cells[0].appendChild(note_id);
        row.cells[0].appendChild(note_title);
        if (note.body_text != '')
        {
            var note_text = document.createElement('p');
            note_text.className = 'note-body';
            note_text.appendChild(document.createTextNode(note.body_text));
            row.cells[0].appendChild(note_text);
        }
        row.cells[1].appendChild(document.createTextNode(note.created));
        row.cells[2].appendChild(document.createTextNode(note.updated));
        this.observeRow(row);
        this.updateCounters();
    },

    setup: function(){
        var that = this;
        var note_rows = $$('.note-row');
        for (var i = 0; i< note_rows.length; i++)
        {
            this.observeRow(note_rows[i]);
        }
        $('new_note_add').observe('click',function(e){
            var theother = that;
            var el = that.getRow(e);
            var note_text = el.cells[0].getElementsByTagName('input')[0].value;
            var note_body_text = el.cells[0].getElementsByTagName('textarea')[0].value;
            var ajax = new Ajax.Request(that.public_uri + 'todo/ajaxaddnote/' + that.role,{
                method: 'post',
                parameters: {note: note_text, note_body: note_body_text},
                onSuccess: function(transport){
                    var ret = transport.responseText.evalJSON(true);
                    theother.addNote(ret);
                    el.cells[0].getElementsByTagName('input')[0].value = '';
                },
                onFailure: function(transport){
                    alert('Kunne ikke tilføje note.');
                }
            });
        });

    },

    observeRow: function(elem){
        var that = this;
        elem.observe('click',function(e){
            var el = that.getRow(e);
            if ($(el).hasClassName('note-row'))
            {
                that.editInPlace(e);
            }
        });
        elem.observe('mouseover',function(e){
            var el = that.getRow(e);
            var the_class = el.cells[0].getElementsByTagName('p')[0].className.substr(0,4);
            el.cells[0].getElementsByTagName('p')[0].className = the_class + '-hover';
        });
        elem.observe('mouseout',function(e){
            var el = that.getRow(e);
            var the_class = el.cells[0].getElementsByTagName('p')[0].className.substr(0,4);
            el.cells[0].getElementsByTagName('p')[0].className = the_class;
        });
    },

    editInPlace: function(e){
        var el = this.getRow(e);
        var that = this;
        for (var i = 0; i < el.cells.length; i++)
        {
            el.cells[i].style.display = 'none';
        }
        var text_nodes = el.cells[0].getElementsByTagName('p');
        var new_note = document.createElement('input');
        new_note.type = 'text';
        new_note.className = 'edit-note';
        new_note.value = text_nodes[0].innerText || text_nodes[0].textContent;
        new_note.style.width = '500px';
        new_note.style.marginRight = '15px';
        var text_area = document.createElement('textarea');
        text_area.style.width = '500px';
        text_area.setAttribute('rows',5);
        text_area.className = 'note-body';
        text_area.style.marginRight = '15px';
        if (text_nodes[1])
        {
            text_area.value = text_nodes[1].innerText || text_nodes[1].textContent;
        }
        var add_button = document.createElement('input');
        add_button.type='button';
        add_button.className = 'edit-note-button';
        add_button.value = 'Rediger';
        $(add_button).observe('click',function(e){
            that.updateNote(e);
        });
        var oops_button = document.createElement('input');
        oops_button.type='button';
        oops_button.className = 'oops-button';
        oops_button.value = 'Fortryd';
        $(oops_button).observe('click',function(e){
            that.regretEdit(e);
        });
        var status_box = document.createElement('input');
        status_box.type='checkbox';
        status_box.className = 'edit-note-status';
        status_box.checked = (($(el.cells[0]).hasClassName('done') || $(el.cells[0]).hasClassName('done-hover')) ? true : false);
        var status_label = document.createElement('label');
        var status_label_text = document.createTextNode('Done? ');
        status_label.appendChild(status_label_text);
        status_label.appendChild(status_box);
        var td1 = document.createElement('td');
        var td2 = document.createElement('td');
        var td3 = document.createElement('td');
        td1.appendChild(new_note);
        td1.appendChild(status_label);
        td1.appendChild(text_area);
        td2.appendChild(add_button);
        td3.appendChild(oops_button);
        el.appendChild(td1);
        el.appendChild(td2);
        el.appendChild(td3);
        el.className = 'edit-row';
    },

    regretEdit: function(e){
        var el = this.getRow(e);
        for (var i = 0; i < 3; i++)
        {
            if (Prototype.Browser.IE)
            {
                el.cells[i].style.display = 'block';
            }
            else
            {
                el.cells[i].style.display = 'table-cell';
            }
        }
        while (el.cells[3])
        {
            $(el.cells[3]).remove()
        }
        el.className = 'note-row';
        Event.stop(e);
    },

    getRow: function(e){
        var el = e || window.event;
        el = el.currentTarget || el.srcElement;
        while (el.tagName != 'TR')
        {
            el = el.parentNode;
        }
        return el;
    }
};
