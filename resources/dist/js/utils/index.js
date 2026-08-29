export function parseTime(seconds) {
    var hour = Math.floor(seconds % (3600*24) / 3600);
    var min = Math.floor(seconds % 3600 / 60);
    var sec = Math.floor(seconds % 60);

    hour = hour < 10 ? '0' + hour : hour
    min = min < 10 ? '0' + min : min
    sec = sec < 10 ? '0' + sec : sec

    return `${hour}:${min}:${sec}`
}

export function getRandomNumber(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}

export function getEmptyArr(length, def) {
    const arr = []
  
    for(let i = 0; i < length; i++) {
        arr.push({ ...def, index: i })
    }
  
    return arr
}

export function getColor(position, pins) {
    position -= 1
    
    switch (pins) {
        case 16:
            if (0 == position || 16 == position) return "rgb(55, 236, 126)";
            if (1 == position || 15 == position) return "rgb(79.25, 233.875, 125.625)";
            if (2 == position || 14 == position) return "rgb(103.5, 231.75, 125.25)";
            if (3 == position || 13 == position) return "rgb(127.75, 229.625, 124.875)";
            if (4 == position || 12 == position) return "rgb(152, 227.5, 124.5)";
            if (5 == position || 11 == position) return "rgb(176.25, 225.375, 124.125)";
            if (6 == position || 10 == position) return "rgb(200.5, 223.25, 123.75)";
            if (7 == position || 9 == position) return "rgb(224.75, 221.125, 123.375)";
            if (8 == position) return "rgb(249, 219, 123)";
            break;
        case 15:
        case 14:
            if (0 == position || (15 == position && 15 == pins) || (14 == position && 14 == pins)) return "rgb(55, 236, 126)";
            if (1 == position || (14 == position && 15 == pins) || (13 == position && 14 == pins)) return "rgb(79.25, 233.875, 125.625)";
            if (2 == position || (13 == position && 15 == pins) || (12 == position && 14 == pins)) return "rgb(103.5, 231.75, 125.25)";
            if (3 == position || (12 == position && 15 == pins) || (11 == position && 14 == pins)) return "rgb(127.75, 229.625, 124.875)";
            if (4 == position || (11 == position && 15 == pins) || (10 == position && 14 == pins)) return "rgb(152, 227.5, 124.5)";
            if (5 == position || (10 == position && 15 == pins) || (9 == position && 14 == pins)) return "rgb(200.5, 223.25, 123.75)";
            if (6 == position || (9 == position && 15 == pins) || (8 == position && 14 == pins)) return "rgb(224.75, 221.125, 123.375)";
            if (7 == position || (8 == position && 15 == pins)) return "rgb(249, 219, 123)";
            break;
        case 13:
        case 12:
            if (0 == position || (13 == position && 13 == pins) || (12 == position && 12 == pins)) return "rgb(55, 236, 126)";
            if (1 == position || (12 == position && 13 == pins) || (11 == position && 12 == pins)) return "rgb(79.25, 233.875, 125.625)";
            if (2 == position || (11 == position && 13 == pins) || (10 == position && 12 == pins)) return "rgb(103.5, 231.75, 125.25)";
            if (3 == position || (10 == position && 13 == pins) || (9 == position && 12 == pins)) return "rgb(152, 227.5, 124.5)";
            if (4 == position || (9 == position && 13 == pins) || (8 == position && 12 == pins)) return "rgb(200.5, 223.25, 123.75)";
            if (5 == position || (8 == position && 13 == pins) || (7 == position && 12 == pins)) return "rgb(224.75, 221.125, 123.375)";
            if (6 == position || (7 == position && 13 == pins)) return "rgb(249, 219, 123)";
            break;
        case 11:
        case 10:
            if (0 == position || (11 == position && 11 == pins) || (10 == position && 10 == pins)) return "rgb(55, 236, 126)";
            if (1 == position || (10 == position && 11 == pins) || (9 == position && 10 == pins)) return "rgb(79.25, 233.875, 125.625)";
            if (2 == position || (9 == position && 11 == pins) || (8 == position && 10 == pins)) return "rgb(103.5, 231.75, 125.25)";
            if (3 == position || (8 == position && 11 == pins) || (7 == position && 10 == pins)) return "rgb(152, 227.5, 124.5)";
            if (4 == position || (7 == position && 11 == pins) || (6 == position && 10 == pins)) return "rgb(200.5, 223.25, 123.75)";
            if (5 == position || (6 == position && 11 == pins)) return "rgb(249, 219, 123)";
            break;
        case 9:
        case 8:
            if (0 == position || (8 == position && 8 == pins) || (9 == position && 9 == pins)) return "rgb(55, 236, 126)";
            if (1 == position || (7 == position && 8 == pins) || (8 == position && 9 == pins)) return "rgb(103.5, 231.75, 125.25)";
            if (2 == position || (6 == position && 8 == pins) || (7 == position && 9 == pins)) return "rgb(152, 227.5, 124.5)";
            if (3 == position || (5 == position && 8 == pins) || (6 == position && 9 == pins)) return "rgb(200.5, 223.25, 123.75)";
            if (4 == position || (5 == position && 9 == pins)) return "rgb(249, 219, 123)";
            break;
    }
}
