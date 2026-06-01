FROM php:8.4-apache

# Extensions PHP requises
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl mbstring opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite pour le routing Symfony
RUN a2enmod rewrite

# Configuration Apache – DocumentRoot sur /public
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier le projet
COPY . .

# Installer les dépendances sans les devtools
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions cache/logs
RUN chown -R www-data:www-data var/ public/uploads/

EXPOSE 80
