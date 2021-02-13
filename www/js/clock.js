document.querySelectorAll('.real-clock').forEach((element) => {
    const timeServer = element.getAttribute('data-timestamp');
    const timeOffset = timeServer - (new Date()).getTime();

    const timeSet = () => {
        const localTime = new Date();
        localTime.setTime(localTime.getTime() + timeOffset);
        const min = localTime.getMinutes();
        const sec = localTime.getSeconds();
        const hours = localTime.getHours();
        element.textContent = ((hours < 10 ? '0' : '') + hours + ':' + (min < 10 ? '0' : '') + min + ':' + (sec < 10 ? '0' : '') + sec);
    }

    window.setTimeout(() => {
        window.setInterval(timeSet, 1000);
        timeSet();
    }, 1000 - timeServer % 1000);
});
$(function () {
    const el = document.getElementById('notifications');
    if (!el) {
        return;
    }
    const url = el.getAttribute('data-link');
    const gameEnd = new Date(el.getAttribute('data-gameEnd'));

    const pollNotifications = (lastAsked) => {
        $.get(url, {lastAsked: lastAsked}, (data) => {
            if (data.redirect) {
                return;
            }
            renderNotifications(data['notifications']);
            const now = data['lastAsked'];
            document.cookie = "lastAsked=" + now + "; expires=" + gameEnd.toUTCString();
            window.setTimeout(function () {
                pollNotifications(now);
            }, data['pollInterval']);
        }, 'json');
    }

    const getCookie = (cname) => {
        const name = cname + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1);
            if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
        }
        return '';
    }

    const renderNotifications = (data) => {
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const datum = data[key];
                const notification = document.createElement('p');
                notification.innerHTML = datum['message'] + '<small class="text-muted pull-right">' + (new Date(datum['created'])).toLocaleTimeString() + '</small>';
                notification.setAttribute('class', 'alert alert-' + datum['level']);
                el.appendChild(notification);
            }
        }
    }

    pollNotifications(getCookie('lastAsked'));
});



