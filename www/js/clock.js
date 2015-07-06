$('.real-clock').each(function() {
    var el = $(this);
    var time_server = el.data('timestamp');
    var time_offset = time_server - (new Date()).getTime();
    function time_set() {
        var local_time = new Date();
        local_time.setTime(local_time.getTime() + time_offset);
        var min = local_time.getMinutes();
        var sec = local_time.getSeconds();
        el.html(local_time.getHours() + ':' + (min < 10 ? '0' : '') + min + ':' + (sec < 10 ? '0' : '') + sec);
    }
    window.setTimeout(function() {
        window.setInterval(time_set, 1000);
        time_set();
    }, 1000 - time_server % 1000);
});
