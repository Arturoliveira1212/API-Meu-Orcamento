FROM php:8.2-apache

# Instala extensões e dependências do sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mysqli

# Ativa o mod_rewrite do Apache
RUN a2enmod rewrite

# Copia a configuração customizada do Apache
COPY ./docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Ajusta permissões (opcional, mas recomendado)
RUN chown -R www-data:www-data /var/www/html

# Define o diretório de trabalho
WORKDIR /var/www/html

# Expõe a porta padrão do Apache
EXPOSE 80
