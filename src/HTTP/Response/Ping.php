<?php

namespace Afterpay\SDK\HTTP\Response;

use Afterpay\SDK\HTTP\Response;

class Ping extends Response
{
    public function __construct()
    {
        parent::__construct();
    }

    public function isSuccessful()
    {
        return $this->http_status_code == 200 && trim($this->raw_body) == 'pong';
    }
}
