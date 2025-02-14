# ベースイメージを指定
FROM php:8.3-fpm

# 必要な拡張機能をインストール
RUN apt-get update && \
	apt-get install -y --no-install-recommends \
	git \
	unzip \
	libzip-dev \
	libicu-dev \
	libonig-dev && \
	docker-php-ext-install intl pdo_mysql zip bcmath && \
	apt-get clean && rm -rf /var/lib/apt/lists/*

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# WP-CLIのインストール
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

# Mailpitをインストール
RUN ["/bin/bash", "-c", "bash < <(curl -sL https://raw.githubusercontent.com/axllent/mailpit/develop/install.sh)"]

# MailpitのSMTP設定を追加
RUN echo "sendmail_path = '/usr/local/bin/mailpit sendmail -S smtp:1025'" > /usr/local/etc/php/conf.d/mailpit.ini

# 作業ディレクトリを設定
WORKDIR /var/www/html

# ポートを公開
EXPOSE 9000
