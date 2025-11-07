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

# COPIA Y HABILITA LA CONFIGURACIÓN DE SWAGGER AQUI
# 1. Copia el archivo .conf al directorio de configuración de Apache
COPY apache-swagger.conf /etc/apache2/conf-available/

# 2. Habilita la configuración de Swagger
RUN a2enconf apache-swagger


COPY . /var/www/html

