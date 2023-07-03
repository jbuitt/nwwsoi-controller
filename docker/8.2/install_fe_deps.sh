#!/bin/bash
if [[ ! -d "node_modules" ]];
then
  su - sail -c "cd /var/www/html/ && npm i && npm run build"
fi
