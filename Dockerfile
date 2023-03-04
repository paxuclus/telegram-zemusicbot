FROM php:8.2-apache

ENV TELEGRAM_BOT_TOKEN ''
ENV TELEGRAM_BOT_USERNAME ''
ENV TELEGRAM_BOT_WEBHOOK_URL ''

COPY . /usr/src/zemusibot

ENV APACHE_DOCUMENT_ROOT /usr/src/zemusibot

RUN a2enmod rewrite

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
