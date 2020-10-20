<?php

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;

final class ShippingCourier extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'shippedAt' => [
            'type' => 'string'
        ],
        'name' => [
            'type' => 'string',
            'length' => 128
        ],
        'tracking' => [
            'type' => 'string',
            'length' => 128
        ],
        'priority' => [
            'type' => 'string'
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
