# backend/Dockerfile
FROM php:8.2-apache

# 安裝系統依賴
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# 安裝 PHP 擴展
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath

# 安裝 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 設置工作目錄
WORKDIR /var/www/html

# 複製 Laravel 專案文件
COPY . .

# 安裝依賴（生產環境優化）
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 設置檔案權限
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage

# 設置 Apache 指向 public 目錄
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 啟用 Apache 重寫模組
RUN a2enmod rewrite
