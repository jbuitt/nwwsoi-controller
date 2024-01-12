#!/bin/bash
APP_ENV=$(grep APP_ENV .env | awk -F= '{print $2}')
#echo \$APP_ENV = $APP_ENV
if [[ ${APP_ENV} == "local" ]]; then
   source sail.env
   npm run dev
fi
