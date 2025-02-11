FROM php:8.3-cli

# Supported arguments
ARG UID
ARG GID

WORKDIR "/app"

RUN apt-get update && apt-get install -y \
    unzip \
    zip

# Composer installing
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN addgroup --gid ${GID} php
RUN adduser --ingroup php --shell /bin/bash -u ${UID} php_user

USER php_user
