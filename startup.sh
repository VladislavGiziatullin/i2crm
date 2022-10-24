#!/bin/sh

composer install;
docker-compose up -d;
symfony console doctrine:migrations:migrate;
symfony server:start -d;