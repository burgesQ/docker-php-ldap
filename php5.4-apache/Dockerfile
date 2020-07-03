# See https://github.com/docker-library/php/blob/master/7.1/fpm/Dockerfile
FROM php:5.4-apache

MAINTAINER Quentin BURGESS <quentin.burgess@gmail.com>

# Install needed php extensions: ldap
RUN \
    apt-get update && \
    apt-get install libldap2-dev less ldap-utils git -y && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
    docker-php-ext-install ldap

WORKDIR /var/www/html
