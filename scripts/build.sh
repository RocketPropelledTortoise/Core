#!/usr/bin/env bash

G="\\033[1;32m" #Green
N="\\033[0;39m" #Back to normal

echo -e "$G--- Linting files$N"
find ../src -name "*.php" -print0 | xargs -0 -n1 -P8 php -l
find ../tests -name "*.php" -print0 | xargs -0 -n1 -P8 php -l

echo -e "$G--- Copy Paste detector$N" 
phpcpd ../src

echo -e "$G--- Checkstyle$N" 
#phpcbf --standard=PSR2 ../src 
phpcs --standard=PSR2 --ignore="migrations" ../src 

echo -e "$G--- Mess detector$N" 
phpmd ../src text phpmd.xml --exclude "migrations"