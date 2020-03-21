#SPA for managing goods, works and services nomenclature directories
##software requirements:
- php 7.2,
- postgres v11.6,
- composer latest,
- laravel v5.6,
- npm latest

##instructions:
- after cloning the project, ``cd`` in there
- ``composer install``
- create and fill-in .env file with database connection settings
- ``php artisan migrate``
- ``php artisan passport:install``
##after every database cleaning and re-migrating run this:
- ``php artisan passport:client --password``
- ``npm install``

##optional:
- ``php artisan db:seed``

##running:
- ``php artisan serve``
- ``npm run watch - if changing front-end``

##deployment on ubuntu server:
https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-application-with-nginx-on-ubuntu-16-04

##initial deployment
- ``composer install --optimize-autoloader --no-dev``
- ``npm install``
- ``sudo chgrp -R www-data storage bootstrap/cache``
- ``sudo chmod -R ug+rwx storage bootstrap/cache``
- write generated id and secret to .env variables: PASSPORT_CLIENT_ID PASSPORT_CLIENT_SECRET
``php artisan passport:client --password``

##update existing server
- ``git pull origin master``
- ``rm public/js/app.js``
- ``rm public/css/app.css``
- ``npm run production``
- ``php artisan cache:clear``
- ``sudo systemctl reload nginx``
- ``php artisan passport:install``
- ``php artisan passport:keys``

####to change autocomplete results limit refer to .env.example file

##to initial migration run:
- ``php artisan migrate --path=/database/migrations/2019_09_23_113408_create_settings_table.php``