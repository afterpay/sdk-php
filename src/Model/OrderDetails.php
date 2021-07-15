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
use Afterpay\SDK\Model\Consumer;
use Afterpay\SDK\Model\Contact;
use Afterpay\SDK\Model\Discount;
use Afterpay\SDK\Model\Item;
use Afterpay\SDK\Model\Money;
use Afterpay\SDK\Model\ShippingCourier;

final class OrderDetails extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'consumer' => [
            'type' => Consumer::class,
            'required' => true
        ],
        'billing' => [
            'type' => Contact::class
        ],
        'shipping' => [
            'type' => Contact::class
        ],
        'courier' => [
            'type' => ShippingCourier::class
        ],
        'items' => [
            'type' => Item::class . '[]'
        ],
        'discounts' => [
            'type' => Discount::class . '[]'
        ],
        'taxAmount' => [
            'type' => Money::class
        ],
        'shippingAmount' => [
            'type' => Money::class
        ],
        'purchaseCountry' => [ # For Europe only
            'type' => 'string',
            'length' => 2
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
