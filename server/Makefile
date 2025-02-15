# Docker
up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f

build:
	docker compose build

destroy:
	docker compose down --rmi all --volumes --remove-orphans

ps:
	docker compose ps

restart:
	docker compose restart 

server-web:
	docker compose exec web /bin/sh

server-app:
	docker compose exec app /bin/sh

server-db:
	docker compose exec db /bin/sh

server-smtp:
	docker compose exec smtp /bin/sh

# Laravel
laravel-install:
	docker compose exec app bash -c "cd /var/www/html && composer create-project --prefer-dist laravel/laravel laravel"

laravel-keygen:
	docker compose exec app bash -c "cd /var/www/html/laravel && cp .env.example .env && php artisan key:generate"

laravel-storage-link:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan storage:link"

laravel-storage-link-change:
	docker compose exec app bash -c "rm -f /var/www/html/laravel/public/storage"
	docker compose exec app bash -c "ln -s /var/www/html/laravel/storage/app/public /var/www/html/public/storage"

laravel-migrate:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan migrate"

laravel-seed:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan db:seed"

laravel-cache-clear:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan cache:clear"

laravel-config-clear:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan config:clear"

laravel-optimize:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan optimize"

laravel-test:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan test"

# WordPress
wp-cli-install:
	docker compose exec app wp core download --path=/var/www/html/wordpress

wp-config-create:
	docker compose exec app wp config create --dbname=$(CMS_DATABASE) --dbuser=$(DB_USERNAME) --dbpass=$(DB_PASSWORD) --dbhost=$(DB_HOST) --path=/var/www/html/wordpress

wp-install:
	docker compose exec app wp core install --url=$(APP_URL) --title="WordPress Site" --admin_user=$(CMS_USERNAME) --admin_password=$(CMS_PASSWORD) --admin_email=$(CMS_EMAIL) --path=/var/www/html/wordpress

wp-plugin-install:
	docker compose exec app wp plugin install $(PLUGIN) --activate --path=/var/www/html/wordpress

wp-plugin-update:
	docker compose exec app wp plugin update $(PLUGIN) --path=/var/www/html/wordpress

wp-plugin-delete:
	docker compose exec app wp plugin delete $(PLUGIN) --path=/var/www/html/wordpress

wp-setup: wp-cli-install wp-config-create wp-install
