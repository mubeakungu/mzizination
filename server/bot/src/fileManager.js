const fs = require("fs");
const path = require("path");

function getTokens(folderPath) {
    return new Promise((resolve, reject) => {
        fs.readdir(folderPath, (err, files) => {
            if (err) {
                return reject(err);
            }

            const tokens = {};

            const promises = files.map((file) => {
                const tokenFolderPath = path.join(folderPath, file);
                const tokenFilePath = path.join(tokenFolderPath, "token.txt");
                const usersFilePath = path.join(tokenFolderPath, "users.txt");

                if (!fs.existsSync(tokenFilePath)) {
                    console.error(
                        `Ошибка: Файл token.txt не найден в папке ${tokenFolderPath}`
                    );
                    process.exit(1);
                }

                if (!fs.existsSync(usersFilePath)) {
                    console.error(
                        `Ошибка: Файл users.txt не найден в папке ${tokenFolderPath}`
                    );
                    process.exit(1);
                }

                return new Promise((resolveToken, rejectToken) => {
                    fs.readFile(tokenFilePath, "utf8", (err, data) => {
                        if (err) {
                            return rejectToken(err);
                        }
                        
                        tokens[file] = data.trim();
                        resolveToken();
                    });
                });
            });

            Promise.all(promises)
                .then(() => {
                    resolve(tokens);
                })
                .catch(reject);
        });
    });
}

function getUsers(filePath) {
    return new Promise((resolve, reject) => {
        fs.readFile(filePath, "utf8", (err, data) => {
            if (err) {
                return reject(err);
            }

            const users = data.split("\r\n");
            resolve(users);
        });
    });
}

module.exports = {
    getTokens,
    getUsers,
};
