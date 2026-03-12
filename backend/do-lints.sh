#!/bin/bash

. ./.env

./vendor/bin/parallel-lint --exclude .git --exclude app --exclude vendor .
./vendor/bin/php-cs-fixer fix --dry-run --diff   # just show changes
#  "check" is a shorthand for:
#       ./vendor/bin/php-cs-fixer fix --dry-run --diff


