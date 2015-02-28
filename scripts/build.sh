#!/usr/bin/env bash

G="\\033[1;32m" #Green
N="\\033[0;39m" #Back to normal

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd ) # current directory

echo -e "$G--- Linting files$N"
find $DIR/../src -name "*.php" -print0 | xargs -0 -n1 -P8 php -l
find $DIR/../tests -name "*.php" -print0 | xargs -0 -n1 -P8 php -l

echo -e "$G--- Copy Paste detector$N"
phpcpd $DIR/../src

echo -e "$G--- Autofixing Code Style$N"
phpcbf --standard=PSR2 $DIR/../src

echo -e "$G--- Checkstyle$N"
phpcs --standard=PSR2 --ignore="migrations" $DIR/../src

echo -e "$G--- Mess detector$N"
phpmd $DIR/../src text $DIR/phpmd.xml --exclude "migrations"
