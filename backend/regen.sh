#!/bin/bash
# -- regenerates the app --
. ./.env

composer update 
composer dump-autoload
