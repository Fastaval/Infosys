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

var users_object = {
    public_uri: null,
    roles: null,

    setup: function() {
        var that = this;

        function getUserId(self) {
            var row = self.closest('tr');

            return row.find('td.user-id').text();
        }

        function makeRoleStructure(role_id) {
            var template = $('.role-template').html(),
                element;

            if (!that.roles[role_id]) {
                return '';
            }

            element = $(template.replace(/role-id-placeholder/, role_id).replace(/role-name-placeholder/, that.roles[role_id]));
            return element;
        }

        $('#users-table').on('click', '.action_removerole', function() {
            var self    = $(this),
                user_id = getUserId(self),
                role_id = self.find('input').val();

            if (confirm('Er du sikker på du vil fjerne denne rolle?')) {
                $.ajax({
                    url: that.public_uri + 'admin/ajax/removerole/' + user_id + '/' + role_id,
                    type: 'get',
                    success: function(data) {
                        if (data == 'worked') {
                            self.closest('span').remove();
                        } else {
                            alert('Kunne ikke fjerne rollen fra brugeren');
                        }
                    },
                    error: function(jqXHR) {
                        alert('Kunne ikke fjerne rollen fra brugeren');
                    }
                });
            }
        }).on('click', '.action_addrole', function() {
            var self = $(this),
                user_id       = getUserId(self),
                row           = self.closest('tr'),
                role_td       = row.find('.roles'),
                role_id       = row.find('.role_select').val(),
                role_selected = row.find('.role_select option:selected');

            $.ajax({
                url: that.public_uri + 'admin/ajax/addrole/' + user_id + '/' + role_id,
                type: 'get',
                success: function(data) {
                    if (data == 'worked') {
                        role_td.append(makeRoleStructure(role_id));
                        role_selected.remove();
                    } else {
                        alert('Kunne ikke tilføje rollen til brugeren');
                    }
                },
                error: function() {
                    alert('Kunne ikke tilføje rollen til brugeren');
                }
            });

        }).on('click', '.action_disable', function() {
            var self = $(this),
                user_id = getUserId(self),
                row     = self.closest('tr'),
                action  = self.val().toLowerCase();

            $.ajax({
                url: that.public_uri + 'admin/ajax/' + action + 'user/' + user_id,
                type: 'get',
                success: function(data) {
                    if (data == 'worked') {
                        if (action == 'disable') {
                            self.val('Enable');
                            row.find('.status').text('ja');
                        } else {
                            self.val('Disable');
                            row.find('.status').text('nej');
                        }
                    } else {
                        alert('Kunne ikke ' + action + ' brugeren');
                    }
                },
                error: function() {
                    alert('Kunne ikke ' + action + ' brugeren');
                }
            });
        }).on('click', '.action_delete', function() {
            var self = $(this),
                user_id = getUserId(self),
                row     = self.closest('tr');

            if (confirm('Er du sikker på du vil slette brugeren?')) {
                $.ajax({
                    url: that.public_uri + 'admin/ajax/deleteuser/' + user_id,
                    type: 'get',
                    success: function(data) {
                        if (data == 'worked') {
                            row.remove();
                        } else {
                            alert('Kunne ikke slette brugeren');
                        }
                    },
                    error: function(jqXHR) {
                        alert('Kunne ikke slette brugeren');
                    }
                });
            }
        }).on('click', '.action_changepass', function() {
            var self = $(this),
                user_id = getUserId(self),
                row     = self.closest('tr'),
                input   = row.find('.new_pass');

            if (input.val().length > 5) {
                $.ajax({
                    url: that.public_uri + 'admin/ajax/changepass/' + user_id,
                    type: 'post',
                    data: {'pass': input.val()},
                    success: function(data){
                        if (data != 'worked') {
                            alert('Kunne ikke skift password for brugeren.');
                        }
                    }
                });
                input.val('');
            } else {
                alert('Kan ikke sætte passwords på mindre end 6 chars.');
            }
        });

        $('#action_adduser').click(function() {
            var self     = $(this),
                row      = self.closest('tr'),
                username = $('#user_name').val(),
                password = $('#user_pass').val(),
                role_id  = $('#role_select').val(),
                template = $('.user-template').html(),
                user,
                element;

            if (username && password.length > 5) {
                $.ajax({
                    url: that.public_uri + 'admin/ajax/createuser/',
                    type: 'post',
                    data: {user: username, pass: password, role: role_id ? role_id : 0},
                    success: function(data) {
                        user    = $.parseJSON(data);
                        element = $('<tr>' + template.replace(/user-id-placeholder/, user.id).replace(/user-name-placeholder/, username) + '</tr>');
                        $('#users-table tbody').append(element);

                        if (role_id) {
                            element.find('.roles').append(makeRoleStructure(role_id));
                        }

                    },
                    error: function(jqXHR) {
                        alert('Kunne ikke oprette bruger.');
                    }
                });

            } else {
                alert('Brugernavn eller kode er for kort eller mangler.');
            }
        });
    }
};

