<?php

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;

final class Contact extends Model
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
        'line1' => [
            'type' => 'string',
            'length' => 128,
            'required' => true
        ],
        'line2' => [
            'type' => 'string',
            'length' => 128
        ],
        'area1' => [
            'type' => 'string',
            'length' => 128
        ],
        'suburb' => [
            'type' => 'string',
            'length' => 128
        ],
        'state' => [
            'type' => 'string',
            'length' => 128
        ],
        'postcode' => [
            'type' => 'string',
            'length' => 128,
            'required' => true
        ],
        'countryCode' => [
            'type' => 'string',
            'length' => 2
        ],
        'phoneNumber' => [
            'type' => 'string',
            'length' => 32
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
