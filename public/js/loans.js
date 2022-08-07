/*global jQuery, window */

var Loans = (function ($, window) {
    'use strict';
    var module = {
        elements: {
        },
        templates: {},
        activityData: [],
        gamesBorrowed: [],
        gamesReturned: [],
        participantData: [],
        loanData: [],
        gameOwners: [],
        indexedGames: {},
        gamestats: null,
        $dialog: null,
        noteText: '',
        noteUpdateToken: null,
        gameCreateMatcher: function (game, term) {
            var name    = game.name.toLowerCase(),
                owner   = game.owner.toLowerCase(),
                barcode = game.barcode.toLowerCase(),
                lc_term = term.toLowerCase();

            if (game.id.toString() === lc_term
                || barcode.substr(0, lc_term.length) === lc_term) {
                return true;
            }

            if (owner.indexOf(lc_term) !== -1 || name.indexOf(lc_term) !== -1) {
                return true;
            }

            return false;
        },
        loanDataMatcher: function (game, term) {
            if (module.gamesBorrowed.indexOf(game.id) !== -1 || game.returned) {
                return false;
            }

            return module.gameCreateMatcher(game, term);
        },
        participantDataMatcher: function (participant, term) {
            var name    = participant.name.toLowerCase(),
                barcode = participant.barcode.toLowerCase(),
                lc_term = term.toLowerCase();

            if (participant.id.toString() === lc_term
                || barcode.substr(0, lc_term.length) === lc_term) {
                return true;
            }

            if (name.indexOf(lc_term) !== -1) {
                return true;
            }

            return false;
        },
        nameFormat: function (game) {
            return game.name;
        },
        loanDataFormat: function (game) {
            return 'ID: ' + game.id + ', ' + game.name + ' (ejer: ' + game.owner + ')';
        },
        gameBarcodeFormat: function (game) {
            return game.barcode;
        },
        participantDataFormat: function (participant) {
            return 'ID: ' + participant.id + ', ' + participant.name;
        },
        dataSearch: function (term, response, data, matcher, formatter, postMatching) {
            var results = [],
            length      = data.length,
            i           = 0;

            for (i, length; i < length; i++) {
                if (matcher(data[i], term)) {
                    results.push(formatter(data[i]));
                }
            }

            if (typeof postMatching === 'function') {
                results = postMatching(results);
            }

            response(results);
        },
        returnBorrowedGame: function () {
            var $row   = $(this).closest('li'),
                loanitemId = $row.data('id'),
                index  = module.gamesBorrowed.indexOf(loanitemId),
                updateHandler = function () {
                    var game = module.getGame(loanitemId);

                    game.borrowed = null;

                    module.gamesBorrowed.splice(index, 1);

                    module.elements.$registeredGamesList.find('li[data-id=' + loanitemId + ']')
                        .addClass('available')
                        .removeClass('borrowed');

                    game.log.push({timestamp: module.formatDateTime(new Date()), status: 'returned'});
                    module.updateGameLogHtml(game);

                    $row.addClass('gone');
                    $row.fadeOut();
                };

            if (index === -1) {
                throw new Error('Game not marked borrowed');
            }

            if ($row.hasClass('gone')) {
                return;
            }

            $.ajax({
                url: window.loans_update_url,
                type: 'POST',
                data: {loanitemId: loanitemId, status: 'returned'},
                success: updateHandler,
                error: function () {
                    window.alert('Kunne ikke opdatere status på tingen');
                }
            });
        },
        finishGame: function () {
            var $row   = $(this).closest('li'),
                loanitemId = $row.data('id'),
                updateHandler = function () {
                    var game = module.getGame(loanitemId);

                    module.getGame(loanitemId).returned = true;

                    module.elements.$registeredGamesList.find('li[data-id=' + loanitemId + ']')
                        .removeClass('available')
                        .addClass('returned');

                    game.log.push({timestamp: module.formatDateTime(new Date()), status: 'finished'});
                    module.updateGameLogHtml(game);
                };

            if (!$row.hasClass('available') || !window.confirm('Er du sikker på tingen skal markeres returneret til ejeren?')) {
                return;
            }

            $.ajax({
                url: window.loans_update_url,
                type: 'POST',
                data: {loanitemId: loanitemId, status: 'finished'},
                success: updateHandler,
                error: function () {
                    window.alert('Kunne ikke opdatere status på tingen');
                }
            });
        },
        enableGameEditing: function () {
            var game = module.getGame($(this).data('id'));

            if (!game.returned) {
                $(this).find('div.editing')
                    .addClass('active');
            }
        },
        updateGameDisplay: function (game) {
            var $registeredGame = module.elements.$registeredGamesList.find('li[data-id=' + game.id + ']'),
                $inPlayGame     = module.elements.$inPlayList.find('li[data-id=' + game.id + ']');

            module.listRegisteredGame(game, $registeredGame);

            if ($inPlayGame.length) {
                module.listBorrowedGame(module.getGame(game.id), $inPlayGame.find('span.sub-info span.name').text(), $inPlayGame.find('span.comment').text().replace(/Kommentar: /, ''), $inPlayGame.find('span.time').text(), $inPlayGame);

                module.sortList(module.elements.$inPlayList);
            }

            module.updateGameLogHtml(game);

        },
        updateGame: function () {
            var self = $(this),
                game = module.getGame(self.closest('li').data('id')),
                $editContainer = self.closest('div.editing'),
                handleEditSuccess = function () {;
                    self.closest('div.editing')
                        .removeClass('active'),

                    game.name    = $name.val();
                    game.barcode = $barcode.val();
                    game.owner   = $owner.val();
                    game.comment = $comment.val();

                    $name.data('original', game.name);
                    $barcode.data('original', game.barcode);
                    $owner.data('original', game.owner);
                    $comment.data('original', game.comment);

                    module.updateGameDisplay(game);
                },
                $name = $editContainer.find('input[name="game"]'),
                $barcode = $editContainer.find('input[name="barcode"]'),
                $owner = $editContainer.find('input[name="owner"]'),
                $comment = $editContainer.find('textarea[name="comment"]');

            $.ajax({
                url: window.loans_edit_url,
                type: 'POST',
                data: {name: $name.val(), barcode: $barcode.val(), owner: $owner.val(), comment: $comment.val(), loanitemId: game.id},
                success: handleEditSuccess,
                error: function () {
                    window.alert('Kunne ikke opdatere tingen');
                }
            });
        },
        disableGameEditing: function (e) {
            $(this).closest('div.editing')
                .removeClass('active');

            e.stopPropagation();

            // set original values
        },
        searchBorrowedGames: function () {
            var term      = this.value.toLowerCase(),
                $children = module.elements.$inPlayList.children();

            $children.detach();

            $children.each(function () {
                var self = $(this),
                    game = module.getGame(self.data('id'));

                if (game.name.toLowerCase().indexOf(term) === -1 && game.owner.toLowerCase().indexOf(term) === -1) {
                    self.addClass('not-found');

                } else {
                    self.removeClass('not-found');
                }
            });

            module.elements.$inPlayList.append($children);
        },
        searchRegisteredGames: function () {
            var term      = this.value.toLowerCase(),
                $children = module.elements.$registeredGamesList.children();

            $children.detach();

            $children.each(function () {
                var self = $(this),
                    game = module.getGame(self.data('id'));

                if (game.name.toLowerCase().indexOf(term) === -1 && game.owner.toLowerCase().indexOf(term) === -1) {
                    self.addClass('not-found');

                } else {
                    self.removeClass('not-found');
                }
            });

            module.elements.$registeredGamesList.append($children);
        },
        filterRegisteredGames: function () {
            var self      = $(this),
                $children = module.elements.$registeredGamesList.children(),
                available,
                borrowed,
                returned;

            self.toggleClass('inactive');

            available = !module.elements.$availableFilter.hasClass('inactive');
            borrowed  = !module.elements.$borrowedFilter.hasClass('inactive');
            returned  = !module.elements.$returnedFilter.hasClass('inactive');

            $children.detach();

            $children.each(function () {
                var $item = $(this);

                if ($item.hasClass('available')) {
                    if (available) {
                        $item.removeClass('hidden');

                    } else {
                        $item.addClass('hidden');

                    }

                } else if ($item.hasClass('borrowed')) {
                    if (borrowed) {
                        $item.removeClass('hidden');

                    } else {
                        $item.addClass('hidden');

                    }

                } else if ($item.hasClass('returned')) {
                    if (returned) {
                        $item.removeClass('hidden');

                    } else {
                        $item.addClass('hidden');

                    }
                }
            });

            module.elements.$registeredGamesList.append($children);
        },
        listActivity: function (timestamp, name, attendees) {
            var template         = module.templates.activityTemplate,
                attendeeTemplate = template.replace(/^[\s\S]*<attendee>(.*)<\/attendee>[\s\S]*$/m, '$1'),
                attendeeItems    = '',
                html             = '',
                i                = 0,
                length           = attendees.length;

            for (i, length; i < length; i++) {
                attendeeItems = attendeeItems + attendeeTemplate.replace(/:attendee-name:/g, attendees[i]);
            }

            html = template.replace(/:name:/, name)
                .replace(/<attendee>(.*)<\/attendee>/m, attendeeItems)
                .replace(/:count:/g, length)
                .replace(/:time:/g, timestamp);

            module.elements.$activityList.append(html);
        },
        listBorrowedGame: function (game, participant, comment, time, toReplace) {
            var template         = module.templates.gameInPlayTemplate,
                html             = '',
                participantName  = participant.name || participant.toString();

            html = template.replace(/:game-title:/g, game.name)
                .replace(/:data-id:/g, game.id)
                .replace(/:game-owner:/g, game.owner)
                .replace(/:borrower:/g, participantName)
                .replace(/:time:/g, time);

            if (game.comment) {
                html = html.replace(/:game-comment:/g, game.comment);

            } else {
                html = html.replace(/<!--game-comment-->.*<!--game-comment-->/g, '');
            }

            if (comment) {
                html = html.replace(/:borrowing-comment:/g, comment);

            } else {
                html = html.replace(/<!--borrowing-comment-->.*<!--borrowing-comment-->/g, '');
            }

            if (toReplace) {
                toReplace.replaceWith(html);

            } else {
                module.elements.$inPlayList.append(html);
            }
        },
        makeGameLogLine: function (logData) {
            var template   = module.templates.logLineTemplate,
                textStatus = '',
                participant;

            switch (logData.status) {
            case 'created':
                textStatus = 'Oprettet';
                break;

            case 'borrowed':
                textStatus = 'Udlånt';

                if (logData.data) {
                    if (logData.data.participant_id) {
                        try {
                            participant = module.getParticipant(logData.data.participant_id);

                            textStatus += ': ' + participant.name;

                        } catch (ignored) {
                            textStatus += ': ' + logData.data.participant;
                        }

                    } else if (logData.data.participant) {
                        textStatus += ': ' + logData.data.participant;
                    }
                }

                break;

            case 'returned':
                textStatus = 'Returneret';
                break;

            case 'finished':
                textStatus = 'Tilbageleveret';
                break;

            default:
                textStatus = logData.status;
            }

            return template.replace(/:timestamp:/g, logData.timestamp)
                .replace(/:status:/g, textStatus);
        },
        updateGameLogHtml: function (game) {
            var $gameElement = module.elements.$registeredGamesList.find('li[data-id=' + game.id + ']'),
                $log         = $gameElement.find('span.log'),
                logHtml      = '',
                i            = 0,
                length       = game.log.length;

            for (i, length; i < length; i++) {
                logHtml += module.makeGameLogLine(game.log[i]);
            }

            $log.html(logHtml);
        },
        listRegisteredGame: function (game, toReplace) {
            var template = module.templates.registeredGameTemplate,
                html     = '',
                status   = '';

            if (game.returned) {
                status = 'returned';

            } else if (game.borrowed) {
                status = 'borrowed';

            } else {
                status = 'available';

            }

            html = template.replace(/:title:/g, game.name)
                .replace(/:data-id:/g, game.id)
                .replace(/:owner:/g, game.owner)
                .replace(/:comment:/g, game.comment || '')
                .replace(/:barcode:/g, game.barcode)
                .replace(/:borrowed:/g, game.borrowed_count)
                .replace(/:game-status:/g, status);

            if (toReplace) {
                toReplace.replaceWith(html);

            } else {
                module.elements.$registeredGamesList.append(html);

            }

            module.updateGameLogHtml(game);
        },
        formatDateTime: function (date) {
            var day     = date.getDate(),
                month   = date.getMonth() + 1,
                hour    = date.getHours(),
                minutes = date.getMinutes(),
                seconds = date.getSeconds();

            return date.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day + ' ' + (hour < 10 ? '0' : '') + hour + ':' + (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        },
        getTime: function (timestamp) {
            var date    = timestamp ? new Date(timestamp) : new Date(),
                hours   = date.getHours(),
                minutes = date.getMinutes();

            hours   = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;

            return hours + '.' + minutes;
        },
        getGame: function (loanitemId) {
            var i      = 0,
                length = module.loanData.length;

            for (i, length; i < length; i++) {
                if (module.loanData[i].id === loanitemId) {
                    return module.loanData[i];
                }
            }

            throw new Error('No such game');
        },
        getParticipant: function (participantId) {
            var i      = 0,
                length = module.participantData.length;

            for (i, length; i < length; i++) {
                if (module.participantData[i].id === participantId) {
                    return module.participantData[i];
                }
            }

            throw new Error('No such participant');
        },
        createGame: function () {
            var gameString    = module.elements.$gameToCreate.val(),
                ownerString   = module.elements.$gcOwner.val(),
                barcodeString = module.elements.$gcBarcode.val(),
                commentString = module.elements.$gcComment.val(),
                gameRegistered = function (data) {
                    var game = {
                        id: parseInt(data.id, 10),
                        barcode: barcodeString,
                        name: gameString,
                        owner: ownerString,
                        comment: commentString || '',
                        log: data.log
                    };

                    module.loanData.push(game);
                    module.indexedGames[data.id] = game;

                    module.elements.$gameToCreate.val('');
                    module.elements.$gcOwner.val('');
                    module.elements.$gcBarcode.val('');
                    module.elements.$gcComment.val('');

                    module.elements.$gamesTab.addClass('auto-highlight');
                    window.setTimeout(function () {
                        module.elements.$gamesTab.removeClass('auto-highlight');
                    }, 1000);

                    module.listRegisteredGame(game);

                    module.sortList(module.elements.$registeredGamesList);
                };

            $.ajax({
                url: window.loans_create_url,
                type: 'POST',
                data: {
                    name: gameString,
                    owner: ownerString,
                    barcode: barcodeString,
                    comment: commentString,
                },
                success: gameRegistered,
                error: function () {
                    window.alert('Kunne ikke oprette tingen');
                }
            });
        },
        lendGame: function () {
            var gameString        = module.elements.$gameToBorrow.val(),
                participantString = module.elements.$borrower.val(),
                comment           = module.elements.$borrowingComment.val(),
                updateHandler = function () {
                    var game = module.getGame(loanitemId),
                        $gameItem,
                        $counter;

                    game.borrowed = {comment: comment, name: participantString, timestamp: module.formatDateTime(new Date())};

                    module.elements.$gameToBorrow.val('');
                    module.elements.$borrower.val('');
                    module.elements.$borrowingComment.val('');

                    module.gamesBorrowed.push(loanitemId);

                    $gameItem = module.elements.$registeredGamesList.find('li[data-id=' + loanitemId + ']');

                    $gameItem.removeClass('available')
                        .addClass('borrowed');

                    $counter = $gameItem.find('.borrowed-count');
                    $counter.text(
                        Number($counter.text().replace(/[^0-9]+/g, '')) + 1
                    );

                    module.listBorrowedGame(module.getGame(loanitemId), participantId ? module.getParticipant(participantId) : participantString, comment, module.getTime());

                    module.sortList(module.elements.$inPlayList);

                    game.log.push({timestamp: module.formatDateTime(new Date()), status: 'borrowed', data: {participant_id: participantId, participant: participantString}});
                    module.updateGameLogHtml(game);
                },
                loanitemId,
                participantId;

            loanitemId        = parseInt(gameString.replace(/^ID: (\d+).*$/, "$1"), 10);
            participantId = parseInt(participantString.replace(/^ID: (\d+).*$/, "$1"), 10);

            if (!loanitemId) {
                return false;
            }

            $.ajax({
                url: window.loans_update_url,
                type: 'POST',
                data: {loanitemId: loanitemId, participantId: participantId, participant: participantString, comment: comment, status: 'borrowed'},
                success: updateHandler,
                error: function () {
                    window.alert('Kunne ikke opdatere status på tingen');
                }
            });
        },
        handleUpload: function () {
            var data = {
                    input: $(this).parent().find('textarea').val()
                };

            $.ajax({
                url: window.loans_parse_url,
                type: 'POST',
                data: data,
                success: function (parsed_data) {
                    document.location.reload(true);
                },
                error: function () {
                    window.alert('Kunne ikke parse data');
                }
            });
        },
        handleUploadSpreadsheet: function () {
            if (module.$dialog) {
                module.$dialog.remove();
            }

            module.$dialog = $('<div><p>Copypaste spreadsheet data ind her. Kolonnerne skal have følgende overskrifter for at upload accepteres: Navn, Ejer, Stregkode, Kommentar. <strong>VÆR OPMÆRKSOM PÅ AT AL DATA SLETTES!</strong></p><textarea class="spreadsheet-upload"></textarea><button class="upload">Upload</button></div>');

            module.$dialog.appendTo('body');

            module.$dialog.dialog({
                modal: true,
                width: '70em',
                close: function () {
                    $dialog.remove();
                    $dialog = null;
                }
            });

            module.$dialog.on('click', 'button.upload', module.handleUpload);
        },
        updateNote: function () {
            if (module.noteUpdateToken) {
                window.clearTimeout(module.noteUpdateToken);
            }

            module.noteUpdateToken = window.setTimeout(function () {
                $.ajax({
                    url: window.loans_notes_url,
                    type: 'POST',
                    data: {note: module.elements.$notes.val()},
                    complete: function () {
                        module.noteUpdateToken = null;
                    }
                });
            }, 1000);
        },
        setupEventHooks: function () {
            module.elements.$inPlayList.on('click.remove-game', 'img.return-game', module.returnBorrowedGame);
            module.elements.$actionPane.on('click.lend-game', 'button.lend-game', module.lendGame);
            module.elements.$actionPane.on('click.finish-game', 'button.finish-game', module.finishGame);
            module.elements.$actionPane.on('click.create-game', 'button.create-game', module.createGame);
            module.elements.$actionPane.on('click.filter-games', 'button.filter', module.filterRegisteredGames);
            module.elements.$actionPane.on('keyup.search-games', '#registered-games-search', module.searchRegisteredGames);
            module.elements.$inPlayList.parent().on('keyup.search-games', '#borrowed-games-search', module.searchBorrowedGames);
            module.elements.$actionPane.on('click.edit-game', 'li.registered-game', module.enableGameEditing);
            module.elements.$actionPane.on('click.update-game', 'button.update-game', module.updateGame);
            module.elements.$actionPane.on('click.cancel-edit-game', 'a.cancel-editing', module.disableGameEditing);
            module.elements.$notes.on('keyup.update-note', module.updateNote);
            $('#upload-spreadsheet-data').click(module.handleUploadSpreadsheet);
        },
        setupTabs: function () {
            $('div.action-board').tabs();
        },
        setupAutoComplete: function () {
            var setup = function ($element, source) {
                $element.autocomplete({
                    delay: 300,
                    minLength: 1,
                    source: source
                });
            };

            setup(module.elements.$gameToBorrow, function (request, response) {
                return module.dataSearch(request.term, response, module.loanData, module.loanDataMatcher, module.loanDataFormat);
            });

            setup(module.elements.$gameToCreate, function (request, response) {
                return module.dataSearch(request.term, response, module.loanData, module.gameCreateMatcher, module.nameFormat, function (results) {
                    var intermediate = results.reduce(function (agg, next) {
                        if (agg.indexOf(next) === -1) {
                            agg.push(next);
                        }

                        return agg;
                    }, []);

                    return intermediate.sort();
                });
            });

            setup(module.elements.$borrower, function (request, response) {
                return module.dataSearch(request.term, response, module.participantData, module.participantDataMatcher, module.participantDataFormat);
            });

            setup(module.elements.$gcOwner, module.gameOwners);

            setup(module.elements.$gcBarcode, function (request, response) {
                return module.dataSearch(request.term, response, module.loanData, module.loanDataMatcher, module.gameBarcodeFormat);
            });
        },
        setupElements: function () {
            return new Promise(function (resolve) {
                module.elements.$borrower               = $('#borrower');
                module.elements.$gameToBorrow           = $('#game-to-lend');
                module.elements.$gameToCreate           = $('#gc-name');
                module.elements.$gcOwner                = $('#gc-owner');
                module.elements.$gcBarcode              = $('#gc-barcode');
                module.elements.$gcComment              = $('#gc-comment');
                module.elements.$borrowingComment       = $('#borrowing-comment');
                module.elements.$inPlayList             = $('div.in-play-list ul');
                module.elements.$actionPane             = $('div.action-pane');
                module.elements.$activityList           = $('div.activity-list ul');
                module.elements.$gamesTab               = $('div.action-board a[href="#games"]').parent();
                module.elements.$registeredGamesList    = $('ul.registered-games');
                module.elements.$notes                  = $('div.notes textarea');
                module.elements.$statistics             = $('#statistics');
                module.templates.gameInPlayTemplate     = $('#in-play-game-template').text();
                module.templates.logLineTemplate        = $('#log-line-template').text();
                module.templates.activityTemplate       = $('#activity-template').text();
                module.templates.registeredGameTemplate = $('#registered-game-template').text();

                // filter buttons
                module.elements.$availableFilter        = $('button[data-status=available]');
                module.elements.$borrowedFilter         = $('button[data-status=borrowed]');
                module.elements.$returnedFilter         = $('button[data-status=returned]');

                resolve();
            });
        },
        setupData: function (data) {
            var parseLoanData = function (loanData) {
                    var length   = loanData.length,
                        index    = 0,
                        parsed   = [],
                        names    = {},
                        owners   = [],
                        returned = [],
                        owner,
                        temp;

                    for (index, length; index < length; index++) {
                        temp = loanData[index];

                        if (!temp.id || !temp.name || !temp.owner) {
                            return false;
                        }

                        temp = $.extend({barcode: ''}, temp);

                        parsed.push(temp);
                        names[temp.owner] = true;

                        if (temp.returned) {
                            returned.push(temp.id);
                        }

                        module.indexedGames[temp.id] = temp;
                    }

                    for (owner in names) {
                        if (names.hasOwnProperty(owner)) {
                            owners.push(owner);
                        }
                    }

                    module.loanData      = parsed;
                    module.gameOwners    = owners;
                    module.gamesReturned = returned;

                    return true;
                },
                parseParticipantData = function (participantData) {
                    var length = participantData.length,
                        index  = 0,
                        parsed = [],
                        temp;

                    for (index, length; index < length; index++) {
                        temp = participantData[index];

                        if (!temp.id || !temp.name || !temp.barcode) {
                            return false;
                        }

                        parsed.push($.extend({}, temp));
                    }

                    module.participantData = parsed;

                    return true;
                },
                parseActivityData = function (activityData) {
                    var length = activityData.length,
                        index  = 0,
                        parsed = [],
                        temp;

                    for (index, length; index < length; index++) {
                        temp = activityData[index];

                        if (!temp.name || !temp.timestamp || !temp.attendees) {
                            return false;
                        }

                        parsed.push($.extend({}, temp));
                    }

                    module.activityData = parsed;

                    return true;
                },
                setupDataAsync = function (resolve, reject) {
                    if (!data.loanData || !data.participantData) {
                        reject();
                        return;
                    }

                    if (!parseLoanData(data.loanData) || !parseParticipantData(data.participantData) || !parseActivityData(data.activityData)) {
                        reject();
                        return;
                    }

                    module.gamestats = data.stats;

                    resolve();
                };

            module.noteText = data.notes || '';

            return new Promise(setupDataAsync);
        },
        getData: function () {
            var getDataAsync = function (resolve, reject) {
                $.ajax({
                    url: window.loans_data_url,
                    type: 'GET',
                    success: resolve,
                    error: reject
                });
            };

            return new Promise(getDataAsync);
        },
        sortList: function ($list) {
            var $children = $list.children().detach();

            $children.sort(
                function (a, b) {
                    return a.getAttribute('data-sort').toLowerCase() < b.getAttribute('data-sort').toLowerCase() ? -1 : 1;
                }
            );

            $list.append($children);
        },
        fillLists: function () {
            var fillInPlay = function () {
                    var length  = module.loanData.length,
                        i       = 0,
                        $parent = module.elements.$inPlayList.parent();

                    module.elements.$inPlayList.detach();

                    for (i, length; i < length; i++) {
                        if (module.loanData[i].borrowed) {
                            module.listBorrowedGame(module.loanData[i], module.loanData[i].borrowed.name, module.loanData[i].borrowed.comment, module.loanData[i].borrowed.timestamp);
                            module.gamesBorrowed.push(module.loanData[i].id);
                        }
                    }

                    module.elements.$inPlayList.appendTo($parent);

                    module.sortList(module.elements.$inPlayList);
                },
                fillRegisteredGames = function () {
                    var length  = module.loanData.length,
                        i       = 0,
                        $parent = module.elements.$registeredGamesList.parent();

                    module.elements.$registeredGamesList.detach();

                    for (i, length; i < length; i++) {
                        module.listRegisteredGame(module.loanData[i]);
                    }

                    module.elements.$registeredGamesList.appendTo($parent);

                    module.sortList(module.elements.$registeredGamesList);
                },
                fillNotes = function () {
                    module.elements.$notes.val(module.noteText);
                },
                fillStats = function () {
                    var $items = [],
                        heading;

                    for (heading in module.gamestats) {
                        if (module.gamestats.hasOwnProperty(heading)) {
                            $items.push($('<dt data-sort="' + heading + '">' + heading + '</dt><dd>' + module.gamestats[heading] + '</dd>'));
                        }
                    }

                    $items.sort(function (a, b) {
                        return a.attr('data-sort') < b.attr('data-sort') ? -1 : 1;
                    });

                    module.elements.$statistics.append($items);
                },
                wrapper = function (resolve) {
                    fillInPlay();
                    fillRegisteredGames();

                    fillNotes();

                    fillStats();

                    resolve();
                };

            return new Promise(wrapper);
        },

        init: function () {
            module.getData()
                .then(module.setupData)
                .then(module.setupElements)
                .then(module.fillLists)
                .then(function () {
                    module.setupEventHooks();
                    module.setupTabs();
                    module.setupAutoComplete();
                })
                .catch(function (fail) {
                    console.log(fail);
                    alert('Failed');
                });
        }
    };

    return module;
})(jQuery, window);
