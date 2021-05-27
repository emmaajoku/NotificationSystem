FROM php:7.4-fpm

ENV DEBIAN_FRONTEND noninteractive

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libxslt-dev \
    zip \
    unzip \
	vim \
	wget \
	cron \
  	nodejs \
  	npm \
    netcat \
    acl \
    redis-tools \
	default-mysql-client \
	bash-completion \
    nano

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure \
  	gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/; \
  	docker-php-ext-install \
  	gd \
  	bcmath \
  	intl \
  	mbstring \
    pdo \
  	pdo_mysql \
  	soap \
  	xsl \
  	exif \
    pcntl \
  	sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1005 -d /home/skg skg
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www/html

USER $user


EXPOSE 80
