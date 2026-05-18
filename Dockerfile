# =========================
# NODE BUILD STAGE (VITE)
# =========================
FROM node:18 AS node_builder

WORKDIR /app

# Copy dependency files untuk kestabilan caching
COPY package*.json ./

# Install dependencies JavaScript
RUN npm ci

# Copy semua source code untuk di-build oleh Vite
COPY . .

# Pastikan production mode
ENV NODE_ENV=production

# Build assets Vite (CSS/JS)
RUN npm run build


# =========================
# PHP + APACHE STAGE
# =========================
FROM php:8.2-apache

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions (PostgreSQL & Zip)
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip

# Enable Apache rewrite module
RUN a2enmod rewrite

# FIX WARNING: Mengubah format ENV ke "key=value" agar tidak legacy
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy semua source code proyek
COPY . .

# Copy hasil build Vite dari stage node_builder ke folder public/build
COPY --from=node_builder /app/public/build ./public/build

# Install PHP dependencies (production mode)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# FIX PERMISSIONS: Membuat struktur folder Laravel dengan absolute path agar aman
RUN mkdir -p \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

# Mengatur hak kepemilikan dan izin akses tulis untuk server Apache (www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]