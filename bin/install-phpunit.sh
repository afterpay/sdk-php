#!/bin/sh

# Copyright (c) 2020 Afterpay Limited Group
# 
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
# 
#     http://www.apache.org/licenses/LICENSE-2.0
# 
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

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
elif [ $a == '8.0' ]; then
    b=9
else
    # Unsupported PHP version
    exit 1
fi

echo "Installing PHPUnit ${b}..."

if command -v wget &> /dev/null
then
    wget -O ./vendor/bin/phpunit https://phar.phpunit.de/phpunit-$b.phar
elif command -v curl &> /dev/null
then
    curl --silent --show-error --location --output ./vendor/bin/phpunit https://phar.phpunit.de/phpunit-$b.phar
else
    # Unable to download PHPUnit
    exit 2
fi

chmod +x ./vendor/bin/phpunit

echo "Installation complete."
