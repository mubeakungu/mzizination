function sleep(ms) {
    return new Promise((resolve) => {
        setTimeout(resolve, ms);
    });
}

function getTimestampInSeconds() {
    return Math.floor(Date.now() / 1000);
}

function parseId(id) {
    return id.replace(/[^A-Za-z0-9\s]/gi, "");
}

module.exports = {
    sleep,
    getTimestampInSeconds,
    parseId,
};
