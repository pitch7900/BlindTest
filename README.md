# Blind Test

Make some fun doing music blind test on audio shortcut of 15s

You need an account on Deezer API for developpers [Developpers Deezer](https://developers.deezer.com/) and create an app. [Deezer Apps](https://developers.deezer.com/myapps)

You'll also need an account on Spotify WebAPI developpeur page. See : [Soptify WebAPI](https://developer.spotify.com/web-api)
Following URL should be filled in the App declaration for Spotify :
 - http(s)://yoursiteurl:port/spotify/auth/sources
 - http(s)://yoursiteurl:port/spotify/auth/destinations
 - http(s)://yoursiteurl:port/spotify/me/about.json
 - http(s)://yoursiteurl:port

## 1. Configuration File needed

You'll need to create a /config/.env file with following parameters:

```ini
SITEURL="https://<Your site url>"
DEEZER_APIKEY="Deezer api key"
DEEZER_APISECRETKEY="Deezer secret key"
SPOTIFY_APIKEY="Spotify api key"
SPOTIFY_APISECRETKEY="Spotify secret key"
```

If "/config" directory and .env files are missing, then a configuration interface will appear (See chapter 2)

## 2. If the configuration file has not been created

A menu will pop up to help you fill the basic informations need to allow the app to run

## 3. Installation

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

## 4. Docker

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

## 5. gulp installation

```bash
npm install gulp --save-dev
npm install -g gulp-cli
npm install gulp-connect-php
npm install browser-sync
```

## 6. Credits

- Throttler : https://github.com/hamburgscleanest/guzzle-advanced-throttle
- Deezer Wrapper : https://github.com/mbuonomo/Deezer-API-PHP-class/
- Seconds to HMS in twig : https://caffeinecreations.ca/blog/twig-macro-convert-seconds-to-hhmmss/
- Logger (monolog) : https://packagist.org/packages/monolog/monolog, https://github.com/Seldaek/monolog