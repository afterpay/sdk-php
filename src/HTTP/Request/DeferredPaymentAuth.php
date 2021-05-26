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

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\HTTP\Request;
use Afterpay\SDK\Model\Contact;
use Afterpay\SDK\Model\Item;
use Afterpay\SDK\Model\Money;

class DeferredPaymentAuth extends Request
{
    /**
     * @var array $data
     */
    protected $data = [
        'requestId' => [
            'type' => 'string'
        ],
        'token' => [
            'type' => 'string',
            'required' => true
        ],
        'merchantReference' => [
            'type' => 'string',
            'length' => 128
        ],
        'amount' => [
            'type' => Money::class
        ],
        'isCheckoutAdjusted' => [
            'type' => 'boolean'
        ],
        'items' => [
            'type' => Item::class . '[]'
        ],
        'shipping' => [
            'type' => Contact::class
        ],
        'paymentScheduleChecksum' => [
            'type' => 'string'
        ]
    ];

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setUri('/v2/payments/auth')
            ->setHttpMethod('POST')
            ->configureBasicAuth()
        ;
    }
}
