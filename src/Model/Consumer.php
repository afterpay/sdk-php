<?php

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;

final class Consumer extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'phoneNumber' => [
            'type' => 'string',
            'length' => 32
        ],
        'givenNames' => [
            'type' => 'string',
            'length' => 128
        ],
        'surname' => [
            'type' => 'string',
            'length' => 128
        ],
        'email' => [
            'type' => 'string',
            'length' => 128,
            'required' => true
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
