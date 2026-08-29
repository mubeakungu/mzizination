const app    = require('express')()
const fs     = require('fs')
const config = require('../config')

const getOptions = () => {
    return config.https 
        ? {
            key: fs.readFileSync(config.ssl.key, 'utf8'),
            cert: fs.readFileSync(config.ssl.cert, 'utf8')
        }
        : {}
}

const options = getOptions()

const server = require(config.https ? 'https' : 'http').createServer(options, app) 
const io     = require('socket.io')(server)
const port   = 8443;

const Socket = {}
Socket.emit = (channel, data) => io.sockets.emit(channel, data)

server.listen(port, () => {
    console.clear()
    console.log(`Server started on port = ${port}`)
});

module.exports = {
    Socket,
    io
}