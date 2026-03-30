FROM php:8.2-apache

# เปิดใช้งาน mod_rewrite สำหรับทำ URL สวยๆ (เผื่อต้องใช้)
RUN a2enmod rewrite

# ติดตั้ง Extension สำหรับเชื่อมต่อ MySQL (PDO และ MySQLi)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# เปิดใช้งาน mod_rewrite และ AllowOverride All
RUN a2enmod rewrite && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf