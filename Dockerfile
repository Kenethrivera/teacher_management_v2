# 1. Use the official PHP with Apache image
FROM php:8.2-apache

# 2. Install MySQL extensions (CRITICAL for your database!)
RUN docker-php-ext-install pdo pdo_mysql

# 3. Enable Apache Rewrite Module (Good for clean URLs/routing)
RUN a2enmod rewrite

# 4. Copy all your files into the container's web folder
COPY . /var/www/html/

# 5. Tell Docker to open Port 80 (Standard web port)
EXPOSE 80