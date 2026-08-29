# Taker Casino Script — Full Version (2026)

Full-featured online casino platform. Slots aggregator integration, live casino and original in-house games. Responsive (mobile / tablet / desktop), light & dark themes.

Installation guide: see [`INSTALL.md`](INSTALL.md).

## Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 7 (PHP 7.4+) |
| Frontend | Vue.js 2 |
| Realtime | Node.js 14+ (PM2) |
| Database | MySQL 5.7+ / MariaDB 10.3+ |
| Server | Nginx · Composer · Let's Encrypt SSL |

## Screenshots

<img src="screenshots/taker-1.jpg" width="100%" alt="Taker Casino" />

<img src="screenshots/taker-2.webp" width="100%" alt="Taker Casino games" />

## Features

**Slots** — aggregator integration. Providers: NetEnt · Pragmatic Play · Play'n GO · Red Tiger · Relax Gaming · Amatic and many others.

**Live Casino** — Live Roulette via Ezugi · Vivo · TVBET.

**Original games**
| Game | Description |
|---|---|
| Mines | Minesweeper with adjustable mines & multipliers |
| Dice | Dice with adjustable odds |
| Wheel | Wheel of Fortune |
| Plinko | Ball through a pyramid |
| Bubbles | Pop the bubbles |

**Platform**
- Responsive UI (mobile / tablet / PC), light & dark themes
- Admin panel (`/admin`) — providers, players, settings, reports
- Telegram bot integration (deposits, notifications)
- Payment integrations
- Crypto wallet support

## Requirements

- Ubuntu 20.04+ / Debian 11+
- PHP 7.4+ (mbstring, xml, curl, mysql, zip, gd)
- MySQL 5.7+ / MariaDB 10.3+
- Node.js 14+ · Composer · Nginx · PM2

## Quick Start

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
cp .env.example .env      # edit DB + settings
mysql -u taker -p taker < baze.sql
npm install --legacy-peer-deps && npm run prod
pm2 start ecosystem.config.js
```

Full step-by-step: [`INSTALL.md`](INSTALL.md).

---

### Need games, live casino or a sportsbook feed?

This script integrates with game aggregators. If you need a ready **seamless-wallet API** — thousands of slots, live casino and a full **sportsbook** (own-book, cashout) behind a single integration, plus **clone / white-label** setups:

<img src="https://web.telegram.org/a/favicon-32x32.png" width="18" height="18"> **Flexrix** — [Telegram: @davidflexrix](https://t.me/davidflexrix) · [flexrix.com](https://flexrix.com)

<sub>Casino · Live Casino · Sportsbook · Clone / White-label · Seamless wallet (HMAC) · multi-currency & crypto.</sub>
