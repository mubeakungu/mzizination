export function parseTime2(seconds) {
    var day = Math.floor(seconds / 86400);
    var hour = Math.floor(seconds % (3600*24) / 3600);
    var min = Math.floor(seconds % 3600 / 60);
    var sec = Math.floor(seconds % 60);

    day = day < 10 ? '0' + day : day
    hour = hour < 10 ? '0' + hour : hour
    min = min < 10 ? '0' + min : min
    sec = sec < 10 ? '0' + sec : sec

    return { day, hour, min, sec }
}
export function parseTime(seconds) {
    var hour = Math.floor(seconds % (3600*24) / 3600);
    var min = Math.floor(seconds % 3600 / 60);
    var sec = Math.floor(seconds % 60);

    hour = hour < 10 ? '0' + hour : hour
    min = min < 10 ? '0' + min : min
    sec = sec < 10 ? '0' + sec : sec

    return `${hour}:${min}:${sec}`
}