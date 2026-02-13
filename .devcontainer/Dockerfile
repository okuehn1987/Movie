# [Choice] PHP version (use -bullseye variants on local arm64/Apple Silicon): 8, 8.1, 8.0, 7, 7.4, 7.3, 8-bullseye, 8.1-bullseye, 8.0-bullseye, 7-bullseye, 7.4-bullseye, 7.3-bullseye, 8-buster, 8.1-buster, 8.0-buster, 7-buster, 7.4-buster
ARG VARIANT=8.2-bullseye
FROM mcr.microsoft.com/vscode/devcontainers/php:0-${VARIANT}

RUN rm -f /etc/apt/sources.list.d/yarn.list
# Install MariaDB client
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client \ 
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip \
    libicu-dev \
    libbz2-dev \
    libpng-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libreadline-dev \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libxml2-dev \
    libzip-dev \
    libgmp-dev \
    # libssh2-1-dev \
    libssl-dev \
    zlib1g-dev \
    graphviz \
    poppler-utils \
    ffmpeg \
    g++ \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd 

# Install php-mysql driver
RUN docker-php-ext-install bz2 intl iconv bcmath opcache calendar mbstring pdo_mysql zip gmp mysqli pdo 

RUN docker-php-ext-configure ftp --with-openssl-dir=/usr \
    && docker-php-ext-install ftp

RUN echo -e "\npost_max_size = 2G\nupload_max_filesize = 2G" >> /usr/local/etc/php/php.ini
# RUN echo -e "\nclient_max_body_size 2g" >> /etc/nginx/nginx.conf  TODO: Add this to nginx config but in http{} block

# [Choice] Node.js version: none, lts/*, 16, 14, 12, 10
ARG NODE_VERSION="none"
RUN if [ "${NODE_VERSION}" != "none" ]; then su vscode -c "umask 0002 && . /usr/local/share/nvm/nvm.sh && nvm install ${NODE_VERSION} 2>&1"; fi

## needed for larvael reverb / websockets
RUN docker-php-ext-install pcntl
RUN docker-php-ext-configure pcntl --enable-pcntl

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>" 2>&1

