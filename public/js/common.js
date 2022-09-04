function object(o){
    function F(){}
    F.prototype = o;
    return new F();
}

if (!Object.create) {
    Object.create = object;
}

var common = {
    buttonExpander: function(button_id, hidden_id) {
        var button = $('#' + button_id),
            hidden = $('#' + hidden_id);
        if (button.length && hidden.length) {
            button.click(function(e) {
                if (!hidden.visible()) {
                    hidden.show();
                } else {
                    hidden.hide();
                }

                Event.stop(e);
            });
        }
    },

    queueLateMethod: function(object_name, method_name, args) {
        $(function() {
            if (window[object_name] && window[object_name][method_name]) {
                window[object_name][method_name](args);
            }
        });
    },

    KeyboardShortcuts: function() {
        if (!(this instanceof arguments.callee)) {
            return new arguments.callee();
        }
    },

    makeChart: function (chart_element, chart_data, chart_config) {
        var data,
            chart,
            options,
            col;

        if (!google.visualization[chart_config.type]) {
            throw {
                name: 'InfosysError',
                message: 'No such graph type'
            };
        }

        data = new google.visualization.DataTable();

        for (col in chart_config.columns) {
            if (chart_config.columns.hasOwnProperty(col)) {
                data.addColumn(chart_config.columns[col].type, chart_config.columns[col].name);
            }
        }

        data.addRows(chart_data);

        options = {
          title: chart_config.title
        };

        chart = new google.visualization[chart_config.type](chart_element);
        chart.draw(data, options);

        return chart;
    },
    selectElementContents: function (el) {
        var body = document.body, range, sel;
        if (document.createRange && window.getSelection) {
            range = document.createRange();
            sel = window.getSelection();
            sel.removeAllRanges();
            try {
                range.selectNodeContents(el);
                sel.addRange(range);
            } catch (e) {
                range.selectNode(el);
                sel.addRange(range);
            }
        } else if (body.createTextRange) {
            range = body.createTextRange();
            range.moveToElementText(el);
            range.select();
        }
    }
};

/**
 * initializes the object with event listeners and such
 *
 * @param object options Options object with init data
 *
 * @return this
 */
common.KeyboardShortcuts.prototype.init = function(options) {
    var self = this;

    this.keys_monitored = {};

    options = options || {};

    if (options.focus) {
        this.setFocusShortcuts(options.focus);
    }


    $('body').on('keydown.KeyboardShortcuts', function(event) {
        self.checkKeyPress(event);
    }).on('keyup.KeyboardShortcuts', function(event) {
        if (self.overview_displayed) {
            self.overview_element.hide();
        }

        if (self.overview_timeout) {
            window.clearTimeout(self.overview_timeout);
            self.overview_timeout = null;
        }
    });

    this.original_options = options;

    return self;
};

common.KeyboardShortcuts.prototype.showOverview = function() {
    var list;

    if (!this.keys_monitored) {
        return;
    }

    this.overview_displayed = true;

    if (this.overview_element) {
        this.overview_element.show();
        return;
    }

    list = $('<dl></dl>');
    for (var key in this.keys_monitored) {
        if (this.keys_monitored.hasOwnProperty(key)) {
            list.append('<dt>' + key + '</dt><dd>' + this.keys_monitored[key].name + '</dd>');
        }
    }

    this.overview_element = $('<div><p><strong>Keyboard shortcuts (without ctrl)</strong></p></div>');
    this.overview_element.append(list);

    this.overview_element.appendTo($('body'));
    this.overview_element.css({
        'z-index': 500,
        border: '1px solid black',
        'box-shadow': '2px 2px 5px rgba(0, 0, 0, 0.5)',
        position: 'fixed',
        top: '50px',
        left: '50px',
        'background-color': '#fff',
        padding: '10px'
    });
};

common.KeyboardShortcuts.prototype.checkKeyPress = function(event) {
    var target_nodename = event.target.nodeName.toLowerCase(),
        pressed_key     = event.keyCode && event.keyCode > 31 ? String.fromCharCode(event.keyCode) : '',
        self            = this,
        result;

    if (pressed_key === '' && event.ctrlKey && !this.overview_timeout) {
        this.overview_timeout = window.setTimeout(function() {
            self.showOverview();
        }, 1000);

        return this;
    }

    if (this.overview_timeout) {
        window.clearTimeout(this.overview_timeout);
        self.overview_timeout = null;
    }

    if (this.overview_displayed) {
        this.overview_element.hide();
        this.overview_displayed = false;
    }

    if (event.shiftKey !== true) {
        pressed_key = pressed_key.toLowerCase();
    }

    if (!(result = this.keys_monitored[pressed_key])) {
        return this;
    }

    if (target_nodename == 'input' || target_nodename == 'select' || target_nodename == 'textarea') {
        return this;
    }

    if (event.target.hasAttribute('contentEditable')) {
        return this;
    }

    event.preventDefault();
    event.stopPropagation();

    switch (result.action) {
    case 'focus':
        result.element.focus();
        $('body').scrollTop(result.element.offset().top);
        return this;

    throw new Error('Unknown action: ' + result.action);
    }
};

common.KeyboardShortcuts.prototype.setFocusShortcuts = function(focus) {
    var i, length;

    if (Object.prototype.toString.call(focus) != '[object Array]') {
        throw new Error('Provided focus options is not an array');
    }

    for (i = 0, length = focus.length; i < length; i++) {
        if (!focus[i].key || (!focus[i].selector && !focus[i].element) || !focus[i].key.match(/^\w$/)) {
            throw new Error('Focus option lacks required data');
        }

        this.keys_monitored[focus[i].key] = {
            action:  'focus',
            element: focus[i].element ? focus[i].element : $(focus[i].selector),
            name:    focus[i].name ? focus[i].name : ''
        };
    }
};

$(function() {
    var shortcuts = new common.KeyboardShortcuts(),
        inputs    = $('input.default');

    if (inputs.length) {
        inputs.each(function(idx, item) {
            var item = jQuery(item);
            var defval = item.val();
            item.blur(function() {
                if (item.val() == '') item.val(defval);
            }).focus(function() {
                if (item.val() == defval) item.val('');
            });
        });
    }

    $('input.datetimepicker').datetimepicker({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'HH:mm',
        minDate: window.infosys.con_start_date,
        maxDate: window.infosys.con_end_date,
        onClose: function (value) {
            $(this).val(value);
        }
    });

    $('.wildcardsearch').focus();

    shortcuts.init({
        focus: [
            {selector: 'input.wildcardsearch', name: 'Search', key: 's'},
            {selector: 'div.logo', name: 'Home', key: 'h'},
        ]
    });

    $('ul.topmenu').on('click', 'li.topmenu-item', function () {
        $(this).toggleClass('opened');
    });
});
