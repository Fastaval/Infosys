
(function ($) {
    var $list = $('#queue-container'),
        $bits = {},
        template = $('#game-template').text(),
        time  = 2500,
        sortData = function (a, b) {
            return a.titles.da < b.titles.da ? -1 : 1;
        },
        updateGame = function ($gamebit, data) {
            var $circle;

            if (parseInt($gamebit.attr('data-need'), 10) === data.gamers_needed || data.gamers_needed === $gamebit.attr('data-need')) {
                return;
            }

            $gamebit.attr('data-need', data.gamers_needed);

            $gamebit.find('div.count').html('<span class="circle">' + data.gamers_needed + '</span>');

            $circle = $gamebit.find('span.circle');

            $circle.css({'background-color': '#fff', 'color': '#fff'});

            window.setTimeout(function () {
                $circle.animate({'background-color': '#8f8', 'color': '#000'}, 300, 'swing', function () {
                    $circle.animate({'background-color': '#ddd'}, 2000);
                });
            }, 200);
        },
        makeGameObject = function (data) {
            var $gamebit;

            $gamebit = $('<div></div>').append(
                template.replace(/:activity-id:/g, data.id)
                    .replace(/:activity-name-danish:/g, data.titles.da)
                    .replace(/:activity-name-english:/g, data.titles.en)
                    .replace(/:writer:/g, data.author)
                    .replace(/:type:/g, data.type)
                    .replace(/:gamers_needed:/g, data.gamers_needed)
            ).children();

            $bits['id_' + data.id] = $gamebit;

            return $gamebit;
        },
        cleanGameObjects = function (seen_ids) {
            var bit_id;

            for (bit_id in $bits) {
                if ($bits.hasOwnProperty(bit_id)) {
                    if (seen_ids.indexOf(bit_id) === -1) {
                        $bits[bit_id].remove();
                        delete $bits[bit_id];
                    }
                }
            }
        },
        updateGameObjects = function (game_data) {
            var i      = 0,
                length = game_data.length,
                seen   = [],
                $children,
                $baby;

            for (i, length; i < length; i++) {
                if ($bits['id_' + game_data[i].id]) {
                    updateGame($bits['id_' + game_data[i].id], game_data[i]);

                } else {
                    $baby = makeGameObject(game_data[i]);
                    $list.append($baby);

                    $children = $list.children();
                    $children.detach();

                    $children.sort(function (a, b) {
                        return a.getAttribute('data-sort') < b.getAttribute('data-sort') ? -1 : 1;
                    });

                    $list.append($children);

                    $baby.fadeIn();
                }

                seen.push('id_' + game_data[i].id);
            }

            cleanGameObjects(seen);
        },
        updateGameQueue = function (data) {
            updateGameObjects(data.sort(sortData));
        },
        gameQueueLoop = function () {
            $.ajax({
                url: window.gsq_ajax_url,
                type: 'GET',
                success: updateGameQueue
            });
        },
        interval;

    interval = window.setInterval(gameQueueLoop, time);
})(jQuery);
