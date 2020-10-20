<?php

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;
use Afterpay\SDK\Model\Money;

final class Discount extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'displayName' => [
            'type' => 'string',
            'length' => 128,
            'required' => true
        ],
        'amount' => [
            'type' => Money::class,
            'required' => true
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
