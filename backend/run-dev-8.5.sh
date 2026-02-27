#!/bin/bash

. ./.env


PORT_DEFAULT=8080

if [ "$1" != "" ];then
  PORT="$1"
  echo "==> Port provided: $PORT"
else
  PORT="$PORT_DEFAULT"
  echo "==> PORT not provided. Using the default port: $PORT_DEFAULT"
fi

php8.5 -S 0.0.0.0:"$PORT" ./public/index.php
