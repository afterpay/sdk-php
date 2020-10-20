<?php

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\HTTP\Request;

class ImmediatePaymentCapture extends Request
{
    /**
     * @var array $data
     */
    protected $data = [
        'token' => [
            'type' => 'string'
        ],
        'merchantReference' => [
            'type' => 'string',
            'length' => 128
        ]
    ];

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setUri('/v2/payments/capture')
            ->setHttpMethod('POST')
            ->configureBasicAuth()
        ;
    }
}
