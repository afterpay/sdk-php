<?php

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;
use Afterpay\SDK\Model\Money;

final class Item extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'name' => [
            'type' => 'string',
            'length' => 255,
            'required' => true
        ],
        'sku' => [
            'type' => 'string',
            'length' => 128
        ],
        'quantity' => [
            'type' => 'integer',
            'min' => self::SIGNED_32BIT_INT_MIN,
            'max' => self::SIGNED_32BIT_INT_MAX
        ],
        'pageUrl' => [
            'type' => 'string',
            'length' => 2048
        ],
        'imageUrl' => [
            'type' => 'string',
            'length' => 2048
        ],
        'price' => [
            'type' => Money::class,
            'required' => true
        ],
        'categories' => [
            'type' => 'array'
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
