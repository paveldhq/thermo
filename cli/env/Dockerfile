FROM php:7.4-cli

RUN apt-get update \
    && apt-get install -y \
        git \
        zip \
    && curl -L getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/bin \
    && chmod +x /usr/bin/composer

VOLUME /project

WORKDIR /project/cli

ENTRYPOINT [ "/project/cli/console" ]

CMD ["php", "-a"]
