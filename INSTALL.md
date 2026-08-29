# Инструкция по установке TAKER

## Требования к серверу

- Ubuntu 20.04+ / Debian 11+
- PHP 7.4+ с расширениями: mbstring, xml, curl, mysql, zip, gd
- MySQL 5.7+ или MariaDB 10.3+
- Node.js 14+
- Composer
- Nginx
- PM2 (для Node.js процессов)
- SSL сертификат (Let's Encrypt)

---

## 1. Подготовка сервера

```bash
# Обновление системы
apt update && apt upgrade -y

# Установка PHP и расширений
apt install php7.4-fpm php7.4-mysql php7.4-mbstring php7.4-xml php7.4-curl php7.4-zip php7.4-gd -y

# Установка MySQL
apt install mysql-server -y

# Установка Nginx
apt install nginx -y

# Установка Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install nodejs -y

# Установка PM2
npm install -g pm2

# Установка Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

---

## 2. Загрузка файлов

```bash
# Создание директории
mkdir -p /var/www/your-domain.com
cd /var/www/your-domain.com

# Загрузите файлы проекта через SFTP или git
# Установите права
chown -R www-data:www-data /var/www/your-domain.com
chmod -R 755 /var/www/your-domain.com
chmod -R 775 storage bootstrap/cache
```

---

## 3. Настройка базы данных

```bash
mysql -u root -p
```

```sql
CREATE DATABASE taker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'taker'@'localhost' IDENTIFIED BY 'ваш_пароль';
GRANT ALL PRIVILEGES ON taker.* TO 'taker'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Импортируйте дамп базы данных:
```bash
mysql -u taker -p taker < database.sql
```

---

## 4. Настройка .env

Скопируйте и отредактируйте файл конфигурации:

```bash
cp .env.example .env
nano .env
```

### Основные настройки:

```env
APP_NAME=TAKER
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_HTTPS=true
SSL_KEY_PATH='/etc/letsencrypt/live/your-domain.com/privkey.pem'
SSL_CERT_PATH='/etc/letsencrypt/live/your-domain.com/fullchain.pem'
```

### База данных:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=taker
DB_USERNAME=taker
DB_PASSWORD=ваш_пароль
```

### VK авторизация:

1. Создайте приложение на https://vk.com/apps?act=manage
2. Тип: Веб-сайт
3. Укажите домен и Redirect URI

```env
VKONTAKTE_CLIENT_ID=ваш_app_id
VKONTAKTE_CLIENT_SECRET=ваш_secret_key
VKONTAKTE_SERVICE_KEY=ваш_service_key
VKONTAKTE_REDIRECT_URI=https://your-domain.com/auth/vk/callback
```

### Telegram бот:

1. Создайте бота через @BotFather
2. Получите токен

```env
TELEGRAM_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_WEBHOOK_SECRET=случайная_строка_для_безопасности
TELEGRAM_ADMIN_IDS=ваш_telegram_id
TELEGRAM_CHANNEL_ID=@ваш_канал
TELEGRAM_MIN_DEPOSIT_NOTIFY=5000
```

---

## 5. Установка зависимостей

```bash
cd /var/www/your-domain.com

# PHP зависимости
composer install --no-dev --optimize-autoloader

# Генерация ключа приложения
php artisan key:generate

# Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Node.js зависимости
npm install --legacy-peer-deps

# Сборка фронтенда (production)
npm run prod
```

---

## 6. Настройка Nginx

Создайте конфиг:

```bash
nano /etc/nginx/sites-available/your-domain.com
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/your-domain.com/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Активируйте конфиг:

```bash
ln -s /etc/nginx/sites-available/your-domain.com /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

---

## 7. SSL сертификат (Let's Encrypt)

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d your-domain.com -d www.your-domain.com
```

---

## 8. Настройка PM2

Отредактируйте `ecosystem.config.js` — замените пути на ваш домен:

```bash
nano ecosystem.config.js
```

```javascript
module.exports = {
  apps: [
    {
      name: 'taker-server',
      script: './server/app.js',
      cwd: '/var/www/your-domain.com',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production'
      }
    },
    {
      name: 'taker-telegram',
      script: './server/telegram.js',
      cwd: '/var/www/your-domain.com',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        NODE_ENV: 'production'
      }
    }
  ]
};
```

Запуск PM2:

```bash
cd /var/www/your-domain.com
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

Полезные команды PM2:
```bash
pm2 list              # Список процессов
pm2 logs              # Логи всех процессов
pm2 restart all       # Перезапуск всех
pm2 stop all          # Остановка всех
```

---

## 9. Настройка Telegram Webhook

После запуска PM2 установите webhook:

```
https://your-domain.com/telegram/set-webhook
```

Проверка webhook:
```
https://your-domain.com/telegram/webhook-info
```

---

## 10. Настройка слотов

В админ-панели (`/admin`) в разделе "Настройки" укажите:

- **TBS Provider ID** и **TBS Provider Secret** — получите у агрегатора TBS
- **B2B Provider ID** — получите у агрегатора B2B

---

## 11. Первый вход

1. Откройте сайт
2. Авторизуйтесь через ВК
3. В базе данных установите себе права админа:

```sql
UPDATE users SET is_admin = 1 WHERE id = 1;
```

4. Админ-панель доступна по адресу: `https://your-domain.com/admin`

---

## Возможные проблемы

### Ошибка 500
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
php artisan config:clear
```

### Не работает авторизация ВК
- Проверьте правильность `VKONTAKTE_REDIRECT_URI`
- Убедитесь что домен добавлен в настройках VK приложения

### Не работает Telegram бот
- Проверьте токен в `.env`
- Проверьте что PM2 процесс `taker-telegram` запущен: `pm2 list`
- Проверьте логи: `pm2 logs taker-telegram`

### Не грузятся слоты
- Проверьте настройки провайдеров в админке
- Проверьте что PM2 процесс `taker-server` запущен

---
