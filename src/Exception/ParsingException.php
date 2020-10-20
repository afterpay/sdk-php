<?php

namespace Afterpay\SDK\Exception;

use Afterpay\SDK\Exception;

class ParsingException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
