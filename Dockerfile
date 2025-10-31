FROM php:8.2-fpm

# Build args
ARG node
ARG supervisor
ARG cron
ARG user
ARG uid

# Set working directory
WORKDIR /var/www/html/

# Install dependencies for the operating system software
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    libzip-dev \
    unzip \
    git \
    libonig-dev \
    curl \
    mariadb-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/* && \
    apt-get update

# Install extensions for php
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Install composer (php package manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN if [ "$user" != "root" ] ; then \
    useradd -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer &&\
    chown -R $user:$user /home/$user; \
    fi

# Enable cron job
RUN if [ $cron ] ; then \
    apt-get -y install cron && \
    echo "* * * * * root cd /var/www/html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/scheduler && \
    chmod 0644 /etc/cron.d/scheduler && \
    crontab /etc/cron.d/scheduler && \
    service cron start; \
    fi

# Enable supervisor
RUN if [ $supervisor ] ; then apt-get install -y supervisor; fi

# Cleaning
RUN apt-get clean && apt-get autoremove -y

USER $user

# Expose port 9000 and start php-fpm server (for FastCGI Process Manager)
EXPOSE 9000
CMD ["php-fpm"]
