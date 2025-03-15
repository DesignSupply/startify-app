include .env

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
	docker compose exec app bash -c "ln -s /var/www/html/laravel/storage/app/public /var/www/html/_webroot/storage"

laravel-migrate:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan migrate"

laravel-seed:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan db:seed"

laravel-cache-clear:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan cache:clear"

laravel-route-clear:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan route:clear"

laravel-config-clear:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan config:clear"

laravel-optimize:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan optimize"

laravel-test:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan test"

laravel-route:
	docker compose exec app bash -c "cd /var/www/html/laravel && php artisan route:list"

# WordPress
wp-download:
	docker compose exec app wp core download --allow-root --locale=ja --path=/var/www/html/wordpress

wp-config-create:
	docker compose exec app wp config create --allow-root --dbname=$(WP_DATABASE) --dbuser=$(WP_DATABASE_USERNAME) --dbpass=$(WP_DATABASE_PASSWORD) --dbhost=$(DB_HOST) --dbcharset=$(WP_DATABASE_CHARSET) --path=/var/www/html/wordpress

wp-db-create:
	docker compose exec db bash -c "mariadb -u root -p$(DB_PASSWORD) -e 'CREATE DATABASE IF NOT EXISTS $(WP_DATABASE) CHARACTER SET $(WP_DATABASE_CHARSET) COLLATE $(WP_DATABASE_CHARSET)_unicode_ci;'"
	docker compose exec db bash -c "mariadb -u root -p$(DB_PASSWORD) -e \"GRANT ALL PRIVILEGES ON $(WP_DATABASE).* TO '$(WP_DATABASE_USERNAME)'@'%';\""
	docker compose exec db bash -c "mariadb -u root -p$(DB_PASSWORD) -e 'FLUSH PRIVILEGES;'"

wp-install:
	docker compose exec app wp core install --allow-root --url=$(WP_HOME) --title="（開発環境）$(APP_NAME)" --admin_user=$(WP_ADMIN_USERNAME) --admin_password=$(WP_ADMIN_PASSWORD) --admin_email=$(WP_ADMIN_EMAIL) --path=/var/www/html/wordpress
	docker compose exec app wp option update siteurl $(WP_SITEURL) --allow-root --path=/var/www/html/wordpress
	docker compose exec app wp option update home $(WP_HOME) --allow-root --path=/var/www/html/wordpress

wp-update:
	docker compose exec app wp core update --allow-root --path=/var/www/html/wordpress

wp-update-db:
	docker compose exec app wp core update-db --allow-root --path=/var/www/html/wordpress

wp-update-all: wp-update wp-update-db

wp-symlinks:	
	docker compose exec app bash -c "mkdir -p /var/www/html/_webroot/wordpress"
	docker compose exec app bash -c "ln -sf /var/www/html/wordpress/* /var/www/html/_webroot/wordpress/"

wp-entrypoint:
	docker compose exec app bash -c "mkdir -p /var/www/html/_webroot/cms"
	docker compose exec app bash -c "echo '<?php define(\"WP_USE_THEMES\", true); require __DIR__ . \"/../wordpress/wp-blog-header.php\";' > /var/www/html/_webroot/cms/index.php"

wp-setup: wp-download wp-config-create wp-db-create wp-install wp-update-all wp-symlinks wp-entrypoint

wp-plugin-install:
	docker compose exec app wp plugin install ${PLUGIN} --allow-root --activate --path=/var/www/html/wordpress

wp-plugin-update:
	docker compose exec app wp plugin update ${PLUGIN} --allow-root --path=/var/www/html/wordpress

wp-plugin-delete:
	docker compose exec app wp plugin delete ${PLUGIN} --allow-root --path=/var/www/html/wordpress
