<?php

namespace Afterpay\SDK;

class Exception extends \Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
