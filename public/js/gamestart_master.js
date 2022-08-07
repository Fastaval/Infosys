var master = (function($) {
    var master = {
        ajax: null,
        last_update: null,
        error_count: 0,
        schedules: {},
        change_url: null,
        info_url: null,

        init: function(options) {
            this.ajax       = new Ajax();
            this.info_url   = options.info_url;
            this.change_url = options.change_url;

            this.info_interval = window.setInterval(this.handleInfoUpdates, 2000);

            $('div.gamestart-activity').each(function() {
                var schedule = new master.Schedule($(this));

                master.schedules[schedule.getId()] = schedule;
            });

            $('body').on('click', 'img.add-reservist', this.addReservistEventHandler)
                .on('click', 'a.set-status', this.setStatusEventHandler);
        },

        setStatusEventHandler: function(event) {
            var container = $(this).closest('div.gamestart-activity'),
                schedule  = master.schedules[container.data('schedule_id')];

            event.preventDefault();
            event.stopPropagation();

            schedule.toggleStatus();
        },

        addReservistEventHandler: function(event) {
            var container = $(this).closest('div.gamestart-activity'),
                schedule  = master.schedules[container.data('schedule_id')];

            schedule.addReservist();
        },

        Schedule: function(container) {
            this.getContainer = function() {
                return container;
            };
        },

        handleInfoUpdates: function() {
            master.ajax.request(
                master.info_url,
                {last_update: master.last_update},
                master.parseInfoUpdate,
                master.handleInfoError
            );
        },

        parseInfoUpdate: function(data) {
            master.error_count = 0;
            master.last_update = Math.round((+new Date()) / 1000);

            if (data.toString() == '[object Object]') {
                for (var i in data) {
                    if (data.hasOwnProperty(i)) {
                        window.setTimeout((function(i, data) {
                            return function() {
                                master.updateSchedule(i, data[i]);
                            };
                        })(i, data), 0);
                    }
                }
            }
        },

        updateSchedule: function(id, data) {
            var schedules = master.schedules;

            if (schedules[id]) {
                schedules[id].setGMsPresent(data.gms_present)
                    .setStatus(parseInt(data.status, 10))
                    .setSpots('gamer-lacking', data.gamers_lacking)
                    .setSpots('offered-reservist', data.reserves_offered)
                    .setSpots('accepted-reservist', data.reserves_accepted)
                    .setMinion(data.minion);
            }
        },

        handleInfoError: function() {
            master.error_count++;

            if (master.error_count > 3) {
                alert('Forbindelsen til serveren ser ud til at være tabt - prøv at reloade siden');
                window.clearInterval(master.info_interval);
            }
        }
    };

    master.Schedule.prototype.toggleStatus = function() {
        var self = this,
            new_status;

        switch (this._status) {
        case 1:
            new_status = 2;
            break;

        case 2:
        default:
            new_status = 1;
            break;

        }

        master.ajax.request(
            master.change_url,
            {type: 'change-schedule-status', schedule_id: this.getId(), status: new_status},
            function() {
                self.setStatus(new_status);
            }, function() {
                alert('fail');
            },
            true
        );
    };

    master.Schedule.prototype.setStatus = function(status) {
        var container = this.getContainer();

        this._status = status;

        container.find('a.set-status').remove();

        switch (status) {
        case 1:
            classname = 'close-schedule';
            text      = 'Luk spilstart';
            break;

        case 2:
            classname = 'open-schedule';
            text      = 'Åben spilstart';
            break;

        default:
            throw new Error('Unknown status for schedule');
        }

        container.find('h2').after('<a href="javascript:void(0);" class="btn set-status ' + classname + '">' + text + '</a>');

        if (status == 2) {
            container.find('h2').addClass('closed');

        } else {
            container.find('h2').removeClass('closed');
        }

        return this;
    };

    master.Schedule.prototype.addReservist = function() {
        var self = this;

        if (this._status == 2) {
            alert('Afvikling lukket');
            return;
        }

        if (!this._gamers_lacking || this._reserves_offered >= (this._gamers_lacking + this.getAvailableSpots())) {
            return;
        }

        master.ajax.request(
            master.change_url,
            {type: 'add-reservist', schedule_id: this.getId()},
            function() {
                self.setSpots('offered-reservist', parseInt(self._reserves_offered, 10) + 1);
            }, function() {
                alert('fail');
            },
            true
        );
    };

    master.Schedule.prototype.getAvailableSpots = function() {
        var element;

        if (this._available_spots !== undefined) {
            return this._available_spots;
        }

        element = this.getContainer().find('span.spots-available');

        this._available_spots = element.length ? element.data('spots') : 0;
        return this._available_spots;
    };

    master.Schedule.prototype.getGMs = function() {
        if (this._gms) {
            return this._gms;
        }

        this._gms = this.getContainer().find('div.gm-spot');
        return this._gms;
    };

    master.Schedule.prototype.setMinion = function(minion) {
        this.getContainer()
            .find('h2')
                .find('span.minion')
                    .remove()
                .end()
                .append(' <span class="minion">(Minion: ' + minion.user + ')</span>');
    };

    master.Schedule.prototype.setSpots = function(type, amount) {
        var parsed    = parseInt(amount, 10),
            tens      = Math.floor(parsed / 10),
            fives     = Math.floor((parsed % 10) / 5),
            ones      = parsed - (tens * 10 + fives * 5),
            elements  = $('<div></div>'),
            self      = this,
            type_info = this.getTypeInfo(type, parsed),
            i;

        if (!ones && (fives || tens)) {
            if (fives) {
                ones  = 5;
                fives = 0;

            } else {
                tens  -= 1;
                fives += 1;
                ones  += 5;
            }
        }

        window.setTimeout(function() {
            self.getContainer().find('div.' + type_info.selector +', span.' + type_info.selector).remove();

            if (!ones) {
                return;
            }

            for (i = 0; i < tens; i++) {
                elements.append('<div class="' + type_info.classname + '">10' + type_info.denotion + '</div>');
            }

            for (i = 0; i < fives; i++) {
                elements.append('<div class="' + type_info.classname + '">5' + type_info.denotion + '</div>');
            }

            for (i = 0; i < ones; i++) {
                elements.append('<div class="' + type_info.classname + '">1' + type_info.denotion + '</div>');
            }

            elements.children().appendTo(self.getContainer().find('div.places-status ' + type_info.parent_class).append('<span class="divider ' + type_info.classname + '">|</span>'));
        }, 0);

        return this;
    };


    master.Schedule.prototype.getTypeInfo = function(type, amount) {
        var type_info = {};

        switch (type) {
        case 'gamer-lacking':
            this._gamers_lacking   = amount;
            type_info.classname    = 'gamer-spot noshow';
            type_info.denotion     = 'S';
            type_info.parent_class = 'div.gamers';
            break;

        case 'offered-reservist':
            this._reserves_offered = amount;
            type_info.classname    = 'reservist-spot offered';
            type_info.denotion     = 'R';
            type_info.parent_class = 'div.offered';
            break;

        case 'accepted-reservist':
            this._reserves_accepted = amount;
            type_info.classname     = 'reservist-spot accepted';
            type_info.denotion      = 'R';
            type_info.parent_class  = 'div.accepted';
            break;

        default:
            throw new Error('Type not recognized: ' + type);
        }

        type_info.selector = type_info.classname.replace(/ /, '.');

        return type_info;
    };

    master.Schedule.prototype.setGMsPresent = function(gm_count) {
        this.getGMs().slice(0, gm_count).removeClass('noshow').addClass('show');
        this.getGMs().slice(gm_count).addClass('noshow').removeClass('show');

        return this;
    };

    master.Schedule.prototype.getId = function() {
        if (this._id) {
            return this._id;
        }

        this._id = this.getContainer().data('schedule_id');
        return this._id;
    };

    return master;
})(jQuery);
