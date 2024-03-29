
# NWWS-OI Controller

This software provides a download client and web dashboard for downloading "products" from NWWS-OI ([NOAA Weather Wire - Open Interface](https://www.nws.noaa.gov/nwws/). NOAA Weather Wire is a satellite data collection and dissemination system operated by the [National Weather Service](http://weather.gov). Its purpose is to provide state and federal government, commercial users, media and private citizens with timely delivery of meteorological, hydrological, climatological and geophysical information.

This client was developed and tested on [Ubuntu 22.04](http://ubuntu.com) (64-bit) and Raspberry Pi OS (64-bit) using the following open-source software stack:

* [Docker](https://www.docker.com/) - Container tool
* [Docker Compose](https://docs.docker.com/compose/) - Multi-container orchestration tool
* [PHP](https://www.php.net/) - Scripting Language
* [Composer](http://getcomposer.org/) - Dependency Manager for PHP
* [Laravel](https://laravel.com/) - PHP Framework
* [Sail](https://laravel.com/docs/10.x/sail) - Laravel Sail command-line interface for Laravel's Docker environment 
* [Livewire](https://laravel-livewire.com/) - Laravel Dynamic Front-end Framework
* [NodeJS](https://nodejs.org/en) - JavaScript runtime environment
* [TailwindCSS](https://tailwindcss.com/) - CSS framework
* [MySQL](https://www.mysql.com/) - Relational Database
* [Redis](https://redis.io/) - Cache
* [Soketi](https://docs.soketi.app/) - Websocket server
* [SliXMPP](https://github.com/poezio/slixmpp) - XMPP client 

## How do I run it?

*Please see the disclaimer below if you wish to run this software on a Raspberry Pi.*

Asuming you already have Docker and Docker Compose [installed](https://github.com/jbuitt/nwwsoi-controller/blob/main/scripts/install_docker.sh), just following the instructions below:

1. First, clone the repo and change directory:

```
git clone https://github.com/jbuitt/nwwsoi-controller.git
cd nwwsoi-controller/
```

2. Copy the desired file you want Docker Compose to use.

```
cp docker-compose-prod.yml docker-compose.yml

# or

cp docker-compose-dev.yml docker-compose.yml   # usually what you want
```

3. Copy the example env file to the file to source in the next step:

```
cp sail.env.example sail.env
```

4. Next, source the env file and build all of the Docker images:

```
source sail.env
docker compose build
```

5. Now, install all the PHP dependencies:

```
docker run --rm --interactive --tty \
  -e WWWUSER=$(id -u) \
  --volume $PWD:/var/www/html \
  nwwsoi-controller:latest \
  composer install -ovn 
```

6. Copy the `.env.example` file to `.env` and make your environment variable changes (documented below).
   
7. Create an Laravel App Key:

```
docker run --rm --interactive --tty \
  -e WWWUSER=$(id -u) \
  --volume $PWD:/var/www/html \
  nwwsoi-controller:latest \
  ./artisan key:generate --force
```

8. Now, install the front-end dependencies:

```
docker run --rm --interactive --tty \
  -e WWWUSER=$(id -u) \
  --volume $PWD:/var/www/html \
  nwwsoi-controller:latest \
  npm i 

docker run --rm --interactive --tty \
  -e WWWUSER=$(id -u) \
  --volume $PWD:/var/www/html \
  nwwsoi-controller:latest \
  npm run build 
```

9. Next, download the other containers and start everything up by running:

```
./vendor/bin/sail up -d
```

10. Now, you migrate and seed the database:

```
./vendor/bin/sail artisan migrate \
   --seed \
   --force
```

11. Create the symbolic link so the web server has access to files in the storage directory:

```
./vendor/bin/sail artisan storage:link
```

12. Finally, you can access the dashboard from a browser by going to [http://127.0.0.1:8080](http://127.0.0.1:8080).

You'll need an admin user to log into the dashboard, create one first by running:

```
./vendor/bin/sail artisan nwwsoi-controller:create_admin_user
```

## Running on a Raspberry Pi

If you would like to run this software on a Raspberry Pi, I recommend using a Raspberry Pi 4 Model B with either 4 or 8 GB of
memory booting from a quality solid-state drive. Running on a Pi Zero, 1, or 3 -or- booting from a MicroSD card is *not*
recommended due to the poor performance you're likely to have.

## .env Environment Variables

The following environment variables will need to be set:

```
APP_KEY=

NWWSOI_USERNAME=
NWWSOI_PASSWORD=
NWWSOI_RESOURCE=
```

Environment Variables in `.env` which requires a value:

* `APP_KEY` - Laravel App Key. Step #5 above will generate a key and populate this variable in your `.env` file.
* `NWWSOI_USERNAME` - Your NWWS-OI username (Get your NWWS-OI credentials [here](https://www.weather.gov/nwws/nwws_oi_request))
* `NWWSOI_PASSWORD` - Your NWWS-OI password (Get your NWWS-OI credentials [here](https://www.weather.gov/nwws/nwws_oi_request))
* `NWWSOI_RESOURCE` - The XMPP Resource ID. This should be unique on the NWWS-OI server. (e.g., `John Doe's NWWS-OI Controller`)

Other variables that can be modified from their default values:

* `APP_NAME` - The name of this app. (Shows up in the browser tab and dashboard)
* `APP_ENV` - The app environment. Typically will want this to be set to `production`.
* `APP_DEBUG` - Debug mode. Typically will want this set to `false`.
* `APP_URL` - The URL of the NWWS-OI Controller application. If running locally, you'll want this set to `http://localhost`.

* `NWWSOI_CONFIG_AUTOSTART` - Flag to specify whether you want the NWWS-OI ingester to run at start up. (e.g., `0` or `1`)
* `NWWSOI_FILE_SAVE_REGEX` - [Regular expression](https://en.wikipedia.org/wiki/Regular_expression) for specifying what types of products you want to save. Default is `.*`, which is all products. Should be surrounded by single quotes. (e.g., `'.*'`)

## Plugins

NWWS-OI Console includes a "plugin" system that gives you the option to take some action when a product is downloaded. For example, you could write a plugin that sends you Tornado Warnings matching a specific Weather Forecast Office via SMS text message.

More to come..

## Author

+	[jbuitt at gmail.com](mailto:jbuitt@gmail.com)

## License

See [LICENSE](https://github.com/jbuitt/emwin-console/blob/main/LICENSE) file.
