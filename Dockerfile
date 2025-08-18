# Gunakan image resmi PHP + Apache
FROM php:8.2-apache

# Install ekstensi mysqli (untuk koneksi MySQL)
RUN docker-php-ext-install mysqli

# Salin semua file ke folder web server
COPY . /var/www/html/

# Atur permission (opsional)
RUN chown -R www-data:www-data /var/www/html
