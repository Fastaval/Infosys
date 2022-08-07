var FVActivity = (function() {
    var FVActivity = {
        participants:          {},
        groups:                {},
        schedules:             null,
        being_dragged:         null,
        group_end_points:      {},
        is_link_click:         false,
        hide_globally_on_busy: true,

        getContainingSchedule: function(element) {
            return element.closest('div.afviklingsbox');
        },

        createGroup: function(ajax, schedule, json) {
            var group_element = $('fieldset.afviklingsbox-group.template').
                    clone().
                    removeClass('template').
                    removeClass('hidden').
                    attr('data-group_id', json.id),
                link = group_element.find('a.show-team'),
                group;

            link.attr('href', link.attr('href').replace(/xxx/, json.id)).
                text('Hold ' + json.holdnummer);

            group_element.appendTo(schedule.find('div.afviklingsbox-groupbox'));

            group = new FVActivity.Group(group_element, ajax);
            FVActivity.groups[group.getId()] = group;
            group.updateStatus(json);
        },

        createGroupHandler: function(e) {
            var ajax     = new Ajax(),
                schedule = FVActivity.getContainingSchedule($(this));

            e.stopPropagation();
            e.preventDefault();

            ajax.request('/hold/ajaxcreategroup/' + schedule.data('schedule_id'),
                {},
                function(json) {
                    FVActivity.createGroup(ajax, schedule, json);

                }, function() {
                    alert('Kunne ikke oprette hold');

                });
        },

        deleteGroup: function(group, json) {
            for (var id in FVActivity.groups) {
                if (FVActivity.groups.hasOwnProperty(id) && id.replace(/^.*-/, '') == json.id) {
                    delete FVActivity.groups[id];
                    group.remove();
                }
            }
        },

        deleteGroupHandler: function(e) {
            var ajax         = new Ajax(),
                group        = $(this).closest('fieldset.afviklingsbox-group'),
                override     = false,
                participants = group.find('tr'),
                callback;

            e.stopPropagation();
            e.preventDefault();

            if (participants.length) {
                if (!(override = confirm('Der er deltagere på holdet! Er du sikker på du vil slette?'))) {
                    return;
                }

                callback = function() {
                    participants.each(function() {
                        var self = this;

                        if (!self.participant) {
                            self.participant = FVActivity.locateParticipantForRow(self);
                        }

                        self.participant.markNotOnTeam().
                            setGroup(null);
                    });
                };
            }

            ajax.request('/hold/ajaxdeletegroup/' + group.data('group_id'),
                {override: override ? 1 : 0},
                function(json) {
                    if (callback) {
                        callback();
                    }

                    FVActivity.deleteGroup(group, json);

                }, function() {
                    alert('Kunne ikke slette hold');

                });
        },

        profileClick: function() {
            FVActivity.is_link_click = true;
        },

        updateGroupStatus: function(json) {
            var groups = {},
                id;

            for (id in FVActivity.groups) {
                if (FVActivity.groups.hasOwnProperty(id)) {
                    groups[id.replace(/^.*-/, '')] = FVActivity.groups[id];
                }
            }

            if (json && json.groups) {
                for (id in json.groups) {
                    if (json.groups.hasOwnProperty(id) && groups[id]) {
                        groups[id].updateStatus(json.groups[id]);
                    }
                }
            }
        },

        startParticipantDrag:  function(e) {
            var self = $(this);
            FVActivity.stopParticipantDrag(e);

            if (self.hasClass('locked') || self.hasClass('deltagerbusy')) {
                return;
            }

            if (FVActivity.is_link_click) {
                FVActivity.is_link_click = false;
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            window.setTimeout(FVActivity.colorSchedules, 50);
            window.setTimeout(FVActivity.setupGroupsForDragging, 50);

            if (!this.participant) {
                this.participant = FVActivity.locateParticipantForRow(this);

            }

            this.participant.startDrag(e, $(this));
        },

        locateGroupFromRow: function(row) {
            var schedule = FVActivity.getContainingSchedule(row),
                groupbox = row.closest('fieldset.afviklingsbox-group'),
                id;

            id = schedule.data('schedule_id') + '-' + groupbox.data('group_id');

            if (FVActivity.groups[id]) {
                return FVActivity.groups[id];
            }

            return null;
        },

        locateParticipantForRow: function(row) {
            var self     = $(row),
                schedule = self.closest('div.afviklingsbox'),
                participant_id;

            if (!(participant_id = self.data('participant_id'))) {
                throw new Error('Participant row without id');
            }

            if (!schedule.length || !schedule.data('schedule_id')) {
                throw new Error('Participant outside of schedule');
            }

            if (!FVActivity.participants[schedule.data('schedule_id') + '-' + participant_id]) {
                throw new Error('No such participant: ' + schedule.data('schedule_id') + '-' + participant_id);
            }

            return FVActivity.participants[schedule.data('schedule_id') + '-' + participant_id];
        },

        colorSchedules: function() {
            if (!FVActivity.being_dragged) {
                FVActivity.schedules.removeClass('inactive');
                return;
            }

            FVActivity.schedules.not(FVActivity.being_dragged.getSchedule()).addClass('inactive');
        },

        getScheduleGroups: function(schedule_id) {
            var result = [];

            for (var m in FVActivity.groups) {
                if (m.indexOf(schedule_id + '-') === 0) {
                    result.push(FVActivity.groups[m]);
                }
            }

            return result;
        },

        setupGroupsForDragging: function() {
            FVActivity.group_end_points = {};

            if (!FVActivity.being_dragged) {
                return;
            }

            for (var i = 0, groups = FVActivity.getScheduleGroups(FVActivity.being_dragged.getScheduleId()), length = groups.length; i < length; ++i) {
                FVActivity.group_end_points[groups[i].getId()] = {
                    group: groups[i],
                    top: groups[i].getYOffset(),
                    left: groups[i].getXOffset(),
                    bottom: groups[i].getYOffset() + groups[i].getHeight(),
                    right: groups[i].getXOffset() + groups[i].getWidth()
                };
            }

        },

        stopParticipantDrag:  function(e) {
            var coords = FVActivity.getEventCoordinates(e);

            window.setTimeout(FVActivity.colorSchedules, 50);

            if (FVActivity.being_dragged) {
                FVActivity.being_dragged.stopDrag(coords);
            }
        },

        checkEndpointHover: function(coords) {
            var hovering = false,
                data;

            if (!FVActivity.being_dragged) {
                return;
            }

            for (var g in FVActivity.group_end_points) {
                if (FVActivity.group_end_points.hasOwnProperty(g)) {
                    data = FVActivity.group_end_points[g];

                    if (data.top <= coords.y && data.bottom >= coords.y && data.left <= coords.x && data.right >= coords.x) {
                        data.group.setHover(true);

                    } else {
                        data.group.setHover(false);
                    }
                }
            }
        },

        moveDrag:  function(e) {
            var coords = FVActivity.getEventCoordinates(e);

            if (!FVActivity.being_dragged) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            FVActivity.being_dragged.moveDrag(coords);
            window.setTimeout(function() {
                FVActivity.checkEndpointHover(coords);
            }, 50);
        },

        Ajax: function(test_mode, use_success) {
            this.test_mode   = !!test_mode;
            this.use_success = !!use_success;
        },

        Participant: function(base_element, ajax) {
            if (!(this instanceof FVActivity.Participant)) {
                return new FVActivity.Participant(base_element);
            }

            this.base_element           = base_element;
            this.ajax                   = ajax;
            base_element[0].participant = this;
        },

        Group: function(base_element, ajax) {
            if (!(this instanceof FVActivity.Group)) {
                return new FVActivity.Group(base_element);
            }

            this.base_element = base_element;
            this.ajax         = ajax;
        },

        getSchedule: function() {
            if (this._schedule) {
                return this._schedule;
            }

            this._schedule = this.base_element.closest('div.afviklingsbox');
            return this._schedule;
        },
        getScheduleId: function() {
            if (this._schedule_id) {
                return this._schedule_id;
            }

            this._schedule_id = this.getSchedule().data('schedule_id');
            return this._schedule_id;
        },

        getEventCoordinates: function(e) {
            var coords = {
                x: 0,
                y: 0
            };

            if (e) {
                if (e.pageX) {
                    coords.x = e.pageX;
                    coords.y = e.pageY;
                } else if (e.screenX) {
                    coords.x = e.screenX;
                    coords.y = e.screenY;
                }
            }

            return coords;
        },

        init: function($, options) {
            var ajax = new Ajax();

            $('table.signup-pool tr').each(function() {
                var participant = new FVActivity.Participant($(this), ajax);
                FVActivity.participants[participant.getId()] = participant;
            });

            $('div.afviklingsbox-groupbox fieldset.afviklingsbox-group').each(function() {
                var group = new FVActivity.Group($(this), ajax);
                FVActivity.groups[group.getId()] = group;
            });

            FVActivity.schedules = $('div.afviklingsbox');

            FVActivity.schedules.on(
                'mousedown', 'a', FVActivity.profileClick
            ).on(
                'mousedown', 'tr.participant', FVActivity.startParticipantDrag
            ).on(
                'click', 'button.add-group', FVActivity.createGroupHandler
            ).on(
                'click', 'button.delete-group', FVActivity.deleteGroupHandler
            );

            $('body').on(
                'mouseup', FVActivity.stopParticipantDrag
            ).on(
                'mousemove', FVActivity.moveDrag
            );

            if (options) {
                if (options.replayable_game !== undefined) {
                    FVActivity.hide_globally_on_busy = !window.replayable_game;
                }
            }
        }
    };

    FVActivity.Ajax.prototype.request = function(url, data, success, failure, is_post) {
        if (this.test_mode) {
            window.setTimeout(this.use_success ? success : failure, 50);
            return;
        }

        $.ajax({
            url:     url,
            type:    is_post ? 'POST' : 'GET',
            data:    data,
            success: success,
            error:   failure
        });
    };

    FVActivity.Participant.prototype.isGamer = function() {
        return this.getRole() === 'S';
    };

    FVActivity.Participant.prototype.isGamemaster = function() {
        return this.getRole() === 'SL';
    };

    FVActivity.Participant.prototype.markOnTeam = function() {
        if (FVActivity.hide_globally_on_busy && !this.isGamemaster()) {
            $('fieldset.afviklingsbox-signups tr.p' + this.getParticipantId()).addClass('on-team');

        } else {
            this.base_element.addClass('on-team');

        }

        return this;
    };

    FVActivity.Participant.prototype.markNotOnTeam = function() {
        if (FVActivity.hide_globally_on_busy && !this.isGamemaster()) {
            $('fieldset.afviklingsbox-signups tr.p' + this.getParticipantId()).removeClass('on-team');

        } else {
            this.base_element.removeClass('on-team');

        }

        return this;
    };

    FVActivity.Participant.prototype.setDragOrigin = function(drag_origin) {
        var group;

        this.drag_origin = drag_origin;

        if (!this.getGroup() && (group = FVActivity.locateGroupFromRow(drag_origin))) {
            this.setGroup(group);
        }

        return this;
    };

    FVActivity.Participant.prototype.setGroup = function(group) {
        this._group = group;
        return this;
    };

    FVActivity.Participant.prototype.getGroup = function() {
        return this._group;
    };

    FVActivity.Participant.prototype.getId = function() {
        if (this._id) {
            return this._id;
        }

        this._id = this.getScheduleId() + '-' + this.getParticipantId();
        return this._id;
    };

    FVActivity.Participant.prototype.getParticipantId = function() {
        if (this._participant_id) {
            return this._participant_id;
        }

        this._participant_id = this.base_element.data('participant_id');
        return this._participant_id;
    };

    FVActivity.Participant.prototype.isBusy = function() {
        return this.base_element.hasClass('deltagerbusy');
    };

    FVActivity.Participant.prototype.beingDragged = function() {
        return this.base_element.hasClass('being-dragged');
    };

    FVActivity.Participant.prototype.getKarma = function() {
        if (this._karma) {
            return this._karma;
        }

        this._karma = this.base_element.data('participant_karma');
        return this._karma;
    };

    FVActivity.Participant.prototype.getRole = function() {
        if (this._role) {
            return this._role;
        }

        this._role = this.base_element.data('participant_role');
        return this._role;
    };

    FVActivity.Participant.prototype.getName = function() {
        if (this._name) {
            return this._name;
        }

        this._name = this.base_element.find('td.name').text();
        return this._name;
    };

    FVActivity.Participant.prototype.getAge = function() {
        if (this._age) {
            return this._age;
        }

        this._age = this.base_element.data('participant_age');
        return this._age;
    };

    FVActivity.Participant.prototype.getGender = function() {
        if (this._gender) {
            return this._gender;
        }

        this._gender = this.base_element.data('participant_gender');
        return this._gender;
    };

    FVActivity.Participant.prototype.getPriority = function() {
        if (this._priority) {
            return this._priority;
        }

        this._priority = this.base_element.data('participant_priority');
        return this._priority;
    };

    FVActivity.Participant.prototype.createDragElement = function(e) {
        var coords = FVActivity.getEventCoordinates(e);

        this.drag_element = $('<div class="drag-box">' + this.getRole() + ': ' + this.getName() + ' - ' + this.getAge() + '/' + this.getGender() + '/' + this.getPriority() + '</div>').
            appendTo('body');
        this.drag_element.sub_y = this.drag_element.height();
        this.drag_element.sub_x = Math.round(this.drag_element.width() / 2);

        this.moveDrag(coords);
    };

    FVActivity.Participant.prototype.moveDrag = function(coords) {
        this.drag_element.css({top: coords.y - this.drag_element.sub_y, left: coords.x - this.drag_element.sub_x});
    };

    FVActivity.Participant.prototype.startDrag = function(e, drag_element) {
        FVActivity.being_dragged = this;

        this.setDragOrigin(drag_element);

        if (this.drag_origin) {
            this.drag_origin.addClass('being-dragged');
        } else {
            this.base_element.addClass('being-dragged');
        }

        this.createDragElement(e);
    };

    FVActivity.Participant.prototype.stopDrag = function(coords) {
        FVActivity.being_dragged = null;

        if (this.drag_origin) {
            this.drag_origin.removeClass('being-dragged');
        } else {
            this.base_element.removeClass('being-dragged');
        }

        this.drag_element.remove();
        this.dropElement(coords);
    };

    FVActivity.Participant.prototype.removeFromGroups = function(callback) {
        var self = this;

        if (this.getGroup()) {
            this.ajax.request('/groups/scheduleparticipant', {
                    participant_id: self.getParticipantId(),
                    from_group: self.getGroup().getGroupId()

                }, function(json) {
                    self.getGroup().base_element.find('tr.p' + self.getParticipantId()).remove();
                    self.markNotOnTeam().
                        setGroup(null);

                    window.setTimeout(function() {
                        FVActivity.updateGroupStatus(json);
                    }, 0);

                    if (json && json.participant && json.participant.id == self.getParticipantId()) {
                        self.updateFromJson(json.participant);
                    }

                    if (callback) {
                        callback();
                    }

                }, function() {
                    self.animateError();

                }, true);
        }
    };

    FVActivity.Participant.prototype.dropElement = function(coords) {
        var end_points = FVActivity.group_end_points,
            remove     = true,
            group;

        for (var group_id in end_points) {
            if (end_points.hasOwnProperty(group_id)) {
                group = end_points[group_id].group;

                if (group.getHover()) {
                    group.setHover(false);

                    if (this.getGroup() && this.getGroup().getId() === group.getId()) {
                        return;
                    }

                    remove = false;

                    group.addParticipant(this);
                    break;
                }
            }
        }

        if (remove) {
            this.removeFromGroups();
        }
    };

    FVActivity.Participant.prototype.animateError = function() {
        var element = this.drag_origin ? this.drag_origin : this.base_element;

        element.css({'background-color': '#f00'}).animate({'background-color': '#fff'}, 700);
    };

    FVActivity.Participant.prototype.updateKarma = function(karma) {
        this._karma = karma;
        this.base_element.find('td.karma').text(karma);

        if (this.getGroup()) {
            this.getGroup().base_element.find('tr.p' + this.getParticipantId() + ' td.karma').text(karma);
        }
    };

    FVActivity.Participant.prototype.updateFromJson = function(json) {
        if (json.karma) {
            this.updateKarma(json.karma);
        }
    };

    FVActivity.Group.prototype.addParticipant = function(participant) {
        var self = this,
            callback = function() {
                self.ajax.request('/groups/scheduleparticipant', {
                    participant_id: participant.getParticipantId(),
                    to_group: self.getGroupId(),
                    gamer: participant.isGamer() ? 'true' : 'false'
                }, function(json) {
                    self.createParticipant(participant);
                    participant.setGroup(self).
                        markOnTeam();

                    if (json && json.participant && json.participant.id == participant.getParticipantId()) {
                        participant.updateFromJson(json.participant);
                    }

                    window.setTimeout(function() {
                        FVActivity.updateGroupStatus(json);
                    }, 0);

                }, function() {
                    participant.animateError();
                }, true);
            };

        if (participant.getGroup()) {
            participant.removeFromGroups(callback);
        } else {
            callback();
        }
    };

    FVActivity.Group.prototype.createParticipant = function(participant) {
        var row = $('<tr class="participant p' + participant.getParticipantId() + '" data-participant_id="' + participant.getParticipantId() + '" data-participant_age="' + participant.getAge() + '" data-participant_role="' + participant.getRole() + '" data-participant_karma="' + participant.getKarma() + '" data-participant_priority="' + participant.getPriority() + '" data-participant_gender="' + participant.getGender() + '"><td>' + participant.getRole() + '</td><td><a href="/deltager/visdeltager/' + participant.getParticipantId() + '">' + participant.getName() + '</a></td><td class="karma">' + participant.getKarma() + '</td><td>' + participant.getAge() + '</td><td>' + participant.getGender() + '</td></tr>'),
            tbody = this.base_element.find('table tbody');

        tbody.
            append(row).
            find('tr').
            sort(function(a, b) {
                if (!a.participant) {
                    a.participant = FVActivity.locateParticipantForRow(a);
                }

                if (!b.participant) {
                    b.participant = FVActivity.locateParticipantForRow(b);
                }

                if (a.participant.isGamemaster()) {
                    return -1;
                }

                if (b.participant.isGamemaster()) {
                    return 1;
                }

                return 0;
            }).
            detach().
            appendTo(tbody);

        row[0].participant = participant;
    };

    FVActivity.Group.prototype.getId = function() {
        if (this._id) {
            return this._id;
        }

        this._id = this.getScheduleId() + '-' + this.getGroupId();
        return this._id;
    };

    FVActivity.Group.prototype.getGroupId = function() {
        if (this._group_id) {
            return this._group_id;
        }

        this._group_id = this.base_element.data('group_id');
        return this._group_id;
    };

    FVActivity.Group.prototype.getYOffset = function() {
        return this.base_element.offset().top;
    };

    FVActivity.Group.prototype.getXOffset = function() {
        return this.base_element.offset().left;
    };

    FVActivity.Group.prototype.getHeight = function() {
        return this.base_element.height();
    };

    FVActivity.Group.prototype.getWidth = function() {
        return this.base_element.width();
    };

    FVActivity.Group.prototype.setHover = function(state) {
        if (this._hover_state === state) {
            return;
        }

        this._hover_state = state;

        if (state) {
            this.base_element.addClass('hovered');
        } else {
            this.base_element.removeClass('hovered');
        }
    };

    FVActivity.Group.prototype.updateStatus = function(json) {
        var gm_span,
            gamer_span;

        if (json) {
            gm_span    = this.base_element.find('span.gm-status');
            gamer_span = this.base_element.find('span.gamer-status');

            if (json.needs_gamemasters) {
                gm_span.removeClass('full').addClass('needs');
            } else {
                gm_span.addClass('full').removeClass('needs');
            }

            if (json.can_use_gamers) {
                if (json.needs_gamers) {
                    gamer_span.addClass('needs').removeClass('open').removeClass('full');
                } else {
                    gamer_span.addClass('open').removeClass('needs').removeClass('full');
                }
            } else {
                gamer_span.addClass('full').removeClass('needs').removeClass('open');
            }
        }
    };

    FVActivity.Group.prototype.getHover = function() {
        return !!this._hover_state;
    };

    FVActivity.Participant.prototype.getScheduleId = FVActivity.getScheduleId;
    FVActivity.Participant.prototype.getSchedule   = FVActivity.getSchedule;
    FVActivity.Group.prototype.getScheduleId       = FVActivity.getScheduleId;
    FVActivity.Group.prototype.getSchedule         = FVActivity.getSchedule;

    return FVActivity;
})();

$('#popup_creator_link').click(function(e){
    $('#popup_creator').css('display', 'block');
});


$('#popup_creator_close').click(function(e){
    $('#popup_creator').css('display', 'none');
    $('#day').val(0);
    $('#start').val(0);
    $('#end').val(0);
});

