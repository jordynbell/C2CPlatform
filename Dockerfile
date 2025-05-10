FROM php:8.3-apache

WORKDIR /var/www
COPY . .

ENV APACHE_DOCUMENT_ROOT=/var/www/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/conf-available/*.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql mysqli

EXPOSE 80
CMD ["apache2-foreground"]
