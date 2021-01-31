$.fn.extend({
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
