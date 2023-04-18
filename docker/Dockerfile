FROM ubuntu:jammy-20230308

SHELL ["/bin/bash", "-euxo", "pipefail", "-c"]

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apt-get update -y; \
    apt-get install -y software-properties-common; \
    add-apt-repository ppa:ondrej/php; \
    apt-get update -y; \
    echo "Europe/Copenhagen" > /etc/timezone; \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        mysql-client \
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
        wget; \
    mkdir -p /var/www/; \
    rm /etc/php/8.0/fpm/pool.d/www.conf; \
    apt-get clean autoclean; \
    apt-get autoremove -y; \
    rm -rf /var/lib/apt/lists/*; \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"; \
    php composer-setup.php; \
    php -r "unlink('composer-setup.php');"; \
    mv composer.phar /usr/bin/composer; \
    addgroup --gid "$GROUP_ID" infosys; \
    adduser --home /var/www --disabled-login --disabled-password --uid "$USER_ID" --gid "$GROUP_ID" infosys

COPY run.sh /usr/local/bin

CMD ["/usr/local/bin/run.sh"]
