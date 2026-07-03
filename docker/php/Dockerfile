FROM debian:bookworm
ARG USER_UID=1000
ARG DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN adduser --disabled-password --gecos "" --uid ${USER_UID} user && \
    apt-get update && apt-get install -y --no-install-recommends curl wget rsync apt-transport-https lsb-release ca-certificates software-properties-common gnupg git unzip postgresql-client && \
    mkdir -p /etc/apt/keyrings && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" > /etc/apt/sources.list.d/nodesource.list && \
    curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /etc/apt/keyrings/php.gpg && \
    sh -c 'echo "deb [signed-by=/etc/apt/keyrings/php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' && \
    apt-get update && \
    apt-get install -y \
        php8.3-fpm \
        php8.3-gd \
        php8.3-xml \
        php8.3-bcmath \
        php8.3-cli \
        php8.3-xsl \
        php8.3-mbstring \
        php8.3-zip \
        php8.3-pgsql \
        php8.3-memcached \
        php8.3-curl \
        php8.3-xdebug \
        libzip-dev \
        zip \
        nodejs \
        msmtp && \
    mkdir -p /run/php && \
    rm -f /var/log/php8.3-fpm.log && ln -s /dev/stderr /var/log/php8.3-fpm.log && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

RUN echo alias composer=\'php8.3 /usr/local/bin/composer\' >> /home/user/.bashrc

WORKDIR /var/www/

COPY .msmtprc /home/user/.msmtprc
RUN chown user:user /home/user/.msmtprc
RUN chmod 600 /home/user/.msmtprc

EXPOSE 9000
CMD ["/usr/sbin/php-fpm8.3", "--nodaemonize", "--fpm-config", "/etc/php/8.3/fpm/php-fpm.conf"]
