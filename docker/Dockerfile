FROM ubuntu:18.04

RUN apt update \
    && apt install -y software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt update \
    && echo "Europe/Copenhagen" > /etc/timezone \
    && DEBIAN_FRONTEND=noninteractive apt install -y \
    nginx-full \
    php8.0-cli \
    php8.0-curl \
    php8.0-fpm \
    php8.0-gd \
    php8.0-mbstring \
    php8.0-mysql \
    php8.0-opcache \
    php8.0-sqlite3 \
    php8.0-tidy \
    php8.0-xml \
    php8.0-zip \
    mysql-server \
    wget

RUN mkdir -p /var/www/ \
    && rm /etc/php/8.0/fpm/pool.d/www.conf \
    && rm /etc/nginx/sites-enabled/default

COPY run.sh /usr/local/bin
COPY composer-installer.sh /usr/local/bin/
COPY create.sql /var/www/

CMD ["/usr/local/bin/run.sh"]
