<?php

/**
 * @copyright Copyright (c) 2020-2021 Afterpay Corporate Services Pty Ltd
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;
use Afterpay\SDK\Model\Money;
use Afterpay\SDK\Model\OrderDetails;
use Afterpay\SDK\Model\PaymentEvent;
use Afterpay\SDK\Model\Refund;

final class Payment extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'id' => [
            'type' => 'string'
        ],
        'token' => [
            'type' => 'string'
        ],
        'status' => [
            'type' => 'string'
        ],
        'created' => [
            'type' => 'string'
        ],
        'originalAmount' => [
            'type' => Money::class
        ],
        'openToCaptureAmount' => [
            'type' => Money::class
        ],
        'paymentState' => [
            'type' => 'string'
        ],
        'merchantReference' => [
            'type' => 'string'
        ],
        'refunds' => [
            'type' => Refund::class . '[]'
        ],
        'orderDetails' => [
            'type' => OrderDetails::class
        ],
        'events' => [
            'type' => PaymentEvent::class . '[]'
        ],
        'merchantPortalOrderUrl' => [
            'type' => 'string'
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
