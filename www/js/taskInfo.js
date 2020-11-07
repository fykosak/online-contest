$.fn.extend({
    infoElement: function (select, options) {
        var element = $(this);
        var handler = function () {
            var item = options['data'][select.val()];

            var message = [];
            if (item != undefined) {
                if (item['type'] == 'real') {
                    message.push('<br/>' + options['realHint']);
                }
            }

            element.html(message.join(' '));
        };
        select.change(handler);
        select.keyup(handler);
    },
    timeElement: function (options) {
        var element = $(this);

        window.setInterval(function () {
            var time = parseInt(element.html());
            if (time > 0) {
                time -= 1;
                element.html(time);
            }
            if (time == 0) {
                window.clearInterval();
                if (options['handler'] != undefined) {
                    options['handler']();
                }
            }
        }, 1000);
    }
});
