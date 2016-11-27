FROM php:5.6-cli

WORKDIR /tmp

RUN apt-get update && apt-get install git zip php5-mcrypt -y && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer && \
    composer global require phpro/grumphp && \
    composer global update phpro/grumphp && \
    echo 'date.timezone = America/Sao_Paulo' > /usr/local/etc/php/conf.d/php.ini && \
    echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/php.ini && \
    echo 'extension=/usr/lib/php5/20131226/mcrypt.so' >> /usr/local/etc/php/conf.d/php.ini && \
    apt-get clean && apt-get autoremove -y

WORKDIR /app

ENTRYPOINT ["/root/.composer/vendor/bin/grumphp"]
CMD ["help"]
