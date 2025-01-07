FROM tangramor/nginx-php8-fpm:php8.2.8_node20.5.0

RUN apk update

RUN apk add poppler-utils

#make the artisan scheduler and cron running
RUN echo -e "\n* * * * * su nginx -s /bin/sh -c 'cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1'" >> /var/spool/cron/crontabs/root
RUN echo -e "[program:cron]\ncommand=crond -f\nautostart=true\nautorestart=true" > /etc/supervisor/conf.d/cron.conf

# install extension needed for laravel reverb
RUN docker-php-ext-install calendar && docker-php-ext-configure calendar --enable-calendar

COPY . /var/www/html
RUN rm -rf /var/www/html/database/database.sqlite 
RUN touch /var/www/html/database/database.sqlite
RUN composer install && cp .env.testing .env && php artisan key:generate && php artisan migrate --seed --seeder=DatabaseSeederE2E
RUN npm ci && npm run build
ENTRYPOINT bash -c "php artisan serve | head -n 3"