FROM php:8.3-fpm-alpine

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    curl-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libtool \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    && apk add --no-cache \
    curl \
    freetype \
    icu \
    icu-dev \
    libjpeg-turbo \
    libwebp-dev \
    libzip \
    postgresql-client \
    nano \
    nginx \
    shadow \
    supervisor \
    zip \
    && pecl install \
    pcov \
    redis \
    && docker-php-ext-enable \
    pcov \
    redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure zip \
    && docker-php-ext-install \
    bcmath \
    curl \
    gd \
    intl \
    opcache \
    pcntl \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    xml \
    zip \
    && curl -s https://getcomposer.org/installer | \
       php -- --install-dir=/usr/local/bin/ --filename=composer \
    && apk del -f .build-deps \
    && rm -rf /tmp/pear

COPY config/nginx.conf /etc/nginx/nginx.conf
COPY config/fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY config/supervisord.conf /etc/supervisord/conf.d/supervisord.conf
COPY config/entrypoint.sh /entrypoint.sh

# Configure non-root user.
ARG PUID=1000
ENV PUID ${PUID}
ARG PGID=1000
ENV PGID ${PGID}

RUN groupmod -o -g ${PGID} nobody && \
    usermod -o -u ${PUID} -g nobody nobody && \
    apk del shadow

RUN chown -R nobody.nobody /run && \
    chown -R nobody.nobody /etc/nginx && \
    chown -R nobody.nobody /var/lib/nginx && \
    chown -R nobody.nobody /var/log/nginx && \
    chmod +x /entrypoint.sh && \
    rm -rf /etc/nginx/conf.d/default.conf

# PHP Tinker directory permission
RUN mkdir /.config && \
    chown -R nobody.nobody /.config

USER nobody

WORKDIR /var/www/html

EXPOSE 8080

CMD ["/entrypoint.sh"]

HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping
