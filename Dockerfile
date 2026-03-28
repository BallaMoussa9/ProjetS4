FROM php:8.2-fpm

# Définition du répertoire de travail
WORKDIR /var/www

# Installation des dépendances système (uniquement le strict nécessaire)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP indispensables
# Note : pdo_mysql est conservé pour permettre la connexion à Clever Cloud
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Passage en configuration PHP de production (Sécurité et Performance)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Installation de Composer via l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie des fichiers de l'application dans le conteneur
COPY . /var/www

# Installation des dépendances PHP sans les bibliothèques de test/dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Création des dossiers de cache et gestion des permissions pour www-data
# On s'assure que PHP a les droits d'écriture là où c'est nécessaire
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/tmp 2>/dev/null || true

# Le port par défaut pour PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
