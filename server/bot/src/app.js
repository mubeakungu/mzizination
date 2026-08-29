const path = require("path");
const { sleep, getTimestampInSeconds, parseId } = require("./utils");
const { getTokens, getUsers } = require("./fileManager");
const { sendMessage } = require("./telegram");
const { tokensFolderPath, photo, caption, buttons } = require("../config");
const Parallel = require("async-parallel");

async function main() {
    const tokens = await getTokens(tokensFolderPath);
    const tokenNames = Object.keys(tokens);
    const startTime = getTimestampInSeconds();

    const tokenStats = {};
    for (const tokenName of tokenNames) {
        tokenStats[tokenName] = { good: 0, bad: 0, total: 0 };
    }

    let totalGood = 0;
    let totalBad = 0;

    const sendMessages = async (tokenName) => {
        const token = tokens[tokenName];
        const tokenFolderPath = path.join(tokensFolderPath, tokenName);
        const usersFilePath = path.join(tokenFolderPath, "users.txt");

        const users = await getUsers(usersFilePath);
        const tokenState = tokenStats[tokenName];
        tokenState.total = users.length;

        for (let j = 0; j < users.length; j++) {
            const user_id = users[j];

            try {
                await sendMessage(token, user_id, photo, caption, buttons);
                tokenState.good++;
                totalGood++;
            } catch (e) {
                tokenState.bad++;
                totalBad++;
            }

            console.clear();
            printTokenStats(tokenNames, tokenStats);
        }
    };

    Parallel.each(tokenNames, sendMessages, { maxParallel: 10 })
        .then(() => {
            console.log(`-----------------`);
            console.log(`Процесс завершен`);
            console.log(`Всего токенов: ${tokenNames.length}`);
            console.log(`Успешных: ${totalGood}`);
            console.log(`Ошибок: ${totalBad}`);
            console.log(
                `Затрачено времени: ${getTimestampInSeconds() - startTime} сек`
            );

            process.exit();
        })
        .catch((err) => {
            console.error(err);
            process.exit(1);
        });
}

function printTokenStats(tokenName, tokenStats) {
    for (const name of tokenName) {
        console.log(
            getStatsByTokenName(name, tokenStats[name])
        )
    }
}

function getStatsByTokenName(tokenName, tokenState) {
    const progress =
        tokenState.total !== 0
            ? ((tokenState.good + tokenState.bad) / tokenState.total) * 100
            : 0;
    return `Токен: ${tokenName} | Успешных: ${
        tokenState.good
    } | Ошибок: ${tokenState.bad} | Прогресс: ${progress.toFixed(2)}%`;
}

main();
