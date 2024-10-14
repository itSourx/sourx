# Utiliser une image de base PHP avec FPM
FROM php:8.1-fpm

# Installer les dépendances nécessaires pour GD et Sodium
RUN apt-get update && apt-get install -y \
    libgd-dev \
    libsodium-dev \
    && docker-php-ext-install gd sodium

# Installer d'autres extensions nécessaires si besoin
RUN docker-php-ext-install pdo pdo_mysql

# Copier le projet dans le répertoire de travail du container
COPY . /var/www/html

# Exposer le port 80 pour l'application
EXPOSE 80

# Commande pour démarrer PHP-FPM
CMD ["php-fpm"]
