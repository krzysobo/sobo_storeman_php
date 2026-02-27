#!/bin/bash

. ./.env

./vendor/bin/parallel-lint --exclude .git --exclude app --exclude vendor .
./vendor/bin/phpcbf . --ignore=vendor
