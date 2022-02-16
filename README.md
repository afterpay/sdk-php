<!-- See https://shields.io/ -->
[![Build](https://github.com/afterpay/sdk-php/workflows/Build/badge.svg)](https://github.com/afterpay/sdk-php/actions?query=workflow%3ABuild)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D5.6-blue)](#prerequisites)
[![Coding Standard](https://img.shields.io/badge/Coding%20Standard-PSR--12-yellow)](CONTRIBUTING.md#contributing)

# Official Afterpay SDK for PHP

## Prerequisites

Minimum requirements:

- PHP 5.6+

Optional recommendations:

- MySQL 5.6+
- PHPUnit 5+

## Installation

Install with [Composer](https://getcomposer.org/).

### For Production

For production usage, ensure the `dist` install method is used. This will exclude development resources
such as tests and sample code.

```bash
composer require --prefer-dist afterpay-global/afterpay-sdk-php
```

### For Development

For development purposes, install from source using the `--prefer-source` option. This will include
development resources such as tests and sample code.

```bash
composer require --prefer-source afterpay-global/afterpay-sdk-php
```

Note: If you need to switch between `source` and `dist` installations, you will need to first remove the
package using the following command, then reinstall using one of the `require` commands above.

```bash
composer remove afterpay-global/afterpay-sdk-php
```

## Configuration

Environment variables are expected to be defined in a PHP file,
located at `./vendor/afterpay-global/afterpay-sdk-php/.env.php`.

A sample file is provided at `./vendor/afterpay-global/afterpay-sdk-php/sample.env.php` - use this
file as a template to create your own environment configuration file.

It is possible to configure the SDK without this expected file, by manually setting the configuration
options on each of the appropriate classes before using them in your application. As a last resort,
the SDK will attempt to load its environment variables from the system with
[`getenv`](https://www.php.net/manual/en/function.getenv.php).

## Testing

Test with PHPUnit. Ensure you have the appropriate version of PHPUnit installed based on your PHP version.

Each release is verified in the following test environments:

| PHP | MySQL | PHPUnit |
| --- | ----- | ------- |
| 5.6 | 5.6   | 5       |
| 7.0 | 5.7   | 6       |
| 7.1 | 5.7   | 7       |
| 7.2 | 5.7   | 8       |
| 7.3 | 5.7   | 9       |
| 7.4 | 8.0   | 9       |
| 8.0 | 8.0   | 9       |
| 8.1 | 8.0   | 9       |

However, it is always recommended to also test all software in your own unique environment prior
to deploying to production.

### Unit Tests

These tests do not require any networking or persistent storage.

```bash
phpunit --colors=always ./vendor/afterpay-global/afterpay-sdk-php/test/unit
```

### Service Tests

These tests assume you have configured a persistent storage provider in your `.env.php` configuration file.
For example, if you provide credentials for a MySQL database, the SDK will verify that it can connect to
the database with write access.

```bash
phpunit --colors=always ./vendor/afterpay-global/afterpay-sdk-php/test/service
```

### Network Tests

These tests will verify that the SDK can communicate with the necessary external services using PHP's
native cURL libraries.

```bash
phpunit --colors=always ./vendor/afterpay-global/afterpay-sdk-php/test/network
```

### Integration Tests

These tests assume you have configured valid Merchant credentials for the Afterpay/Clearpay Online API
in your `.env.php` configuration file. If so, the SDK will verify that it can send requests to the API,
and that the API responds as expected.

```bash
phpunit --colors=always ./vendor/afterpay-global/afterpay-sdk-php/test/integration
```

## Usage

### Constructing Data Models

All the model classes are available in [src/Model](src/Model). These classes are intended to reflect the
data models described in the official online documentation for both
[Afterpay](https://developers.afterpay.com/afterpay-online/reference#models) and
[Clearpay](https://developers.clearpay.co.uk/clearpay-online/reference#models). By using these model
classes to construct HTTP Requests, validity of request format can be verified at runtime.

Sample code is provided in the [sample](sample) directory:

- [Using method calls](sample/ModelConstructionUsingMethodCalls.php)
- [Using associative arrays](sample/ModelConstructionUsingAssociativeArrays.php)
- [Using ordered arguments](sample/ModelConstructionUsingOrderedArguments.php)
- [Using JSON strings](sample/ModelConstructionUsingJsonStrings.php)
- [Combination of the above](sample/ModelConstruction.php)

### Making HTTP Requests

Most API endpoints require authentication (with the exception of Ping). You can define your API credentials
for your Afterpay/Clearpay merchant account using several different methods. You will also need to specify the ISO 3166-1
alpha-2 two-character country code of the merchant account. The following methods of specifying these details are supported:

1. Inside your `.env.php` file, as the `merchantId`, `secretKey` and `countryCode` properties of the `$afterpay_sdk_env_config`
   array.
2. As the `MERCHANT_ID`, `SECRET_KEY` and `COUNTRY_CODE` environment variables.
3. By manually defining an object of class `\Afterpay\SDK\MerchantAccount`, passing your account details using its
   `setMerchantId`, `setSecretKey` and `setCountryCode` methods, then passing this object to the the HTTP Request object using
   its `setMerchantAccount` method.

Sample code is provided in the [sample](sample) directory:

- [Making a "Ping" request](sample/HTTPRequestPing.php)
- [Making a "Get Configuration" request, and persisting the result](sample/HTTPRequestGetConfigurationWithPersistence.php)
- [Validating and creating a checkout](sample/HTTPRequestCreateCheckoutWithValidation.php)
- [Immediately capturing payment for a confirmed checkout](sample/HTTPRequestImmediatePaymentCapture.php)
- [Creating a payment auth for a confirmed checkout](sample/HTTPRequestDeferredPaymentAuth.php)
- [Capturing payment for a despatched shipment](sample/HTTPRequestDeferredPaymentCapture.php)
- [Voiding an unfulfillable portion of a payment auth](sample/HTTPRequestDeferredPaymentVoid.php)
- [Creating a refund](sample/HTTPRequestCreateRefund.php)

## Troubleshooting

### How To Retrieve Raw HTTP Logs

All [Request](src/HTTP/Request.php) and [Response](src/HTTP/Response.php) objects provide a `getRawLog` method for returning a raw HTTP log. This is often the most useful
data to facilitate an investigation, should a scenario be encountered where the Afterpay API does not behave
as expected.

_Note: By default, any potentially sensitive information is obfuscated for privacy reasons. It is strongly
recommended that this feature is not disabled, unless extraordinary precautions are taken to ensure the raw
HTTP logs are not stored for any extended period of time, and destroyed immediately after their intended
purpose has been fulfilled._

For example:

```php
require __DIR__ . '/vendor/autoload.php';

use Afterpay\SDK\HTTP\Request\CreateCheckout as AfterpayCreateCheckoutRequest;

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
```

```
########## BEGIN RAW HTTP REQUEST  ##########
POST /v2/checkouts HTTP/2
Host: global-api-sandbox.afterpay.com
Authorization: Basic MzM******************************************************************************************************************************************************************************TA=
User-Agent: afterpay-sdk-php/1.4.0 (PHP/8.1.1; cURL/7.77.0; Merchant/*****)
Accept: */*
Content-Type: application/json
Content-Length: 1223

{"amount":{"amount":"10.00","currency":"AUD"},"consumer":{"phoneNumber":"**** *** ***","givenNames":"****","surname":"****","email":"****************"},"billing":{"name":"*** ********","line1":"***** *","line2":"*** ******* ******","area1":"*********","region":"***","postcode":"****","countryCode":"AU","phoneNumber":"**** *** ***"},"shipping":{"name":"*** ********","line1":"***** *","line2":"*** ******* ******","area1":"*********","region":"***","postcode":"****","countryCode":"AU","phoneNumber":"**** *** ***"},"courier":{"shippedAt":"2019-01-01T00:00:00+10:00","name":"********* ****","tracking":"AA0000000000000","priority":"STANDARD"},"items":[{"name":"******* * **** * **** *","sku":"TSH0001B1MED","quantity":10,"pageUrl":"https:\/\/www.example.com\/page.html","imageUrl":"https:\/\/www.example.com\/image.jpg","price":{"amount":"10.00","currency":"AUD"},"categories":[["Clothing","T-Shirts","Under $25"],["Sale","Clothing"]]}],"discounts":[{"displayName":"20% off SALE","amount":{"amount":"24.00","currency":"AUD"}}],"merchant":{"redirectConfirmUrl":"http:\/\/localhost","redirectCancelUrl":"http:\/\/localhost"},"taxAmount":{"amount":"0.00","currency":"AUD"},"shippingAmount":{"amount":"0.00","currency":"AUD"}}
########## END RAW HTTP REQUEST    ##########
########## BEGIN RAW HTTP RESPONSE ##########
HTTP/2 201
date: Tue, 15 Sep 2020 14:20:49 GMT
content-type: application/json
content-length: 249
set-cookie: __cfduid=d05b6824b1ed88439e0b8edfe4905c7671600179649; expires=Thu, 15-Oct-20 14:20:49 GMT; path=/; domain=.afterpay.com; HttpOnly; SameSite=Lax; Secure
http_correlation_id: ed5d72kgz3ezlylm7g3qxiej6a
cf-cache-status: DYNAMIC
cf-request-id: 0533bcd2b60000fd36ac918200000001
expect-ct: max-age=604800, report-uri="https://report-uri.cloudflare.com/cdn-cgi/beacon/expect-ct"
strict-transport-security: max-age=31536000; includeSubDomains; preload
server: cloudflare
cf-ray: 5d32fd97883dfd36-SYD

{
  "token" : "001.ug27ke8qbpljfsssn4t98c60eq61767rva1e3f3g7g4nup7c",
  "expires" : "2020-09-15T17:20:49.267Z",
  "redirectCheckoutUrl" : "https://portal.sandbox.afterpay.com/au/checkout/?token=001.ug27ke8qbpljfsssn4t98c60eq61767rva1e3f3g7g4nup7c"
}
########## END RAW HTTP RESPONSE   ##########
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md#contributing).
