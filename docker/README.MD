# Usage

Running

```bash
UID=${UID} GID=${GID} docker-compose up -d
```

Stopping
```bash
docker-compose down
```

## Rebuilding

If you modify ./docker/Dockerfile or ./docker/run.sh, then you'll need to rebuild the Infosys image. If docker-compose doesn't do that by itself, run

```bash
docker-compose up -d --force-recreate --build
```

Go to <http://infosys.localhost:8080/> to connect.
