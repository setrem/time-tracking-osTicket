// CHANGED!
var audio = new Audio('../assets/osTicketNotification.ogg');
audio.volume = 1;

function timeTrackingAlert(cb) {
    $.get("ajax.php/staff/alert-time-tracking-shift")
        .done(function (data) {
            audio.pause();
            $('[data-id="warning-time-tracking"]').remove();
            if (data.action === 'start') {
                audio.play();
                $('#content').prepend('<div id="msg_warning" style="font-size: 28px; margin-bottom: 16px;" data-id="warning-time-tracking">You need to start time tracking.</div>');
            } else if (data.action === 'stop') {
                $('#content').prepend('<div id="msg_warning" style="font-size: 28px; margin-bottom: 16px;" data-id="warning-time-tracking">You need to stop time tracking <a href="' + data.link_redirect + '">here</a>.</div>');
            }
            if (cb) {
                cb();
            }
        });
}

$(document).ready(function () {
    timeTrackingAlert(function () {
        setInterval(function () {
            timeTrackingAlert();
        }, 1000 * 60);
    });
});
// CHANGED!