module.exports = {
  apps: [
    {
      name: 'golden1x-server',
      script: './server/app.js',
      cwd: '/var/www/golden1x.ru',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production'
      },
      error_file: '/var/www/golden1x.ru/storage/logs/pm2-server-error.log',
      out_file: '/var/www/golden1x.ru/storage/logs/pm2-server-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z'
    },
    {
      name: 'golden1x-telegram',
      script: './server/telegram.js',
      cwd: '/var/www/golden1x.ru',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        NODE_ENV: 'production'
      },
      error_file: '/var/www/golden1x.ru/storage/logs/pm2-telegram-error.log',
      out_file: '/var/www/golden1x.ru/storage/logs/pm2-telegram-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z'
    }
  ]
};
