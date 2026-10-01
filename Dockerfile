# ============================================================
# AgriConnect — Production Docker Image
# PHP 8.1 + Apache + MySQL extension
# Marketplace-ready: driven entirely by env vars.
# ============================================================
FROM php:8.1-apache

LABEL maintainer="MubeeTech <admin@mubeetech.com.ng>"
LABEL org.opencontainers.image.title="AgriConnect"
LABEL org.opencontainers.image.description="USSD-first agricultural marketplace for Northern Nigeria"
LABEL org.opencontainers.image.version="1.0.0"

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        unzip \
        curl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        xml \
        opcache

# Apache config: enable rewrite + remoteip
RUN a2enmod rewrite headers remoteip

# PHP production config
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'display_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/var/www/html/logs/error.log'; \
        echo 'upload_max_filesize=16M'; \
        echo 'post_max_size=20M'; \
        echo 'date.timezone=Africa/Lagos'; \
    } > /usr/local/etc/php/conf.d/agri.ini

# Apache docroot = public directory (or root — see .htaccess)
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Copy application
WORKDIR /var/www/html
COPY . /var/www/html

# Writable dirs
RUN mkdir -p /var/www/html/logs /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/logs /var/www/html/uploads \
    && chmod -R 775 /var/www/html/logs /var/www/html/uploads

# Default env vars — overridable at deploy time
ENV APP_ENV=production \
    BASE_URL=http://localhost \
    APP_VERSION=1.0.0 \
    AT_USERNAME=sandbox \
    AT_API_KEY=replace_me \
    AT_SENDER_ID=AgriConnect \
    AT_USSD_CODE=*384*0032# \
    DB_HOST=localhost \
    DB_NAME=agriconnect \
    DB_USER=root \
    DB_PASS=secret \
    MAIL_ADMIN_EMAIL=admin@mubeetech.com.ng

EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost/ || exit 1

CMD ["apache2-foreground"]