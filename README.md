# Blind Test

Make some fun doing music blind test on audio shortcut of 15s

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