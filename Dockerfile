FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP curl extension
RUN docker-php-ext-install curl

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/html>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Startup script that sets PORT at runtime
RUN echo '#!/bin/bash\nsed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf\nsed -i "s/:80/:${PORT:-8080}/" /etc/apache2/sites-available/000-default.conf\napache2-foreground' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]
