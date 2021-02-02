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

use Afterpay\SDK\MerchantAccount;
use Afterpay\SDK\HTTP;
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

    private function maybeGet($key, $array)
    {
        return array_key_exists($key, $array) ? $array[ $key ] : null;
    }

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setUri('/v2/checkouts')
            ->setHttpMethod('POST')
            ->configureBasicAuth()
        ;
    }

    /**
     * @return \Afterpay\SDK\HTTP\CreateCheckout
     */
    public function fillBodyWithMockData()
    {
        $mockData = MerchantAccount::generateMockData(HTTP::getCountryCode());

        $this
            ->setAmount('10.00', $mockData[ 'currency' ])
            ->setConsumer([
                'phoneNumber' => $this->maybeGet('phoneNumber', $mockData),
                'givenNames' => 'Test',
                'surname' => 'Test',
                'email' => 'test@example.com'
            ])
            ->setBilling([
                'name' => 'Joe Consumer',
                'line1' => $this->maybeGet('line1', $mockData),
                'line2' => $this->maybeGet('line2', $mockData),
                'area1' => $this->maybeGet('area1', $mockData),
                'region' => $this->maybeGet('region', $mockData),
                'postcode' => $this->maybeGet('postcode', $mockData),
                'countryCode' => $this->getCountryCode(),
                'phoneNumber' => $this->maybeGet('phoneNumber', $mockData)
            ])
            ->setShipping([
                'name' => 'Joe Consumer',
                'line1' => $this->maybeGet('line1', $mockData),
                'line2' => $this->maybeGet('line2', $mockData),
                'area1' => $this->maybeGet('area1', $mockData),
                'region' => $this->maybeGet('region', $mockData),
                'postcode' => $this->maybeGet('postcode', $mockData),
                'countryCode' => $this->getCountryCode(),
                'phoneNumber' => $this->maybeGet('phoneNumber', $mockData)
            ])
            ->setItems([
                [
                    'name' => 'T-Shirt - Blue - Size M',
                    'sku' => 'TSH0001B1MED',
                    'quantity' => 10,
                    'pageUrl' => 'https://www.example.com/page.html',
                    'imageUrl' => 'https://www.example.com/image.jpg',
                    'price' => [ '10.00', $mockData[ 'currency' ] ],
                    'categories' => [
                        [ 'Clothing', 'T-Shirts', 'Under 25.00' ],
                        [ 'Sale', 'Clothing' ]
                    ]
                ]
            ])
            ->setDiscounts([
                [
                    'displayName' => '20% off SALE',
                    'amount' => [ '24.00', $mockData[ 'currency' ] ]
                ]
            ])
            ->setMerchant([
                'redirectConfirmUrl' => 'http://localhost',
                'redirectCancelUrl' => 'http://localhost'
            ])
            ->setTaxAmount('0.00', $mockData[ 'currency' ])
            ->setShippingAmount('0.00', $mockData[ 'currency' ])
        ;

        return $this;
    }
}
