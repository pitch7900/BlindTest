# Blind Test

Make some fun doing music blind test on audio shortcut of 15s

You need an account on Deezer API for developpers [Developpers Deezer](https://developers.deezer.com/) and create an app. [Deezer Apps](https://developers.deezer.com/myapps)

You'll also need an account on Spotify WebAPI developpeur page. See : [Soptify WebAPI](https://developer.spotify.com/web-api)
Following URL should be filled in the App declaration for Spotify :

- http(s)://yoursiteurl:port/spotify/auth/sources
- http(s)://yoursiteurl:port/spotify/auth/destinations
- http(s)://yoursiteurl:port/spotify/me/about.json
- http(s)://yoursiteurl:port

## Configuration File needed

You'll need to create a /config/.env file with following parameters:

```ini
SITEURL="http(s)://<yoursiteurl:port>"
playlistsids="1913917022, 7708037842,7821141762, 789794642, 248297032, 878989033, 1470022445, 867825522, 620264073, 1413309725, 1977689462, 1724212365, 745674991, 2021626162, 2159765062, 6122298184, 4676818664, 6030306984, 1728093421, 6200785264, 5782150322, 6080610264, 1057779131, 5014738124, 2004964442, 1913763402, 1419215845, 1950632062, 1045800791, 4135818362, 5922972724, 1276784581, 4135981802, 1437011185, 1471284255, 715215865, 5714797982, 1405240385, 2322259622, 735402575, 6122724024, 5337198442, 1950353862, 5958115324, 6126745404, 1294679255"

```

## Installation

Download the project from github,remove the vendor folder and reinstall composer packages

```bash
git clone https://github.com/pitch7900/BlindTest.git
cd BlindTest
rm -rf vendor
composer install
```

and setup your webserver configuration to point to the /public folder.

For example, the project is downloaded to /var/www/BlindTest and the virtual host points to /var/www/BlindTest/public

```ApacheConf
<VirtualHost *:80>
        RewriteEngine On
        ServerName  <WEBSERVERNAME>
        ServerAlias <WEBSERVERNAME>
        Header set Access-Control-Allow-Origin "*"
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/BlindTest/public

        ErrorLog ${APACHE_LOG_DIR}/BlindTest-error.log
        CustomLog ${APACHE_LOG_DIR}/BlindTest-access.log combined
        LogLevel alert rewrite:trace6

        <Directory "/var/www/BlindTest/public">
                Options Indexes FollowSymLinks
                AllowOverride All
        </Directory>
</VirtualHost>
```

## Docker

For a build under docker see folder /Docker and run the startup.sh.

It will download the git project, recreate the vendor from composer and package everything for a docker image ready to run

```bash
#!/bin/bash
#apt-get install -y composer git libapache2-mod-php php-mbstring
git clone https://github.com/pitch7900/BlindTest.git
rm -rf ./BlindTest/vendor
mv BlindTest BlindTest
cd BlindTest
composer install
cd ..
tar -czf BlindTest.tar.gz BlindTest
docker build .
echo "Start now the container with : docker-compose up --build"
```

## gulp installation

```bash
npm install gulp --save-dev
npm install -g gulp-cli
npm install gulp-connect-php
npm install browser-sync
npm install safer-buffer
```

## Credits

- Throttler : https://github.com/hamburgscleanest/guzzle-advanced-throttle
- Deezer Wrapper : https://github.com/mbuonomo/Deezer-API-PHP-class/
- Seconds to HMS in twig : https://caffeinecreations.ca/blog/twig-macro-convert-seconds-to-hhmmss/
- Logger (monolog) : https://packagist.org/packages/monolog/monolog, https://github.com/Seldaek/monolog