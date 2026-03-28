FROM php:8.2-apache

WORKDIR /var/www/html

# 1. Dépendances système
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Configuration Apache : Activer le module Rewrite et pointer vers /public
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 3. Extensions PHP (nécessaire pour MySQL sur Clever Cloud)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 4. Config PHP de production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# 5. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Copie du projet
COPY . /var/www/html

# 7. Installation des dépendances sans les outils de dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. Permissions (Crucial pour Laravel sur Render/Production)
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Script de démarrage : Migrations + Lancement Apache
RUN echo '#!/bin/sh\n\
php artisan migrate --force\n\
apache2-foreground' > /usr/local/bin/start-app.sh && chmod +x /usr/local/bin/start-app.sh

EXPOSE 80

CMD ["/usr/local/bin/start-app.sh"]
