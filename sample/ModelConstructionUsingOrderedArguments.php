<?php

$composer_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    require_once __DIR__ . '/../test/autoload.php';
}

use Afterpay\SDK\Model as AfterpayModel;
use Afterpay\SDK\Model\Contact as AfterpayContact;

if (! headers_sent()) {
    header('Content-Type: text/plain');
}



/**
 * Constructing an object without any validation, by passing a
 * strictly ordered sequence of unnamed arguments.
 */

$contact = new AfterpayContact(
    'Joe Consumer',
    'Level 23',
    '2 Southbank Blvd',
    'Southbank',
    'Southbank',
    'VIC',
    '3006',
    'AU',
    '0400 000 000'
);

echo json_encode($contact) . "\n";

/*=
{"name":"Joe Consumer","line1":"Level 23","line2":"2 Southbank Blvd","suburb":"Southbank","state":"VIC","postcode":"3006","countryCode":"AU","phoneNumber":"0400 000 000"}
=*/



/**
 * Constructing an object with automatic validation.
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
    $contact = new AfterpayContact(
        0
    );
} catch (\Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}

/*=
Afterpay\SDK\Exception\InvalidModelException: Expected string for Afterpay\SDK\Model\Contact::$name; integer given
=*/



/**
 * Constructing an object with manual validation.
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

$contact = new AfterpayContact(
    0
);

if (! $contact->isValid()) {
    echo $contact->getValidationErrorsAsHtml();
}

/*=
<ul>
    <li>name:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\Contact::$name; integer given</li>
    </ul>
    <li>line1:</li>
    <ul>
        <li>Required property missing: Afterpay\SDK\Model\Contact::$line1</li>
    </ul>
    <li>postcode:</li>
    <ul>
        <li>Required property missing: Afterpay\SDK\Model\Contact::$postcode</li>
    </ul>
</ul>
=*/
