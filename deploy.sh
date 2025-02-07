#!/bin/sh
set -e

git pull origin main

composer install

php artisan migrate
