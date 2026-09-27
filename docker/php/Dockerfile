FROM php:8.2-fpm-alpine

# 必要パッケージのインストール
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    mysql-client

# PHP拡張のインストール
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# Composerの取得
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
