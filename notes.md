1) Create/adjust your .env

Run these in PowerShell (or inside the app container — both work since the code is bind-mounted):

# (host) if .env doesn’t exist yet
Copy-Item .env.example .env


Then set these values (important ones shown):

APP_NAME=Najib ERP
APP_ENV=local
APP_KEY=            # will be generated next step
APP_DEBUG=true
APP_URL=http://localhost

# DB (matches docker-compose)
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=app
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Cache / session / queue
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Broadcasting + Reverb (so Echo works out of the box)
BROADCAST_CONNECTION=reverb
REVERB_APP_KEY=local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_HOST=${REVERB_HOST}
VITE_REVERB_PORT=${REVERB_PORT}

2) Install PHP deps, generate key, migrate

Run everything inside the app container:

docker compose exec app bash
composer install --no-interaction --prefer-dist
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
exit


If bash isn’t found, use sh.

3) Build frontend assets (choose one)

If you have Node locally:

npm ci
npm run build


If you don’t have Node (use a one-off Node container):

docker run --rm -v "${PWD}:/app" -w /app node:20 npm ci
docker run --rm -v "${PWD}:/app" -w /app -p 5173:5173 node:20 npm run dev -- --host


(or swap npm run dev for npm run build)

4) Visit the app & quick checks

App: http://localhost
 (served by Nginx → PHP-FPM)

Reverb WS: ws://localhost:8080 (already running as its own service)

Horizon queue is running in the queue container.
If you’ve published Horizon routes, go to http://localhost/horizon
.

Sanity checks:

docker compose logs -f app
docker compose logs -f queue
docker compose logs -f reverb
docker compose exec redis redis-cli ping   # expect PONG
docker compose exec app php artisan migrate:status

5) (Optional) Install broadcasting/Echo scaffolding

If you haven’t done it yet:

docker compose exec app bash
php artisan install:broadcasting
php artisan reverb:install   # publishes config/reverb.php & Vite envs
exit

6) Common gotchas

If port 80 or 8080 is busy, change them in docker-compose.yml (web.ports, reverb.ports) and APP_URL / Reverb envs accordingly.

PCNTL errors only happen if you run Horizon outside Docker. Keep using the queue service.

On Windows, bind mounts can be slower. If you notice slowness, consider using a volume for /vendor:

app:
  volumes:
    - ./:/var/www/html
    - vendor_cache:/var/www/html/vendor
volumes:
  vendor_cache: {}