const TelegramBot = require("node-telegram-bot-api");

function sendMessage(token, user_id, photo, caption, buttons) {
    return new Promise((resolve, reject) => {
        const inlineKeyboard = buttons.map((buttonRow) =>
            buttonRow.map((button) => ({
                text: button.text,
                url: button.url,
            }))
        );

        const botWithToken = new TelegramBot(token);

        if(photo) {
            return botWithToken
                .sendPhoto(user_id, photo, {
                    caption,
                    reply_markup: { inline_keyboard: inlineKeyboard },
                })
                .then((_) => resolve())
                .catch((e) => reject(e));
        }

        botWithToken
            .sendMessage(user_id, caption, {
                reply_markup: { inline_keyboard: inlineKeyboard },
            })
            .then((_) => resolve())
            .catch((e) => reject(e));
    });
}

module.exports = {
    sendMessage,
};
