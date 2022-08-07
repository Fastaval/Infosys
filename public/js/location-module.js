/*global jQuery */

(function ($, global, Promise) {
    'use strict';
    var hideMap = function () {
            getMapOverlay().then(function ($map) {
                $map.hide();
            });
        },
        getMapOverlay = function () {
            var p = new Promise(function (resolve, reject) {
                    $.ajax({
                        url: '/img/location.svg',
                        success: function (data) {
                            resolve(makeHtml(data));
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR);
                            reject();
                        }
                    });
                }),
                makeHtml = function (docFragment) {
                    var $overlay = $('<div id="location-overlay"><div class="inner"></div></div>'),
                        node     = docFragment.documentElement;

                    $('body').append($overlay);

                    $overlay.css({
                        height: '100%',
                        width: '100%',
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        'z-index': 10000,
                        display: 'none'
                    });

                    $overlay.children().css({
                        height: '90%',
                        width: '90%',
                        background: 'rgba(0, 0, 0, 0.5)',
                        margin: '2%'
                    });

                    $overlay.click(hideMap);

                    $('body').on('keyup', function (event) {
                        if (event.keyCode === 27) {
                            hideMap();
                        }
                    });

                    getMapOverlay = function () {
                        return p;
                    };

                    document.importNode(node);
                    $overlay.children().append(node);

                    return $overlay;
                };

            return p;
        },
        showMap = function ($map) {
            $map.show();

            return $map;
        },
        highlight = function ($map) {
            $map.find('#rooms').children().hide();
            $map.find('#R' + this).show();
        },
        showLocation = function () {
            var locationId = this.parentNode.getAttribute('data-location-id');

            if (String(locationId).length) {
                getMapOverlay()
                    .then(showMap)
                    .then(highlight.bind(locationId));
            }
        },
        init = function () {
            $('head').append('<link href="/css/location-module.css" rel="stylesheet"/>');

            $('.location-link').each(function () {
                $(this).append('<span class="location-link-icon"></span>');
            });

            $('body').on('click.map', '.location-link-icon', showLocation);
        };

    $(init);
}(jQuery, this, Promise));
