FROM php:8.3-cli

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    unzip \
    curl \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        xml \
        curl \
        zip \
        bcmath \
    && curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin \
        --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

RUN cd backend && composer install --no-dev --optimize-autoloader \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8000
CMD cd backend && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
