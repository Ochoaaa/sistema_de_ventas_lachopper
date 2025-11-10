# Imagen base con Apache y PHP (ajusta la versión si es necesario)
FROM php:8.1-apache

# Instalar extensiones necesarias para conectar a MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite (muchos proyectos PHP lo usan)
RUN a2enmod rewrite

# Copiar los archivos del proyecto al directorio de Apache
# Copiar TODO el contenido de la raíz del proyecto al servidor
COPY . /var/www/html/

# Establecer permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80

# Iniciar Apache en primer plano
CMD ["apache2-foreground"]
