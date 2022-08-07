var minion = (function($) {
    var minion = {
        gamemasters: {},
        gamers: {},
        gamer_count: 0,
        ajax: null,
        change_url: '',
        info_url: '',
        schedule_id: null,
        last_update: null,
        error_count: 0,
        reserves_container: null,
        updating_reserves: false,
        status: null,

        init: function(options) {
            minion.ajax = new Ajax();

            this.info_interval = window.setInterval(this.handleInfoUpdates, 2000);

            $('div.gms div.gm').each(function(idx) {
                minion.gamemasters[idx] = new minion.GameMaster($(this));
                this.gamemaster         = minion.gamemasters[idx];
            });

            $('div.gamers div.gamer').each(function(idx) {
                minion.gamers[idx] = new minion.Gamer($(this));
                this.gamer         = minion.gamers[idx];
                minion.gamer_count++;
            });

            $('div.gms').on('click', 'div.gm', this.handleGMClick);
            $('div.gamers').on('click', 'div.gamer', this.handleGamerClick);

            this.reserves_container = $('div.reserves');

            this.reserves_container.on('click', 'div.reserve.offered', this.handleReserveClick);

            minion.change_url  = options.change_url;
            minion.info_url    = options.info_url;
            minion.schedule_id = options.schedule_id;
        },

        handleInfoUpdates: function() {
            minion.ajax.request(
                minion.info_url,
                {last_update: minion.last_update, schedule_id: minion.schedule_id},
                minion.parseInfoUpdate,
                minion.handleInfoError
            );
        },

        updateReservists: function(reserves_offered, reserves_accepted) {
            var reserves_container = minion.reserves_container,
                i;

            reserves_container.html('');

            for (i = 1; i <= reserves_accepted; i++) {
                reserves_container.append('<div class="reserve accepted">' + i + '</div>');
            }

            for (i = 1; i <= reserves_offered; i++) {
                reserves_container.append('<div class="reserve offered" data-number="' + i + '">' + i + '</div>');
            }
        },

        parseInfoUpdate: function(data) {
            minion.error_count       = 0;
            minion.last_update       = Math.round((+new Date()) / 1000);
            minion.updating_reserves = false;

            if (data.toString() == '[object Object]') {
                for (var i in data) {
                    if (data.hasOwnProperty(i)) {
                        minion.updateReservists(parseInt(data[i].reserves_offered, 10), parseInt(data[i].reserves_accepted, 10));
                        minion.updateStatus(parseInt(data[i].status));
                    }
                }
            }
        },

        updateStatus: function(status) {
            minion.status = status;

            if (status == 2) {
                $('div.gamestart-app h1').addClass('closed');

            } else {
                $('div.gamestart-app h1').removeClass('closed');
            }
        },

        handleReserveClick: function() {
            var reserve_clicked = $(this);

            if (minion.status !== 1) {
                alert('Afviklingen er lukket');
                return;
            }

            if (minion.updating_reserves) {
                return;
            }

            minion.updating_reserves = true;

            minion.ajax.request(
                minion.change_url,
                {type: 'accepted-reserve', schedule_id: minion.schedule_id, count: reserve_clicked.data('number')},
                function() {
                    var set = reserve_clicked.parent().find('div.offered'),
                        index = set.index(reserve_clicked);

                    set.slice(0, index).add(reserve_clicked).toggleClass('offered updating');

                }, function() {
                    alert('Kunne ikke opdatere');

                    minion.updating_reserves = false;
                },
                true
            );
        },

        handleInfoError: function() {
            master.error_count++;

            if (master.error_count > 3) {
                alert('Forbindelsen til serveren ser ud til at være tabt - prøv at reloade siden');
                window.clearInterval(master.info_interval);
            }
        },

        handleGMClick: function(e) {
            var gamemaster = this.gamemaster;

            if (minion.status !== 1) {
                alert('Afviklingen er lukket');
                return;
            }

            minion.ajax.request(
                minion.change_url,
                {type: 'gamemaster', schedule_id: minion.schedule_id, gamemaster_id: gamemaster.getId(), state: gamemaster.isNoShow() ? 1: 0},
                function() {
                    gamemaster.toggleState();
                }, function() {
                    alert('fail');
                },
                true
            );
        },

        handleGamerClick: function(e) {
            // switch state of element
            // switch on all previous elements
            var gamer = this.gamer,
                prior_elements = gamer.getNumber() - 1,
                total_gamers   = minion.gamer_count - (prior_elements + (gamer.isNoShow() ? 1 : 0));

            if (minion.status !== 1) {
                alert('Afviklingen er lukket');
                return;
            }

            minion.ajax.request(
                minion.change_url,
                {type: 'gamer', schedule_id: minion.schedule_id, gamer_count: total_gamers},
                function() {
                    gamer.toggleState().enablePriorElements();
                }, function() {
                    alert('fail');
                },
                true
            );
        },

        GameMaster: function(base_element) {
            if (!(this instanceof arguments.callee)) {
                return new GameMaster(base_element);
            }

            this.getBaseElement = function() {
                return base_element;
            };
        },

        Gamer: function(base_element) {
            if (!(this instanceof arguments.callee)) {
                return new Gamer(base_element);
            }

            this.getBaseElement = function() {
                return base_element;
            };
        },

        Ajax: function(test_mode, use_success) {
            this.test_mode   = !!test_mode;
            this.use_success = !!use_success;
        },
        
        isNoShow: function() {
            return this.getBaseElement().hasClass('noshow');
        },

        toggleState: function() {
            this.getBaseElement().toggleClass('show noshow');

            return this;
        }
    };

    minion.Ajax.prototype.request = function(url, data, success, failure, is_post) {
        if (this.test_mode) {
            console.log(data);
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

    minion.Gamer.prototype.getNumber = function() {
        if (this.number !== undefined) {
            return this.number;
        }

        this.number = this.getBaseElement().data('number');
        return this.number;
    };

    minion.GameMaster.prototype.getId = function() {
        if (this.id !== undefined) {
            return this.id;
        }

        this.id = this.getBaseElement().data('participant_id');
        return this.id;
    };

    minion.Gamer.prototype.enablePriorElements = function() {
        var gamers = minion.gamers,
            gamer,
            number;

        for (var i in gamers) {
            if (gamers.hasOwnProperty(i)) {
                gamer  = gamers[i];
                number = gamer.getNumber();

                if (number < this.getNumber()) {
                    gamer.getBaseElement().addClass('show').removeClass('noshow');
                } else if (number > this.getNumber()) {
                    gamer.getBaseElement().addClass('noshow').removeClass('show');
                }
            }
        }
    };

    minion.GameMaster.prototype.isNoShow = minion.isNoShow;
    minion.Gamer.prototype.isNoShow      = minion.isNoShow;

    minion.GameMaster.prototype.toggleState = minion.toggleState;
    minion.Gamer.prototype.toggleState      = minion.toggleState;

    return minion;
})(jQuery);
