FROM php:8.3-apache

RUN apt-get update && apt-get install -y libcurl4-openssl-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install curl
RUN a2enmod rewrite
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork
RUN sed -i '/<Directory \/var\/www\/html>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
RUN chmod +x /var/www/html/start.sh

CMD ["/var/www/html/start.sh"]
