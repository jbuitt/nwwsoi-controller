#!/bin/bash
if [[ ! -d "vendor" ]];
then
  su - sail -c "cd /var/www/html/ && composer install -ovn"
fi
