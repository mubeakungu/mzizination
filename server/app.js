const { Socket, io } = require('./functions/socket')
const RedisClient    = require('./functions/redis')
const config         = require('./config')
const bot            = require('./telegram')
const axios          = require('axios')
const cron           = require('node-cron')

let games = [],
    timerBot = null
    interval = null;
    
var slotsHistory = [];

// Фейковый онлайн от ботов (случайное число, меняется каждые 30 сек)
let fakeOnline = Math.floor(Math.random() * 16) + 15; // 15-30
setInterval(() => {
    // Плавно меняем фейковый онлайн на +-3
    const change = Math.floor(Math.random() * 7) - 3; // -3 to +3
    fakeOnline = Math.max(10, Math.min(40, fakeOnline + change));
}, 30000);

RedisClient.subscribe('newGame')
RedisClient.subscribe('setNewBotTimer');
RedisClient.subscribe('userMessage');
RedisClient.subscribe('userRank');
RedisClient.subscribe('slhis');

RedisClient.on('message', async (channel, message) => {
    if(channel == 'setNewBotTimer') {
        clearInterval(interval);
        interval = null;
        timerBot = message;

        return startBot();
    }

    if(channel == 'newGame') {
        if(games.length >= 14) {
            games.pop()
        }
        games.unshift(JSON.parse(message))
        return Socket.emit(channel, JSON.parse(message))
    }
    if(channel == "slhis") {
        console.log(channel, message);
        let data = JSON.parse(message);
        
        slotsHistory.unshift(data);

        if(slotsHistory.length > 7) slotsHistory.pop();
        return Socket.emit("slotsHistory", data)
    }
    Socket.emit(channel, JSON.parse(message))
})


io.on('connection', (socket) => {
    const updateOnline = () => {
        // Реальный онлайн + фейковый от ботов
        const realOnline = Object.keys(io.sockets.adapter.rooms).length;
        Socket.emit('online', realOnline + fakeOnline);
    };

    socket.on('disconnect', () => {
        updateOnline();
    });
    socket.on("getHistory", () => {
        socket.emit("getHistory", slotsHistory);
    });
    socket.emit('history', games)
    updateOnline();
})

const startBot = () => {
    interval = setInterval(() => {
        axios.post(`${config.domain}/api/fake`)
            .then(res => {

            })
            .catch(err => {})
    }, timerBot);
};

const getTimer = () => {
    axios.post(`${config.domain}/api/getTimer`)
        .then(res => {
            timerBot = res.data;

            startBot();
        })
        .catch(err => {})
};

getTimer() // запускаем ботов

const sendCashback = () => {
    axios.post('/api/cashback/send')
    .catch(() => {
        console.log('[sendCashback] error')
        setTimeout(sendCashback, 1000)
    })
}

const resetCashback = () => {
    axios.post('/api/cashback/reset')
    .catch(() => {
        console.log('[resetCashback] error')
        setTimeout(resetCashback, 1000)
    })
}

// начисляем кешбек
cron.schedule('0 0 * * 1', () => {
    sendCashback()
});


// обнуляем кешбек
cron.schedule('59 23 * * 1', () => {
    resetCashback()
});