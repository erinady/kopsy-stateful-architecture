# =========================
# NODE BUILD STAGE (VITE)
# =========================
FROM node:18 AS node_builder

WORKDIR /app

# copy dependency files dulu (lebih stable caching)
COPY package*.json ./
COPY package-lock.json ./

# install dependencies lebih stabil
RUN npm ci

# copy semua source
COPY . .

# pastikan production mode
ENV NODE_ENV=production

# build vite
RUN npm run build


# =========================
# PHP + APACHE STAGE
# =========================
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev

# PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip

# enable apache rewrite
RUN a2enmod rewrite

# set document root ke /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copy project
COPY . .

# copy hasil Vite build
COPY --from=node_builder /app/public/build ./public/build

# install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# permissions
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]