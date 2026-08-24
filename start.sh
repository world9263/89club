#!/bin/bash
# Disable conflicting MPM modules, keep only prefork (required for mod_php)
a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Set Railway's dynamic PORT
sed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT:-8080}/" /etc/apache2/sites-available/000-default.conf

# Start Apache
apache2-foreground
