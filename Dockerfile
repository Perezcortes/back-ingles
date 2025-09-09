# Imagen oficial de PHP con Apache como base.
FROM php:8.2-apache

# Establece el directorio de trabajo dentro del contenedor.
WORKDIR /var/www/html

# Habilita el módulo de reescritura de Apache para URLs
RUN a2enmod rewrite

# Esta línea es crucial para que funcionen reglas como las de las páginas de error o las reglas de reescritura.
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Instala las extensiones de PHP esenciales para la conectividad a la base de datos.
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copia todo lo que esté en el directorio donde se encuentra el Dockerfile.
COPY . /var/www/html
