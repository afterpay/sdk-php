<?php

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;

final class Merchant extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'redirectConfirmUrl' => [
            'type' => 'string',
            'required' => true
        ],
        'redirectCancelUrl' => [
            'type' => 'string',
            'required' => true
        ],
        'popupOriginUrl' => [
            'type' => 'string',
            'required' => false
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
