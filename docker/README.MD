# Run container

To install infosys into a docker-container, run the following commands:

```bash
docker build -t infosys .
```

```bash
docker run --name infosys -v C:/git/Infosys/:/var/www/infosys -v C:/git/Infosys/docker/infosys-fpm.conf:/etc/php/8.0/fpm/pool.d/infosys-fpm.conf -v C:/git/Infosys/docker/infosys-site.conf:/etc/nginx/sites-enabled/infosys-site.conf -v C:/git/Infosys/docker/config.ini:/var/www/infosys/include/config.ini -p 127.0.0.1:8080:80 -d infosys
```

Go to <http://infosys.local:8080/> to connect.
In case you get an error message, probably mySQL is not running. To check if this is the case, write

```bash
docker exec -it infosys /bin/bash
service --status-all
```

If needed, restart MySQL with:

```bash
service mysql start
```
