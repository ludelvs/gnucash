FROM php:7-fpm

RUN apt-get update -y && \
    apt-get install -y libzip-dev zip libpq-dev libpcre3-dev zlib1g-dev libpng-dev curl libcurl4-openssl-dev vim libgmp-dev sqlite3 libsqlite3-dev\
  && docker-php-ext-configure zip --with-libzip \
  && docker-php-ext-install pdo mbstring gd curl gmp

COPY . /var/www/html
COPY ./etc/docker/app/.vimrc /root/.vimrc
COPY ./etc/docker/app/docker-entrypoint.sh /bin/docker-entrypoint.sh
COPY ./etc/docker/app/php-fpm.d/docker.conf /usr/local/etc/php-fpm.d/zzz-docker.conf
COPY ./etc/docker/app/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/zzz-www.conf
COPY ./etc/docker/app/php.ini /usr/local/etc/php/php.ini

VOLUME /var/www/html

CMD ["/bin/docker-entrypoint.sh"]
