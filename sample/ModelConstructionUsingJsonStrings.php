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
use Afterpay\SDK\Model\ShippingCourier as AfterpayShippingCourier;

if (! headers_sent()) {
    header('Content-Type: text/plain');
}



/**
 * Constructing an object without any validation, by passing
 * a JSON-formatted string as the first argument.
 */

$courier = new AfterpayShippingCourier('{
    "shippedAt" : "2019-01-01T00:00:00+10:00",
    "name" : "Australia Post",
    "tracking" : "AA0000000000000",
    "priority" : "STANDARD"
}');

echo json_encode($courier) . "\n";

/*=
{"shippedAt":"2019-01-01T00:00:00+10:00","name":"Australia Post","tracking":"AA0000000000000","priority":"STANDARD"}
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
    $courier = new AfterpayShippingCourier('{
        "shippedAt" : false,
        "name" : false,
        "tracking" : false,
        "priority" : false
    }');
} catch (\Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}

/*=
Afterpay\SDK\Exception\InvalidModelException: Expected string for Afterpay\SDK\Model\ShippingCourier::$shippedAt; boolean given
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

$courier = new AfterpayShippingCourier('{
    "shippedAt" : false,
    "name" : false,
    "tracking" : false,
    "priority" : false
}');

if (! $courier->isValid()) {
    echo $courier->getValidationErrorsAsHtml();
}

/*=
<ul>
    <li>shippedAt:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\ShippingCourier::$shippedAt; boolean given</li>
    </ul>
    <li>name:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\ShippingCourier::$name; boolean given</li>
    </ul>
    <li>tracking:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\ShippingCourier::$tracking; boolean given</li>
    </ul>
    <li>priority:</li>
    <ul>
        <li>Expected string for Afterpay\SDK\Model\ShippingCourier::$priority; boolean given</li>
    </ul>
</ul>
=*/
