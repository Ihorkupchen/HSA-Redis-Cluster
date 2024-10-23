# Dockerfile
FROM php:8.1-cli

# Встановлення phpredis
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www/html

CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]