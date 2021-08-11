# Vue.js SPA for managing dictionaries
## software requirements:
- php minimal version 7.2,
- some database,
- composer latest,
- laravel v5.6,
- npm latest

## steps to install:
- after cloning the project, ``cd`` in there
- ``composer install``
- create and fill-in .env file with database connection settings
- ``php artisan migrate``
- ``php artisan db:seed``
- ``php artisan passport:client --password``
- copy generated id and secret to .env variables: PASSPORT_CLIENT_ID PASSPORT_CLIENT_SECRET
- ``npm run production``
- finally start server by:
- ``php artisan serve``