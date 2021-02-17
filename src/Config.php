<?php

/**
 * @copyright Copyright (c) 2020-2021 Afterpay Corporate Services Pty Ltd
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Afterpay\SDK;

use Afterpay\SDK\Exception\InvalidArgumentException;
use Afterpay\SDK\Exception\ParsingException;

final class Config
{
    /**
     * @var array $data
     */
    private static $data = [
        /**
         * These are properties of a merchant account.
         *
         * @todo Move these into the MerchantAccount class.
         * @todo Allow more than 1 MerchantAccount to be configured for the SDK.
         *       This may require a set of rules to determine which account to use in which scenarios,
         *       or the application could just choose the appropriate account using its own logic.
         */
        'merchantId' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'secretKey' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'countryCode' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'apiEnvironment' => [
            'type' => 'enumi',
            'options' => [
                'sandbox',
                'production'
            ],
            'default' => 'sandbox',
            'src' => '.env.php'
        ],
        /**
         * These are database settings.
         */
        'db.api' => [
            'type' => 'enum',
            'options' => [
                'mysqli'
            ],
            'src' => '.env.php'
        ],
        'db.host' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'db.port' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'db.database' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'db.tablePrefix' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'db.user' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'db.pass' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        /**
         * These are test settings - used only by the Test classes for automated testing.
         */
        'test.consumerEmail' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        'test.consumerPassword' => [
            'type' => 'string',
            'src' => '.env.php'
        ],
        /**
         * This is where the contents of the SDK's composer.json file will be loaded.
         */
        'composerJson' => [
            'type' => 'object',
            'src' => 'composer.json'
        ]
    ];

    /**
     * @var bool $envConfigLoaded
     */
    private static $envConfigLoaded = false;

    /**
     * @var bool $composerJsonLoaded
     */
    private static $composerJsonLoaded = false;

    /**
     * Multi-level dirname support for PHP 5.6.
     * (The $levels argument was introduced in PHP 7.0.)
     *
     * @param string $path
     * @param int $levels
     * @return string
     */
    private static function dirname($path, $levels = 1)
    {
        if ($levels > 1) {
            return dirname(self::dirname($path, --$levels));
        } else {
            return dirname($path);
        }
    }

    /**
     * @param mixed $needle
     * @param array $haystack
     * @param bool $strict
     * @return bool
     * @see https://www.php.net/manual/en/function.in-array.php#89256
     */
    private static function inArrayCaseInsensitive($needle, $haystack, $strict = false)
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack), $strict);
    }

    /**
     * Convert lower camel dot notation to allcap snake case.
     *
     * Note: This operation is irreversible.
     *
     * For example:
     *    "db.tablePrefix" --> "DB_TABLE_PREFIX"
     *
     * @param string $str
     * @return string
     */
    private static function lowerCamelDotNotationToAllcapSnakeCase($str)
    {
        return strtoupper(implode('_', preg_split('/\.|(?=[A-Z])/', $str)));
    }

    /**
     * @throws \Afterpay\SDK\Exception\ParsingException
     */
    private static function loadEnvConfig()
    {
        $package_installation_path = self::dirname(__FILE__, 2);
        $env_php_path = "{$package_installation_path}/.env.php";

        if (file_exists($env_php_path)) {
            include $env_php_path;

            if (is_array($afterpay_sdk_env_config)) {
                foreach ($afterpay_sdk_env_config as $property => $value) {
                    if (array_key_exists($property, self::$data)) {
                        self::set($property, $value);
                    } else {
                        throw new ParsingException("Unexpected property '{$property}' found in '.env.php' configuration file");
                    }
                }
            } else {
                throw new ParsingException('Failed to parse \'.env.php\' configuration file');
            }
        } else {
            foreach (self::$data as $property => $data) {
                if ($data[ 'src' ] == '.env.php') {
                    $value = getenv(self::lowerCamelDotNotationToAllcapSnakeCase($property));

                    if ($value !== false) {
                        self::set($property, $value);
                    }
                }
            }
        }

        self::$envConfigLoaded = true;
    }

    /**
     * @throws \Afterpay\SDK\Exception\ParsingException
     */
    private static function loadComposerJson()
    {
        $package_installation_path = self::dirname(__FILE__, 2);
        $composer_json_path = "{$package_installation_path}/composer.json";

        if (file_exists($composer_json_path)) {
            $composerJson = json_decode(file_get_contents($composer_json_path));

            if (is_null($composerJson)) {
                throw new ParsingException(json_last_error_msg(), json_last_error());
            }

            self::set('composerJson', $composerJson);
        } else {
            throw new ParsingException('Unable to locate \'composer.json\' configuration file');
        }

        self::$composerJsonLoaded = true;
    }

    /**
     * @param string $property
     * @param mixed $value
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public static function set($property, $value)
    {
        if (! is_string($property)) {
            throw new InvalidArgumentException('Expected string for $property; ' . gettype($property) . ' given');
        }

        if (! array_key_exists($property, self::$data)) {
            throw new InvalidArgumentException("Invalid string given for \$property: '{$property}'");
        }

        if (self::$data[ $property ][ 'type' ] == 'enum') {
            if (! in_array($value, self::$data[ $property ][ 'options' ])) {
                throw new InvalidArgumentException("Unexpected value '{$value}' for '{$property}' given; expected one of '" . implode("', '", self::$data[ $property ][ 'options' ]) . "'");
            }
        } elseif (self::$data[ $property ][ 'type' ] == 'enumi') {
            if (! self::inArrayCaseInsensitive($value, self::$data[ $property ][ 'options' ])) {
                throw new InvalidArgumentException("Unexpected value '{$value}' for '{$property}' given; expected one of '" . implode("', '", self::$data[ $property ][ 'options' ]) . "' (case insensitive)");
            }
        } else {
            if (gettype($value) != self::$data[ $property ][ 'type' ]) {
                throw new InvalidArgumentException('Expected ' . self::$data[ $property ][ 'type' ] . " for \$value of '{$property}'; " . gettype($value) . ' given');
            }
        }

        self::$data[ $property ][ 'value' ] = $value;
    }

    /**
     * @param string $property
     * @return mixed
     */
    public static function get($property)
    {
        if (array_key_exists($property, self::$data)) {
            if (self::$data[ $property ][ 'src' ] == '.env.php') {
                if (! self::$envConfigLoaded) {
                    self::loadEnvConfig();
                }
            } elseif (self::$data[ $property ][ 'src' ] == 'composer.json') {
                if (! self::$composerJsonLoaded) {
                    self::loadComposerJson();
                }
            }

            if (array_key_exists('value', self::$data[ $property ])) {
                return self::$data[ $property ][ 'value' ];
            } elseif (array_key_exists('default', self::$data[ $property ])) {
                return self::$data[ $property ][ 'default' ];
            }
        }

        return null;
    }
}
