// СТАРЫЙ БОТ ОТКЛЮЧЕН - используется новый @valuba_bot через webhook
// Файл: app/Http/Controllers/TelegramController.php

const config = require('./config')

// Пустой экспорт чтобы app.js не падал
module.exports = {
    sendMessage: () => {},
    on: () => {}
};

console.log('[Telegram] Old bot disabled. Using new webhook bot @valuba_bot');
