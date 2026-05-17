# Twitter Clone

A full-stack Twitter-like app built with Laravel 11 (API), React 18 (SPA), and PostgreSQL.

## Architecture Decisions

### Timeline and Follows Graph
The timeline is built using a simple follower/following join on the `follows` table. When a user requests their timeline, the API fetches all `following_id` values for the authenticated user, then queries `tweets` by those user IDs ordered by `(created_at DESC, id DESC)`. Cursor-based pagination encodes the last tweet's timestamp and ID, avoiding the offset penalty of page-based pagination. The `tweets` table has a compound index on `(user_id, created_at)`.

### Authentication
Laravel Sanctum token authentication: on login/register the backend issues a plain-text API token (stored in `personal_access_tokens`). The React app stores the token in `localStorage` and sends it as `Authorization: Bearer <token>` on every request. Session/cookie auth was intentionally avoided to keep the API stateless and deployable separately.

### Trade-offs and Known Limitations
- **No real-time updates**: The timeline does not update live; users must refresh to see new tweets. WebSockets (Pusher/Reverb) would fix this.
- **No tweet media**: Media upload is listed as an extra feature and is not implemented.
- **Search is case-insensitive**: Uses `LOWER(name) LIKE ?` (works on both SQLite in tests and PostgreSQL in production).
- **No rate limiting**: The API has no rate limits on tweet creation or likes.
- **Single-server deployment**: Docker Compose runs all services on one machine; not horizontally scaled.
- **Follow state on profile**: The profile page FollowButton does not reflect actual follow state — `GET /users/:username` does not return a followers array. The button always defaults to "Follow" regardless of actual follow state.

## Quick Start (Docker)

```bash
cp .env.example .env
# Edit .env: set APP_KEY (run `php artisan key:generate` inside the backend container, or locally in backend/)
docker compose up --build
```
Open http://localhost in your browser.

Login with seed user: **alice@example.com** / **password**

## Local Development

### Prerequisites
- PHP 8.3+, Composer 2.x
- Node 20+, npm 10+
- PostgreSQL 15+

### Backend
```bash
cd backend
cp .env.example .env
# Edit .env: set DB_CONNECTION=pgsql, DB_DATABASE, DB_USERNAME, DB_PASSWORD
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
# Backend runs at http://localhost:8000
```

### Frontend
```bash
cd frontend
cp .env.example .env
# VITE_API_URL=http://localhost:8000/api is already set in .env.example
npm install
npm run dev
# Frontend runs at http://localhost:5173
```

### Run Tests
```bash
# Backend (PHPUnit)
cd backend && php artisan test

# Backend with coverage
cd backend && php artisan test --coverage

# Frontend (Vitest)
cd frontend && npm run test
```

### Seed Data
```bash
cd backend && php artisan migrate:fresh --seed
```

Seed users (all passwords are `password`):

| Name | Email | Username |
|------|-------|----------|
| Alice Chen | alice@example.com | alicechen |
| Bob Martinez | bob@example.com | bobmartinez |
| Clara Johnson | clara@example.com | claraj |
| David Kim | david@example.com | davidkim |
| Eva Torres | eva@example.com | evatorres |
| Frank Osei | frank@example.com | frankosei |
| Grace Liu | grace@example.com | graceliu |
| Hiro Tanaka | hiro@example.com | hirotanaka |
| Isabelle Blanc | isabelle@example.com | isabelleblanc |
| Jake Williams | jake@example.com | jakewilliams |

## Environment Variables

### Backend (`backend/.env`)

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_KEY` | Laravel app key (generate with `php artisan key:generate`) | `base64:...` |
| `DB_CONNECTION` | Database driver | `pgsql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `5432` |
| `DB_DATABASE` | Database name | `twitter_clon` |
| `DB_USERNAME` | Database user | `postgres` |
| `DB_PASSWORD` | Database password | `secret` |

### Frontend (`frontend/.env`)

| Variable | Description | Example |
|----------|-------------|---------|
| `VITE_API_URL` | Backend API base URL | `http://localhost:8000/api` |
