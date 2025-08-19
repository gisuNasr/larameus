FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/larameus

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libcurl4-openssl-dev \
    wget \
    zip \
    apt-utils \
    libzip-dev \
    libxml2-dev \
    libssh-dev \
    git \
    supervisor \
    cron \
    zlib1g-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxpm-dev \
    libfreetype6-dev \
    iputils-ping \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-xpm \
    && docker-php-ext-install \
        curl \
        bcmath \
        zip \
        iconv \
        xml \
        soap \
        pcntl \
        pdo \
        pdo_mysql \
        simplexml \
        gd \
    && pecl install apcu \
    && docker-php-ext-enable apcu


# Copy Supervisor config
COPY Supervisor/larameus.conf /etc/supervisor/conf.d/

# Set timezone
ENV TZ=Asia/Tehran
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && printf '[PHP]\ndate.timezone="%s"\n' "$TZ" > /usr/local/etc/php/conf.d/tzone.ini

# Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer