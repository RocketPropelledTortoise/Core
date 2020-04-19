#!/usr/bin/env bash

if [ ! -d build ]; then
  mkdir build;
fi

wget -O build/phpunit https://phar.phpunit.de/phpunit-8.phar
chmod +x build/phpunit

wget -O build/phpcs https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.5.5/phpcs.phar
chmod +x build/phpcs

wget -O build/phpcbf https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.5.5/phpcbf.phar
chmod +x build/phpcbf
