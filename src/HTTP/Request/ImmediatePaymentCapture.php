<?php

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\HTTP\Request;
use Afterpay\SDK\Model\Money;

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
        ],
        'amount' => [
            'type' => Money::class,
            'required' => false
        ]
    ];

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setUri('/v1/payments/capture')
            ->setHttpMethod('POST')
            ->configureBasicAuth()
        ;
    }
}
