#!/bin/bash

a=$( php -r '$a=explode(".",PHP_VERSION);echo$a[0].".".$a[1];' )

if [ $a == '5.6' ]; then
    b=5
elif [ $a == '7.0' ]; then
    b=6
elif [ $a == '7.1' ]; then
    b=7
elif [ $a == '7.2' ]; then
    b=8
elif [ $a == '7.3' ]; then
    b=9
elif [ $a == '7.4' ]; then
    b=9
else
    # Unsupported PHP version
    exit 1
fi

echo "Installing PHPUnit ${b}..."
wget -O ./vendor/bin/phpunit https://phar.phpunit.de/phpunit-$b.phar
chmod +x ./vendor/bin/phpunit
