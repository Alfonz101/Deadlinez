FROM php:8.3-apache

# libpq-dev provides the PostgreSQL headers that pdo_pgsql needs to compile.
# apt-get is the Linux package manager inside the container.
RUN apt-get update && apt-get install -y libpq-dev

# Install PostgreSQL driver for PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Point Apache document root at /var/www/public instead of /var/www/html
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf
