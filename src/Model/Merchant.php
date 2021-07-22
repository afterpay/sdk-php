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
            'required' => false
        ],
        'redirectCancelUrl' => [
            'type' => 'string',
            'required' => false
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
