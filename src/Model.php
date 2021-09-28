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

class Model implements \JsonSerializable
{
    use \Afterpay\SDK\Shared\ModelMethods;

    private static $stringTruncationEnabled = false;
    private static $automaticFormattingEnabled = true;
    private static $automaticValidationEnabled = false;

    const SIGNED_32BIT_INT_MIN = -2147483647 - 1;
    const SIGNED_32BIT_INT_MAX = 2147483647;

    /**
     * @return bool
     */
    public static function getStringTruncationEnabled()
    {
        return self::$stringTruncationEnabled;
    }

    /**
     * @param bool $stringTruncationEnabled
     */
    public static function setStringTruncationEnabled($stringTruncationEnabled)
    {
        if (! is_bool($stringTruncationEnabled)) {
            $stringTruncationEnabled = (bool) $stringTruncationEnabled;
        }

        self::$stringTruncationEnabled = $stringTruncationEnabled;
    }

    /**
     * @return bool
     */
    public static function getAutomaticFormattingEnabled()
    {
        return self::$automaticFormattingEnabled;
    }

    /**
     * @param bool $automaticFormattingEnabled
     */
    public static function setAutomaticFormattingEnabled($automaticFormattingEnabled)
    {
        if (! is_bool($automaticFormattingEnabled)) {
            $automaticFormattingEnabled = (bool) $automaticFormattingEnabled;
        }

        self::$automaticFormattingEnabled = $automaticFormattingEnabled;
    }

    /**
     * @return bool
     */
    public static function getAutomaticValidationEnabled()
    {
        return self::$automaticValidationEnabled;
    }

    /**
     * @param bool $automaticValidationEnabled
     */
    public static function setAutomaticValidationEnabled($automaticValidationEnabled)
    {
        if (! is_bool($automaticValidationEnabled)) {
            $automaticValidationEnabled = (bool) $automaticValidationEnabled;
        }

        self::$automaticValidationEnabled = $automaticValidationEnabled;
    }

    public function __construct(...$args)
    {
        if (! empty($args)) {
            $this->passConstructArgsToMagicSetters(... $args);
        }
    }

    /**
     * Magic method for setting undefined properties,
     * because \mysqli_result::fetch_object will attempt to set public properties directly.
     *
     * @param string $name
     * @param mixed $value
     */
    /*public function __set( $name, $value )
    {
        if ( array_key_exists( $name, $this->data) )
        {
            $this->setProperty( $name, $value );
        }
        else
        {
            $this->$name = $value;
        }
    }*/
}
