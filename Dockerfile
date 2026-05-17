FROM php:8.2-apache

# Install dependency
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl \
    zip

# Install ekstensi PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Apache document root ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
/etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
/etc/apache2/apache2.conf \
/etc/apache2/conf-available/*.conf

# Enable rewrite
RUN a2enmod rewrite

# Working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Install Laravel dependency
RUN composer install --no-dev --optimize-autoloader

# Permission Laravel
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]