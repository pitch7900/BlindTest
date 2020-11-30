# Blind Test

Make some fun doing music blind test on music extracts of 30 seconds
All music are streamed from the Deezer previews system (open).
All links for Deezer's playlist or songs are reachable during the game.

## Prerequisites

- A mail server
- Google Recaptcha API key registration for your domain
- Mysql/MariaDB Database server
- Creating the database using the /databases/blindtest.sql scripts
- Apache2
- php >=7.2
- php lib curl-php

## Installation

Download the project from github, remove the vendor folder (if needed) and reinstall composer packages

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

## Configuration

A configuration file should be created in /config/.env
The file should be readable by the service account running apache (usualy www-data on linux/ubuntu Systems)
This file should contain DB access credential like following exemple

A Google cloud API key for ReCaptcha is needed : <https://developers.google.com/recaptcha/docs/v3>

When the REGISTRATION_REQUIRE_APPROVAL is set to "true", all registrations are submitted to the address set in REGISTRATION_ADMIN_EMAIL for approval.

```ini
SQL_HOST = "localhost"
SQL_PORT = 3306
SQL_DATABASE = "SQL_DB_NAME"
SQL_USERNAME = "SQL_USERNAME"
SQL_PASSWORD = "SQL_PASSWORD"
SQL_CHARSET = "latin1"
SQL_COLLATION = "latin1_swedish_ci"
PUBLIC_HOST = "https://whatever.domain.com"
SMTP_SERVER = "mail.domain.com"
SMTP_PORT = "457"
SMTP_USERNAME = "webmaster@domain.com"
SMTP_PASSSWORD = "password"
SMTP_MAILFROM = "webmaster@domain.com"
SMTP_USESSL = "true"
SMTP_USEAUTH = "true"
REGISTRATION_REQUIRE_APPROVAL = "true"
REGISTRATION_ADMIN_EMAIL = "admin.email@domain.com"
GOOGLE_RECAPTCHA_SITE_KEY="TOBEFILLED_WITH_VALID_KEY"
GOOGLE_RECAPTCHA_SECRET_KEY="TOBEFILLED_WITH_VALID_SECRET"
````

## Docker

Section to be finished and tested...

For a build under docker see folder /Docker and run the startup.sh.

It will download the git project, recreate the vendor from composer and package everything for a docker image ready to run

```bash
#!/bin/bash
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
npm install -g gulp-cli --save
npm install gulp-connect-php --save
npm install browser-sync --save
npm install safer-buffer --save
```

## Credits

- Throttler : <https://github.com/hamburgscleanest/guzzle-advanced-throttle>
- Deezer Wrapper : <https://github.com/mbuonomo/Deezer-API-PHP-class/>
- Google Recaptcha : <https://developers.google.com/recaptcha/docs/v3>
