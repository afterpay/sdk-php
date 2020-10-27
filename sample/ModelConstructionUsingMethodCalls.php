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
use Afterpay\SDK\Model\Consumer as AfterpayConsumer;

if (! headers_sent()) {
    header('Content-Type: text/plain');
}



/**
 * Constructing an object without any validation, using
 * chained method calls.
 */

$consumer = new AfterpayConsumer();
$consumer
    ->setPhoneNumber('0400 000 000')
    ->setGivenNames('Test')
    ->setSurname('Test')
    ->setEmail('test@example.com')
;

echo json_encode($consumer) . "\n";

/*=
{"phoneNumber":"0400 000 000","givenNames":"Test","surname":"Test","email":"test@example.com"}
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
    $consumer = new AfterpayConsumer();
    $consumer
        ->setPhoneNumber(false)
        ->setGivenNames(false)
        ->setSurname(false)
        ->setEmail(false)
    ;
} catch (\Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}

/*=
Afterpay\SDK\Exception\InvalidModelException: Expected string for Afterpay\SDK\Model\Consumer::$phoneNumber; boolean given
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

$consumer = new AfterpayConsumer();
$consumer
    ->setPhoneNumber(false)
    ->setGivenNames(false)
    ->setSurname(false)
    ->setEmail(false)
;

if (! $consumer->isValid()) {
    echo $consumer->getValidationErrorsAsHtml();
}

/*=
<ul>
    <li>phoneNumber:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\Consumer::$phoneNumber; boolean given</li>
    </ul>
    <li>givenNames:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\Consumer::$givenNames; boolean given</li>
    </ul>
    <li>surname:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\Consumer::$surname; boolean given</li>
    </ul>
    <li>email:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\Consumer::$email; boolean given</li>
    </ul>
</ul>
=*/
