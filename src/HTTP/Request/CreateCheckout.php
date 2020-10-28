<?php

/**
 * @copyright Copyright (c) 2020 Afterpay Limited Group
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
use Afterpay\SDK\Model\Consumer;
use Afterpay\SDK\Model\Contact;
use Afterpay\SDK\Model\Discount;
use Afterpay\SDK\Model\Item;
use Afterpay\SDK\Model\Merchant;
use Afterpay\SDK\Model\Money;
use Afterpay\SDK\Model\ShippingCourier;

class CreateCheckout extends Request
{
    /**
     * @var array $data
     */
    protected $data = [
        'amount' => [
            'type' => Money::class,
            'required' => true
        ],
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
        'description' => [
            'type' => 'string',
            'length' => 256
        ],
        'items' => [
            'type' => Item::class . '[]'
        ],
        'discounts' => [
            'type' => Discount::class . '[]'
        ],
        'merchant' => [
            'type' => Merchant::class,
            'required' => true
        ],
        'merchantReference' => [
            'type' => 'string',
            'length' => 128
        ],
        'taxAmount' => [
            'type' => Money::class
        ],
        'shippingAmount' => [
            'type' => Money::class
        ]
    ];

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setUri('/v2/checkouts')
            ->setHttpMethod('POST')
            ->configureBasicAuth()
        ;
    }
}
