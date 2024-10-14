
# Utilise une image de base PHP
FROM php:8.1-fpm

# Installe les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Définit le répertoire de travail
WORKDIR /app

# Copie le fichier composer.json et composer.lock
COPY composer.json composer.lock ./

# Installe les dépendances PHP
RUN composer install

# Copie le reste de l'application
COPY . .

# Commande par défaut pour démarrer l'application
CMD ["php-fpm"]
