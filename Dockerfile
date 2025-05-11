FROM php:8.3-apache

WORKDIR /var/www

COPY . .

ENV APACHE_DOCUMENT_ROOT=/var/www/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf \
    && a2enmod rewrite

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

RUN docker-php-ext-install pdo pdo_mysql mysqli

EXPOSE 80
CMD ["apache2-foreground"]
