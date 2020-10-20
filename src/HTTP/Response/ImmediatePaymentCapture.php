<?php

namespace Afterpay\SDK\HTTP\Response;

use Afterpay\SDK\HTTP\Response;

class ImmediatePaymentCapture extends Response
{
    public function __construct()
    {
        parent::__construct();
    }

    public function isApproved()
    {
        return $this->isSuccessful() && $this->getParsedBody()->status == 'APPROVED';
    }
}
