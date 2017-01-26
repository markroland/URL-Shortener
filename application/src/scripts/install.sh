#!/bin/sh

# Note: See application/data/sample-credentials/mysql-user.json for mysql settings
mysql_db="webapp\_main"
mysql_user="webapp"

# Create Database(s)
mysql -e "CREATE DATABASE $mysql_db DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;"

# Put github token in place for composer
# ghtoken=`cat /vagrant/config/dev/composer/github.token`
# composer config --global github-oauth.github.com $ghtoken

# Install Composer dependencies
cd /vagrant/application
/usr/local/bin/composer install --no-dev

# Create User(s) (based on ./vendor/markroland/url-shortener-lib/mysql/sample-user.sql)
mysql -e "GRANT USAGE ON *.* TO '$mysql_user'@'localhost' IDENTIFIED BY PASSWORD '*BF7C27E734F86F28A9386E9759D238AFB863BDE3';"
mysql -e "GRANT ALTER ROUTINE, CREATE ROUTINE, EXECUTE, INSERT, UPDATE PRIVILEGES ON `$mysql_db`.* TO '$mysql_user'@'localhost';"

# Install MySQL schema, stored procedures and sample data from library
mysql -u root webapp_main < /vagrant/application/vendor/markroland/url-shortener-lib/mysql/schema.sql
mysql -u root webapp_main < /vagrant/application/vendor/markroland/url-shortener-lib/mysql/stored-procedures.sql
mysql -u root webapp_main < /vagrant/application/vendor/markroland/url-shortener-lib/mysql/sample-data.sql
