<?php

$composer_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    require_once __DIR__ . '/../test/autoload.php';
}

use Afterpay\SDK\Exception\InvalidModelException as AfterpayInvalidModelException;
use Afterpay\SDK\HTTP\Request\CreateCheckout as AfterpayCreateCheckoutRequest;
use Afterpay\SDK\Model\Consumer as AfterpayConsumer;
use Afterpay\SDK\Model\Money as AfterpayMoney;

if (! headers_sent()) {
    header('Content-Type: text/plain');
}



/**
 * The CreateCheckout Request class allows you to validate the entire payload in the constructor, or use
 * setters for each field of the request body. Either way, the provided arguments are automatically validated
 * against the application data models. You can also instantiate the models manually and build the request
 * payload yourself.
 *
 * Note that all classes that extend from the Model base class have two different validation modes. When
 * "Automatic Validation" is enabled, Exceptions will be thrown whenever an attempt is made to assign an
 * invalid value to a property of the object. Otherwise, the validation still takes place, but you must
 * manually check the validity using the "isValid" method when you are ready to perform validation logic.
 *
 * Method A:
 *
 * Validating the entire request payload in the constructor. Classes are instantiated for each field of the
 * request, according to the data models described in the API specification. If one or more fields are
 * invalid, and Automatic Validation is enabled, an InvalidModelException will be thrown at the first
 * encounter of an invalid model.
 */

\Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

try {
    $createCheckoutRequest = new AfterpayCreateCheckoutRequest([
        'amount' => [ '10.00', 'AUD' ],
        'consumer' => [
            'phoneNumber' => '0400 000 000',
            'givenNames' => 'Test',
            'surname' => 'Test',
            'email' => 'test@example.com'
        ],
        'billing' => [
            'name' => 'Joe Consumer',
            'line1' => 'Level 5',
            'line2' => '406 Collins Street',
            'area1' => 'Melbourne',
            'region' => 'VIC',
            'postcode' => '3000',
            'countryCode' => 'AU',
            'phoneNumber' => '0400 000 000'
        ],
        'shipping' => [
            'name' => 'Joe Consumer',
            'line1' => 'Level 5',
            'line2' => '406 Collins Street',
            'area1' => 'Melbourne',
            'region' => 'VIC',
            'postcode' => '3000',
            'countryCode' => 'AU',
            'phoneNumber' => '0400 000 000'
        ],
        'courier' => [
            'shippedAt' => '2019-01-01T00:00:00+10:00',
            'name' => 'Australia Post',
            'tracking' => 'AA0000000000000',
            'priority' => 'STANDARD'
        ],
        'items' => [
            [
                'name' => 'T-Shirt - Blue - Size M',
                'sku' => 'TSH0001B1MED',
                'quantity' => 10,
                'pageUrl' => 'https://www.example.com/page.html',
                'imageUrl' => 'https://www.example.com/image.jpg',
                'price' => [ '10.00', 'AUD' ],
                'categories' => [
                    [ 'Clothing', 'T-Shirts', 'Under $25' ],
                    [ 'Sale', 'Clothing' ]
                ]
            ]
        ],
        'discounts' => [
            [
                'displayName' => '20% off SALE',
                'amount' => [ '24.00', 'AUD' ]
            ]
        ],
        'merchant' => [
            'redirectConfirmUrl' => 'http://localhost',
            'redirectCancelUrl' => 'http://localhost'
        ],
        'taxAmount' => [ '0.00', 'AUD' ],
        'shippingAmount' => [ '0.00', 'AUD' ]
    ]);

    $createCheckoutRequest->send();

    echo $createCheckoutRequest->getRawLog();
} catch (AfterpayInvalidModelException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}



/**
 * Method B:
 *
 * Instantiating an empty Request class, then setting the values of each field using the individual
 * setter methods. If Automatic Validation is disabled, you can load in all of the data and then iterate over
 * the list of errors, rather than only catching the first.
 */

\Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

$createCheckoutRequest = new AfterpayCreateCheckoutRequest();

$createCheckoutRequest
    ->setAmount('10.00', 'AUD')
    ->setConsumer([
        'phoneNumber' => '0400 000 000',
        'givenNames' => 'Test',
        'surname' => 'Test',
        'email' => 'test@example.com'
    ])
    ->setBilling([
        'name' => 'Joe Consumer',
        'line1' => 'Level 5',
        'line2' => '406 Collins Street',
        'area1' => 'Melbourne',
        'region' => 'VIC',
        'postcode' => '3000',
        'countryCode' => 'AU',
        'phoneNumber' => '0400 000 000'
    ])
    ->setShipping([
        'name' => 'Joe Consumer',
        'line1' => 'Level 5',
        'line2' => '406 Collins Street',
        'area1' => 'Melbourne',
        'region' => 'VIC',
        'postcode' => '3000',
        'countryCode' => 'AU',
        'phoneNumber' => '0400 000 000'
    ])
    ->setCourier([
        'shippedAt' => '2019-01-01T00:00:00+10:00',
        'name' => 'Australia Post',
        'tracking' => 'AA0000000000000',
        'priority' => 'STANDARD'
    ])
    ->setItems([
        [
            'name' => 'T-Shirt - Blue - Size M',
            'sku' => 'TSH0001B1MED',
            'quantity' => 10,
            'pageUrl' => 'https://www.example.com/page.html',
            'imageUrl' => 'https://www.example.com/image.jpg',
            'price' => [ '10.00', 'AUD' ],
            'categories' => [
                [ 'Clothing', 'T-Shirts', 'Under $25' ],
                [ 'Sale', 'Clothing' ]
            ]
        ]
    ])
    ->setDiscounts([
        [
            'displayName' => '20% off SALE',
            'amount' => [ '24.00', 'AUD' ]
        ]
    ])
    ->setMerchant([
        'redirectConfirmUrl' => 'http://localhost',
        'redirectCancelUrl' => 'http://localhost'
    ])
    ->setTaxAmount('0.00', 'AUD')
    ->setShippingAmount('0.00', 'AUD')
;

if ($createCheckoutRequest->isValid()) {
    $createCheckoutRequest->send();

    echo $createCheckoutRequest->getRawLog();
} else {
    echo $createCheckoutRequest->getValidationErrorsAsHtml();
}



/**
 * Method C:
 *
 * Using some of the Model classes, but ultimately constructing the JSON payload manually. This sample
 * demonstrates that since the Model class implements JsonSerializable, any Model can be easily JSON-encoded.
 */

$createCheckoutRequest = new AfterpayCreateCheckoutRequest();

$amount = new AfterpayMoney('10.00', 'AUD');

$consumer = new AfterpayConsumer();
$consumer
    ->setPhoneNumber('0400 000 000')
    ->setGivenNames('Test')
    ->setSurname('Test')
    ->setEmail('test@example.com')
;

$createCheckoutRequest
    ->setRequestBody('{"amount":' . json_encode($amount) . ',"consumer":' . json_encode($consumer) . ',"merchant":{"redirectConfirmUrl":"http://localhost","redirectCancelUrl":"http://localhost"}}')
    ->send()
;

echo $createCheckoutRequest->getRawLog();
