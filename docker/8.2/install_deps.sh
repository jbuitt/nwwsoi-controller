#!/bin/bash
if [[ ! -d "vendor" ]];
then
  su - sail -c "cd /var/www/html/ && composer install -ovn"
fi
if [[ ! -d "node_modules" ]];
then
  su - sail -c "cd /var/www/html/ && npm i && npm run build"
fi
