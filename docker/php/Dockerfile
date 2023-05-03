FROM debian:buster
ARG USER_UID=1000
ARG DEBIAN_FRONTEND=noninteractive
RUN adduser --disabled-password --gecos "" --uid ${USER_UID} user && \
    apt-get update && apt-get install -y curl wget rsync apt-transport-https lsb-release ca-certificates software-properties-common && \
    curl -sL https://deb.nodesource.com/setup_14.x | bash - && \
    wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' && \
    apt-get update && \
    apt-get install -y \
        php8.1-fpm \
        php8.1-gd \
        php8.1-xml \
        php8.1-bcmath \
        php8.1-cli \
        php8.1-xsl \
        php8.1-mbstring \
        php8.1-zip \
        php8.1-pgsql \
        php-memcached \
        php-curl \
        libzip-dev \
        zip \
        nodejs \
        msmtp && \
    mkdir -p /run/php && \
    rm -f /var/log/php8.1-fpm.log && ln -s /dev/stderr /var/log/php8.1-fpm.log

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

RUN echo alias composer=\'php8.1 /usr/local/bin/composer\' >> /home/user/.bashrc

WORKDIR /var/www/

COPY .msmtprc /home/user/.msmtprc
RUN chown user:user /home/user/.msmtprc
RUN chmod 600 /home/user/.msmtprc

EXPOSE 9000
ENTRYPOINT ["/usr/sbin/php-fpm8.1", "--nodaemonize", "--fpm-config", "/etc/php/8.1/fpm/php-fpm.conf"]
