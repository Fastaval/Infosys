# InfoSys

This is a system to keep track of as much data as possible
for a role-playing convention. It is based on the needs of
one such convention (Fastaval in Denmark) but might have a
use for a wider audience - hence it's opensource.

## Requirements

PHP 8.0

- xml
- mbstring

Mysql 5.5/5.7

## Docker

To setup the docker environment, go into the docker folder in the root of the repo. Using this you can setup a dev environment fairly simple, you just need to run two docker commands and modify your hosts file. Enter the directory and run the following

```bash
docker build -t infosys .
```

```bash
docker run --name infosys -v $(realpath):/var/www/infosys -v $(pwd)/infosys-fpm.conf:/etc/php/8.0/fpm/pool.d/infosys-fpm.conf -v $(pwd)/infosys-site.conf:/etc/nginx/sites-enabled/infosys-site.conf -v $(pwd)/config.ini:/var/www/infosys/include/config.ini -p 127.0.0.1:8080:80 -d infosys
```

If you're on windows, substitute `$(realpath)` for the full path to the repo, and `$(pwd)` with the full path to the docker folder. Also, if your port 8080 is already in use, you'll need to use a different port on the host.

Remember to add an entry to your hosts file to point infosys.local to 127.0.0.1.

The dev setup comes complete with one admin user with the following credentials:

- username: admin@infosys.local
- password: password

## Contact

If you have any questions, feedback or ideas, you can reach
me on peter.e.lind(a)gmail.com
