FROM php:8.3-apache

# Install mysqli and PDO extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

EXPOSE 80
