FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Create .env file if it doesn't exist (for key generation)
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate key (--force to overwrite if exists)
RUN php artisan key:generate --no-interaction --force || true

# Cache configurations
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create nginx configuration
RUN echo 'server { \
    listen 80; \
    server_name _; \
    root /var/www/public; \
    index index.php; \
    charset utf-8; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    location ~ /\.ht { \
        deny all; \
    } \
}' > /etc/nginx/sites-available/default

# Create supervisor configuration
RUN echo '[program:php-fpm] \
command=php-fpm \
autostart=true \
autorestart=true \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0 \
\n \
[program:nginx] \
command=nginx -g "daemon off;" \
autostart=true \
autorestart=true \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0' > /etc/supervisor/conf.d/laravel.conf

# Create migration script
RUN echo '#!/bin/bash\n\
echo "========================================="\n\
echo "Démarrage de l'API Réseau Social"\n\
echo "========================================="\n\
echo ""\n\
echo "1. Attente de la base de données..."\n\
sleep 5\n\
echo "2. Exécution des migrations..."\n\
php artisan migrate --force\n\
echo "3. Optimisation du cache..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
echo "4. Démarrage des services..."\n\
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf' > /var/www/start.sh

RUN chmod +x /var/www/start.sh

EXPOSE 80

CMD ["/var/www/start.sh"]
