#!/bin/sh

####
#
# Provision a Vagrant VM
#
####

# Get Package Manager(s)

# Use Webtatic (https://webtatic.com)
rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm

yum install -y git

####################################################################################################
# MYSQL
####################################################################################################

# Remove default installation
yum remove -y mysql-libs

# Install
yum install -y mysql55w
yum install -y mysql55w-server
# yum install -y mysql55w-devel # Necessary?

# Add to Boot
chkconfig mysqld on

# Start
/sbin/service mysqld start

####################################################################################################
# Apache (httpd)
####################################################################################################

# Install
yum install -y httpd

# Add to Boot
chkconfig httpd on

# Config for site
cp -f /vagrant/config/httpd/httpd.conf /etc/httpd/conf/httpd.conf
cp /vagrant/config/httpd/virtualhost.conf /etc/httpd/conf/virtualhost.conf

####################################################################################################
# PHP
####################################################################################################

# Install
yum install -y php71w

# Install PHP modules
yum install -y php71w-cli
yum install -y php71w-mysql
yum install -y php71w-pdo

# Composer (1.3.1)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '55d6ead61b29c7bdee5cccfb50076874187bd9f21f65d8991d46ec5cc90518f447387fb9f76ebae1fbbacf329e583e30') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer

####################################################################################################
# START SERVICES
####################################################################################################
/sbin/service httpd start
