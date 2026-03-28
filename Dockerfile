FROM php:8.2-apache

WORKDIR /var/www/html

# Dépendances système
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activation du module de réécriture Apache (pour les routes)
RUN a2enmod rewrite

# Extensions PHP (pdo_mysql est indispensable pour les migrations)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

# Installation propre
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# --- LA MIGRATION ---
# On crée un petit script de démarrage pour lancer les migrations puis Apache
RUN echo '#!/bin/sh\n\
php artisan migrate --force\n\
apache2-foreground' > /usr/local/bin/start-app.sh && chmod +x /usr/local/bin/start-app.sh

EXPOSE 80

# On lance le script au démarrage
CMD ["/usr/local/bin/start-app.sh"]
