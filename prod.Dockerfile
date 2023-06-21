FROM laravelphp/vapor:php82
RUN docker-php-ext-install exif
COPY ./php.ini /usr/local/etc/php/conf.d/overrides.ini
COPY . /var/task
