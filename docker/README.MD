To install infosys into a docker-container, run the following commands:

  docker build -t infosys .
  docker run --name infosys -v C:/Infosys-master/:/var/www/infosys -v C:/Infosys-master/docker/infosys-fpm.conf:/etc/php/5.6/fpm/pool.d/infosys-fpm.conf -v C:/Infosys-master/docker/infosys-site.conf:/etc/nginx/sites-enabled/infosys-site.conf -v C:/Infosys-master/docker/config.ini:/var/www/infosys/include/config.ini -p 127.0.0.1:8080:80 -d infosys

Go to http://infosys.local:8080/ to connect.
In case you get an error message, probably mySQL is not running. To check if this is the case, write
  docker exec -it infosys /bin/bash
  service --status-all
	If needed: service mysql start
	
	