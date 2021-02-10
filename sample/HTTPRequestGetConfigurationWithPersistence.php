<?php

/**
 * @copyright Copyright (c) 2021 Afterpay Corporate Services Pty Ltd
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

use Afterpay\SDK\Config as AfterpayConfig;
use Afterpay\SDK\MerchantAccount as AfterpayMerchantAccount;
use Afterpay\SDK\PersistentStorage as AfterpayPersistentStorage;
use Afterpay\SDK\HTTP\Request\GetConfiguration as AfterpayGetConfigurationRequest;

if (! headers_sent()) {
    header('Content-Type: text/plain');
}



/**
 * Make a "Get Configuration" request by manually providing API credentials
 * for this individual request only.
 */

$merchant = new AfterpayMerchantAccount();

$merchant
    ->setMerchantId('MERCHANT_ID')
    ->setSecretKey('SECRET_KEY')
;

$getConfigurationRequest = new AfterpayGetConfigurationRequest();

$getConfigurationRequest
    ->setMerchantAccount($merchant)
;

$getConfigurationRequest->send();

$body = $getConfigurationRequest->getResponse()->getParsedBody();

var_dump($body);

# Expected output example, if real credentials were used instead of "MERCHANT_ID" and "SECRET_KEY":
/*
object(stdClass)#11 (1) {
  ["maximumAmount"]=>
  object(stdClass)#10 (2) {
    ["amount"]=>
    string(7) "1000.00"
    ["currency"]=>
    string(3) "AUD"
  }
}
*/

# Expected output using the placeholder (invalid) credentials above (regular expression):
/*~
object\(stdClass\)#[0-9]+ \(4\) \{
  \["errorCode"\]=>
  string\(12\) "unauthorized"
  \["errorId"\]=>
  string\(16\) "[0-9a-f]{16}"
  \["message"\]=>
  string\(49\) "Credentials are required to access this resource\."
  \["httpStatusCode"\]=>
  int\(401\)
\}
~*/



/**
 * You can also utilise the PersistentStorage class to retain configuration information in your
 * local server environment, only calling the Afterpay API when necessary.
 *
 * The default lifespan of data retreived from the "Get Configuration" endpoint is 15 minutes. The
 * SDK will automatically call the API to request fresh data if the age threshold of stored data
 * has been exceeded.
 *
 * For example, if MySQL access details are configured in
 * ./vendor/afterpay-global/afterpay-sdk-php/.env.php
 * like this:
 *
 * $afterpay_sdk_env_config = [
 *   'merchantId' => 'MERCHANT_ID',
 *   'secretKey' => 'SECRET_KEY',
 *   'apiEnvironment' => 'sandbox',
 *   'db.api' => 'mysqli',
 *   'db.host' => '127.0.0.1',
 *   'db.port' => '3306',
 *   'db.database' => 'DB_DATABASE',
 *   'db.tablePrefix' => 'DB_TABLE_PREFIX',
 *   'db.user' => 'DB_USER',
 *   'db.pass' => 'DB_PASS'
 * ];
 *
 * You can also configure the details manually onto the Config class like this:
 *
 * AfterpayConfig::set('merchantId', 'MERCHANT_ID');
 * AfterpayConfig::set('secretKey', 'SECRET_KEY');
 * AfterpayConfig::set('apiEnvironment', 'sandbox');
 * AfterpayConfig::set('db.api', 'mysqli');
 * AfterpayConfig::set('db.host', '127.0.0.1');
 * AfterpayConfig::set('db.port', '3306');
 * AfterpayConfig::set('db.database', 'DB_DATABASE');
 * AfterpayConfig::set('db.tablePrefix', 'DB_TABLE_PREFIX');
 * AfterpayConfig::set('db.user', 'DB_USER');
 * AfterpayConfig::set('db.pass', 'DB_PASS');
 */

try {
    $min = AfterpayPersistentStorage::get('orderMinimum'); // If persistent config is available but stored limits are old, this will make the GetConfiguration call automatically and persist the response data.
    $max = AfterpayPersistentStorage::get('orderMaximum'); // This will then simply access the data persisted from the line above.

    var_dump($min);
    var_dump($max);
} catch (\Exception $e) {
    var_dump($e->getMessage());
}

# Example output to be expected, if real credentials and database configuration were provided
# (regular expression):
/*~
(NULL|object\(Afterpay\\SDK\\Model\\Money\)#[0-9]+ \(1\) \{
  \["data":protected\]=>
  array\(2\) \{
    \["amount"\]=>
    array\(4\) \{
      \["type"\]=>
      string\(6\) "string"
      \["default"\]=>
      string\(4\) "0\.00"
      \["required"\]=>
      bool\(true\)
      \["value"\]=>
      string\([0-7]\) "[0-9.]+"
    \}
    \["currency"\]=>
    array\(4\) \{
      \["type"\]=>
      string\(6\) "string"
      \["length"\]=>
      int\(3\)
      \["required"\]=>
      bool\(true\)
      \["value"\]=>
      string\(3\) "[A-Z]{3}"
    \}
  \}
\})
(NULL|object\(Afterpay\\SDK\\Model\\Money\)#[0-9]+ \(1\) \{
  \["data":protected\]=>
  array\(2\) \{
    \["amount"\]=>
    array\(4\) \{
      \["type"\]=>
      string\(6\) "string"
      \["default"\]=>
      string\(4\) "0\.00"
      \["required"\]=>
      bool\(true\)
      \["value"\]=>
      string\([0-7]\) "[0-9.]+"
    \}
    \["currency"\]=>
    array\(4\) \{
      \["type"\]=>
      string\(6\) "string"
      \["length"\]=>
      int\(3\)
      \["required"\]=>
      bool\(true\)
      \["value"\]=>
      string\(3\) "[A-Z]{3}"
    \}
  \}
\})
~*/

# Expected output without any configuration:
/*
string(25) "No available database API"
*/

/**
 * If a database is configured in .env.php, the following is the simplest, most efficient, and recommended
 * method of accessing, persisting and using Afterpay Merchant Account Configuration information.
 */

$myAccount = new AfterpayMerchantAccount();

try {
    $myOrderMin = $myAccount->getOrderMinimumAsFloat();
    $myOrderMax = $myAccount->getOrderMaximumAsFloat();

    var_dump($myOrderMin);
    var_dump($myOrderMax);

    $orderAmount = 200.00;

    if ($orderAmount >= $myOrderMin && $orderAmount <= $myOrderMax) {
        echo "Afterpay is available for this order.\n";
    }
} catch (\Exception $e) {
    var_dump($e->getMessage());
}

# Example expected output, if real credentials and database configuration were provided in .env.php
# (regular expression):
/*~
float\((-INF|[0-9.]+)\)
float\([0-9.]+\)
Afterpay is available for this order\.
~*/

# Expected output without a suitable .env.php file:
/*
string(25) "No available database API"
*/
