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
