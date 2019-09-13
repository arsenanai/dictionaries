SPA for ENS TRU code management for Samruk Kazyna Kontract

software requirements:
php,
database(postgres),
composer,
laravel,
npm,
yarn

instructions:
composer install
create and fill-in .env file with database connection settings
php artisan migrate
php artisan passport:install
#after every database cleaning and re-migrating run this:
php artisan passport:client --personal
yarn install

optional:
php artisan db:seed

running:
php artisan serve
yarn watch - if changing front-end

deployment on ubuntu server:
https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-application-with-nginx-on-ubuntu-16-04

git clone https://arsenanai@bitbucket.org/arsenanai/enstru.git
composer install
npm install
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

git pull
rm public/js/app.js
rm public/css/app.css

npm run production
php artisan cache:clear
sudo systemctl reload nginx