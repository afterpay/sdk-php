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

$composer_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    require_once __DIR__ . '/../test/autoload.php';
}

use Afterpay\SDK\Model as AfterpayModel;
use Afterpay\SDK\Model\Contact as AfterpayContact;
use Afterpay\SDK\HTTP\Request\CreateCheckout as AfterpayCreateCheckoutRequest;

if (! headers_sent()) {
    header('Content-Type: text/plain');
}



/**
 * Constructing an HTTP request comprised of
 * multiple objects using automatic validation.
 *
 * Note: Automatic validation will interrupt
 *       processing at the first sign of invalid
 *       data. However, it allows you to log
 *       a stack trace, which can be useful for
 *       debugging.
 *
 * @see https://www.php.net/manual/en/exception.gettraceasstring.php
 *
 * Note: This model is deliberately invalid
 *       to demonstrate validation rules.
 */

AfterpayModel::setAutomaticValidationEnabled(true);

try {
    $createCheckoutRequest = new AfterpayCreateCheckoutRequest([
        'amount' => [ '100.00', 'AUD' ],
        'consumer' => [
            'phoneNumber' => 0400000000,
            'givenNames' => 'Test',
            'surname' => 'Test',
            'email' => 'test@example.com'
        ],
        'merchant' => [
            'redirectConfirmUrl' => 'http://localhost',
            'redirectCancelUrl' => 'http://localhost'
        ]
    ]);
} catch (\Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}

/*=
Afterpay\SDK\Exception\InvalidModelException: Expected string for Afterpay\SDK\Model\Consumer::$phoneNumber; integer given
=*/



/**
 * Constructing an object comprised of multiple child
 * objects, constucted using different methods,
 * using manual validation.
 *
 * Note: This is the default model validation mode.
 *
 * Note: The object constructed below is deliberately
 *       invalid to demonstrate validation rules.
 *
 * Note: If you do not check that objects are valid
 *       before sending them to the API, you may
 *       receive similar errors in the HTTP response.
 */

AfterpayModel::setAutomaticValidationEnabled(false);

$discount = new \Afterpay\SDK\Model\Discount();
$discount
    ->setDisplayName('20% off SALE')
    ->setAmount('24.00', 'AUD')
;

$createCheckoutRequest = new AfterpayCreateCheckoutRequest([
    'amount' => [ '10.00', 'AUD' ],
    'consumer' => [
        'phoneNumber' => '0400 000 000',
        'givenNames' => 'Test',
        'surname' => 'Test',
        'email' => 'test@example.com'
    ],
    'billing' => new AfterpayContact([
        'name' => 'Joe Consumer',
        'line1' => 'Level 23',
        'line2' => '2 Southbank Blvd',
        'area1' => 'Southbank',
        'region' => 'VIC',
        'postcode' => '3006',
        'countryCode' => 'AU',
        'phoneNumber' => '0400 000 000'
    ]),
    'shipping' => '{
        "name" : "Joe Consumer",
        "line1" : "Level 23",
        "line2" : "2 Southbank Blvd",
        "area1" : "Southbank",
        "region" : "VIC",
        "postcode" : "3006",
        "countryCode" : "Australia",
        "phoneNumber" : "0400 000 000"
    }',
    'courier' => new \Afterpay\SDK\Model\ShippingCourier([
        'shippedAt' => '2019-01-01T00:00:00+10:00',
        'name' => 'Australia Post',
        'tracking' => 'AA0000000000000',
        'priority' => 'STANDARD'
    ]),
    'items' => [
        new \Afterpay\SDK\Model\Item([
            'name' => 'T-Shirt - Blue - Size M',
            'sku' => 'TSH0001B1MED',
            'quantity' => 10,
            'pageUrl' => false,
            'imageUrl' => 'https://www.example.com/image.jpg',
            'price' => '10.00',
            'categories' => [
                [ 'Clothing', 'T-Shirts', 'Under $25' ],
                [ 'Sale', 'Clothing' ]
            ]
        ]),
        array()
    ],
    'discounts' => [
        $discount
    ],
    'merchant' => [
        'redirectConfirmUrl' => 'http://localhost',
        'redirectCancelUrl' => 'http://localhost'
    ],
    'taxAmount' => new \Afterpay\SDK\Model\Money('0.00', 'AUD'),
    'shippingAmount' => new \Afterpay\SDK\Model\Money([ '0.00', 'AUD' ])
]);

if (! $createCheckoutRequest->isValid()) {
    echo $createCheckoutRequest->getValidationErrorsAsHtml();
}

/*=
<ul>
    <li>shipping:</li>
    <ul>
        <li>countryCode:</li>
        <ul>
            <li>Expected maximum of 2 characters for Afterpay\SDK\Model\Contact::$countryCode; 9 characters given</li>
        </ul>
    </ul>
    <li>items[0]:</li>
    <ul>
        <li>pageUrl:</li>
        <ul>
            <li>Expected string for Afterpay\SDK\Model\Item::$pageUrl; boolean given</li>
        </ul>
        <li>price:</li>
        <ul>
            <li>Expected Afterpay\SDK\Model\Money for Afterpay\SDK\Model\Item::$price; string given</li>
        </ul>
    </ul>
    <li>items[1]:</li>
    <ul>
        <li>name:</li>
        <ul>
            <li>Required property missing: Afterpay\SDK\Model\Item::$name</li>
        </ul>
        <li>price:</li>
        <ul>
            <li>Required property missing: Afterpay\SDK\Model\Item::$price</li>
        </ul>
    </ul>
</ul>
=*/
