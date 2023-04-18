#!/bin/bash

/usr/sbin/service php8.0-fpm start

if [ -f /var/www/infosys/docker/create.sql ]
then
    while ! mysql -u root -proot -h mysql -e "select 1" > /dev/null ; do sleep 1 ; done
    /usr/bin/mysql -u root -h mysql -proot  < /var/www/infosys/docker/create.sql
fi

if [ -f /var/www/infosys/composer.json ]
then
    (cd /var/www/infosys/ && composer i)
fi

shutdown() {
	/usr/sbin/service php8.0-fpm stop
}

trap shutdown INT TERM

while true
do
    sleep 1
done
