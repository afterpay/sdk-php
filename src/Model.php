<?php

namespace Afterpay\SDK;

class Model implements \JsonSerializable
{
    use \Afterpay\SDK\Shared\ModelMethods;

    private static $stringTruncationEnabled = false;
    private static $automaticFormattingEnabled = true;
    private static $automaticValidationEnabled = false;

    const SIGNED_32BIT_INT_MIN = -2147483648;
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
