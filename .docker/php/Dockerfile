FROM php:8.1-fpm

ARG XDEBUG_VERSION=3.1.3

RUN apt-get update -yqq \
    && apt-get install -yqq --no-install-recommends curl apt-utils debconf apt-transport-https \
    build-essential locales acl wget zip unzip \
;

RUN docker-php-ext-install \
  mysqli \
  pdo_mysql

RUN apt -qy install $PHPIZE_DEPS \
    && pecl install xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/php.ini
COPY php-fpm-pool.conf /usr/local/etc/php/pool.d/www.conf
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN groupadd dev -g 999
RUN useradd dev -g dev -d /home/dev -m
RUN passwd -d dev

RUN rm -rf /var/lib/apt/lists/*
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "es_ES.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN echo "dev ALL=(ALL) ALL" > /etc/sudoers

COPY init.sh /opt/aos/init.sh

WORKDIR /home/wwwroot/

EXPOSE 9000
# EXPOSE 9003
CMD ["php-fpm"]

ENTRYPOINT ["/opt/aos/init.sh"]