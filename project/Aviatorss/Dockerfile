FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libldap2-dev \
    libxml2-dev \
    libonig-dev \
    chromium \
    fonts-liberation \
    libnss3 \
    libgbm1 \
    libatk-bridge2.0-0 \
    libgtk-3-0 \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        ldap \
        zip \
        bcmath \
        mbstring \
        opcache \
        gd \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV HOME=/tmp
ENV PUPPETEER_CACHE_DIR=/tmp/puppeteer-cache

RUN echo "upload_max_filesize = 32M\npost_max_size = 40M\nmemory_limit = 256M" \
    > /usr/local/etc/php/conf.d/99-aviator.ini

WORKDIR /var/www/html

CMD ["php-fpm"]