var roles_object = {
    public_uri: null,
    privileges: {},
    setup: function() {
        var that = this;

        $('table#roles-table').on(
            'click', 'a.action_deleterole', that.deleteRole
        ).on(
            'click', 'a.action_removepriv', that.removePrivilege
        ).on(
            'click', 'input.action_addpriv', that.addPrivilege
        );

        $('#action_addrole').click(that.createRole);
    },

    createRole: function(e){
        var self = $(this),
            name = $('#role_name'),
            description = $('#role_description');

        e.preventDefault();

        if (name.val() !== '' && description.val() !== '') {

            $.ajax({
                url: roles_object.public_uri + 'admin/ajax/createrole',
                type: 'POST',
                data: {name: name.val(), description: description.val()},
                success: function(transport) {
                    if (transport.substr(0,1) =='{') {
                        roles_object.createRoleCallback($.parseJSON(transport), name, description);
                    } else {
                        alert('Kunne ikke tilføje rollen.');
                    }
                }
            });
        } else {
            alert('Navn eller beskrivelse er ikke udfyldt.');
        }
    },

    createRoleCallback: function(role, name, description){
        var select,
            row;

        row = $('<tr><td><a href="javascript:void(0);" class="action_deleterole"><img src="' + roles_object.public_uri + 'img/remove.png" alt="Remove role"/></a> <span class="role_id">' + role.id + '</span></td><td>' + name.val() + '</td><td>' + description.val() + '</td><td class="privileges"></td><td class="privilege-selector"><input type="button" class="action_addpriv" value="Add privilege"/></td></tr>');

        select = $('<select class="priv_select"></select>');
        for (var m in roles_object.privileges) {
            if (roles_object.privileges.hasOwnProperty(m)) {
                $('<option value="' + m.toString().substr(2) + '">' + roles_object.privileges[m] + '</option>').appendTo(select);
            }
        }

        select.insertBefore(row.find('td.privilege-selector input'));

        $('#roles-table tbody').append(row);

        $('#role_name').val('');
        $('#role_description').val('');
    },

    deleteRole: function(e) {
        var self = $(this),
            row  = self.closest('tr'),
            id   = row.find('span.role_id').text();

        e.preventDefault();

        if (confirm('Er du sikker på du vil slette denne rolle?')) {
            $.ajax({
                url: roles_object.public_uri + 'admin/ajax/deleterole/' + id,
                type: 'GET',
                success: function(transport){
                    if (transport == 'worked') {
                        row.remove();
                    } else {
                        alert('Kunne ikke slette rollen.');
                    }
                }
            });
        }
    },

    addPrivilege: function(e) {
        var self         = $(this),
            row          = self.closest('tr'),
            role_id      = row.find('span.role_id').text(),
            privilege_id = row.find('select').val();

        e.preventDefault();

        $.ajax({
            url: roles_object.public_uri + 'admin/ajax/addprivilege/' + role_id + '/' + privilege_id,
            type: 'GET',
            success: function(transport){
                if (transport == 'worked') {
                    roles_object.addPrivilegeCallback(row, privilege_id);
                } else {
                    alert('Kunne ikke tilføje privilegiet.');
                }
            }
        });

    },

    addPrivilegeCallback: function(row, privilege_id) {
        var privilege_name = row.find('select option:selected').text();

        row.find('td.privileges').append('<span class="priv-id-' + privilege_id + ' span-block"> <a href="javascript:void(0);" class="action_removepriv"> <img src="' + roles_object.public_uri + 'img/remove.png' + '" alt="remove role"/></a>' + privilege_name + '</span>');
    },

    removePrivilege: function(e){
        var self         = $(this),
            span         = self.closest('span'),
            row          = self.closest('tr'),
            privilege_id = span.attr('class').replace(/[^0-9]/g, ''),
            role_id      = row.find('span.role_id').text();

        e.preventDefault();

        if (confirm('Er du sikker på du vil fjerne privilegiet?')) {
            $.ajax({
                url: roles_object.public_uri + 'admin/ajax/removeprivilege/' + role_id + '/' + privilege_id,
                type: 'GET',
                success: function(data) {
                    if (data == 'worked') {
                        span.remove();
                    } else {
                        alert('Kunne ikke fjerne privilegiet');
                    }
                }
            });
        }
    },
};


var privileges_object = {
    public_uri: null,
    setup: function() {
        $('#privileges-table').on('click', 'input.action_delete', this.removePrivilege);

        $('#action_addprivilege').click(this.addPrivilege);
    },

    addPrivilege: function(e) {
        var self = $(this),
            controller = $('#priv_controller'),
            method     = $('#priv_method');

        e.preventDefault();

        if (controller.val() && method.val()) {
            $.ajax({
                url: privileges_object.public_uri + 'admin/ajax/createprivilege',
                type: 'POST',
                data: {controller: controller.val(), method: method.val()},
                success: function(data) {
                    try {
                        privileges_object.addPrivilegeCallback($.parseJSON(data), controller, method);
                    } catch (error) {
                        alert('Kunne ikke tilføje privilegiet.');
                    }

                }
            });
        } else {
            alert('Controller eller method mangler.');
        }
    },

    addPrivilegeCallback: function(priv, controller, method) {
        $('<tr><td class="id">' + priv.id + '</td><td>' + controller.val() + '</td><td>' + method.val() + '</td><td></td><td><input type="button" value="Slet" class="action_delete"/></td></tr>').appendTo('#privileges-table tbody');

        controller.val('');
        method.val('');
    },

    removePrivilege: function(e) {
        var self = $(this),
            row  = self.closest('tr'),
            id   = row.find('td.id').text();

        e.preventDefault();

        if (confirm('Er du sikker på du vil slette dette privilegie?')) {
            $.ajax({
                url: privileges_object.public_uri + 'admin/ajax/deleteprivilege/' + id,
                type: 'GET',
                success: function(data) {
                    if (data =='worked') {
                        row.remove();
                    } else {
                        alert('Kunne ikke slette privilegiet.');
                    }
                }
            });
        }
    }
};
