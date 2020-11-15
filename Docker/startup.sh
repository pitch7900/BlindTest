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
echo "Start now the container with : docker-compose up" 
