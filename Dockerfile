
FROM php:8.3-apache-bookworm

LABEL maintainer="HostelERP Team"
LABEL description="HostelERP PHP 8.3 Apache Application"


ENV APACHE_DOCUMENT_ROOT=/var/www/html \
    PHP_INI_DIR=/usr/local/etc/php


RUN set -eux; \
    docker-php-ext-install -j$(nproc) mysqli


RUN set -eux; \
    a2enmod rewrite


RUN set -eux; \
    sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html|g' \
        /etc/apache2/sites-available/000-default.conf && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/s|AllowOverride None|AllowOverride All|g' \
        /etc/apache2/apache2.conf


RUN set -eux; \
    { \
        echo 'display_errors=Off'; \
        echo 'display_startup_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/dev/stderr'; \
        echo 'upload_max_filesize=50M'; \
        echo 'post_max_size=50M'; \
        echo 'max_execution_time=60'; \
        echo 'session.save_path=/var/lib/php/sessions'; \
        echo 'session.gc_maxlifetime=3600'; \
    } > "$PHP_INI_DIR/conf.d/99-hostelerp.ini"


RUN set -eux; \
    mkdir -p /var/lib/php/sessions && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chmod 755 /var/lib/php/sessions


WORKDIR /var/www/html


COPY --chown=www-data:www-data . .


EXPOSE 80


HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://localhost/index.php') ? 0 : 1);"


CMD ["apache2-foreground"]
