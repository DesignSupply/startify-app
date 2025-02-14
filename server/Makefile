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

exec-web:
	docker compose exec web /bin/sh

exec-app:
	docker compose exec app /bin/sh

exec-db:
	docker compose exec db /bin/sh

exec-smtp:
	docker compose exec smtp /bin/sh

# Laravel
laravel-install:
	docker compose exec app bash -c "cd /var/www/html/backend && composer create-project --prefer-dist laravel/laravel laravel"

laravel-migrate:
	docker compose exec app php artisan migrate

laravel-seed:
	docker compose exec app php artisan db:seed

laravel-cache-clear:
	docker compose exec app php artisan cache:clear

laravel-config-clear:
	docker compose exec app php artisan config:clear

laravel-optimize:
	docker compose exec app php artisan optimize

# WordPress
wp-cli-install:
	docker compose exec app wp core download --path=/var/www/html/backend/wordpress

wp-config-create:
	docker compose exec app wp config create --dbname=$(DB_DATABASE) --dbuser=$(DB_USERNAME) --dbpass=$(DB_PASSWORD) --dbhost=$(DB_HOST) --path=/var/www/html/backend/wordpress

wp-install:
	docker compose exec app wp core install --url=$(APP_URL) --title="WordPress Site" --admin_user=$(CMS_USERNAME) --admin_password=$(CMS_PASSWORD) --admin_email=$(CMS_EMAIL) --path=/var/www/html/backend/wordpress

wp-plugin-install:
	docker compose exec app wp plugin install $(PLUGIN) --activate --path=/var/www/html/backend/wordpress

wp-plugin-update:
	docker compose exec app wp plugin update $(PLUGIN) --path=/var/www/html/backend/wordpress

wp-plugin-delete:
	docker compose exec app wp plugin delete $(PLUGIN) --path=/var/www/html/backend/wordpress

wp-setup: wp-cli-install wp-config-create wp-install
