# Dockerfile excerpt
FROM php:8.4-apache

WORKDIR /var/www
COPY . .

# point Apache at public/
ENV APACHE_DOCUMENT_ROOT=/var/www/public

# increase upload size to ensure bigger pictures (phone image) can be uploaded
RUN { \
  echo 'upload_max_filesize = 12M'; \
  echo 'post_max_size = 15M'; \
  echo 'memory_limit = 128M'; \
} > /usr/local/etc/php/conf.d/uploads.ini

RUN sed -ri \
      -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
      /etc/apache2/sites-available/000-default.conf \
      /etc/apache2/apache2.conf \
    && a2enmod rewrite

# === add this block to enable .htaccess ===
RUN printf '\n<Directory /var/www/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' \
  >> /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

RUN docker-php-ext-install pdo pdo_mysql mysqli

EXPOSE 80
CMD ["apache2-foreground"]
