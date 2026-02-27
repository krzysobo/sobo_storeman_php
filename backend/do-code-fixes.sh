#!/bin/bash

. ./.env

./vendor/bin/parallel-lint --exclude .git --exclude app --exclude vendor .

./vendor/bin/php-cs-fixer fix --dry-run --diff   # just show changes
# ./vendor/bin/php-cs-fixer fix                    # apply
./vendor/bin/php-cs-fixer fix         # apply changes - do it locally or in pre-commit hooks.