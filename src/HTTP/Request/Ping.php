<?php

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\HTTP\Request;

class Ping extends Request
{
    public function __construct()
    {
        parent::__construct();

        $this->setUri('/ping');
    }
}
