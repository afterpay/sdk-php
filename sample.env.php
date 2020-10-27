<?php

/**
 * This is a sample environment configuration file.
 *
 * Please use this as a template to create a
 * `.env.php` file in the same directory, ensuring that
 * all values of the array below are populated with
 * real data.
 *
 * Note: Please do not change the array keys;
 *       change the values only.
 *
 * Note: This file will be parsed by
 *       \Afterpay\SDK\Config::loadEnvConfig
 *       in ./src/Config.php
 * 
 * 
 * 
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

$afterpay_sdk_env_config = [
    'merchantId' => 'MY_MERCHANT_ID',
    'secretKey' => 'MY_SECRET_KEY',
    'countryCode' => 'US',
    'apiEnvironment' => 'sandbox', // must be 'sandbox' or 'production'
    'db.api' => 'mysqli', // must be 'mysqli'
    'db.host' => '127.0.0.1',
    'db.port' => '3306',
    'db.database' => 'MY_DATABASE',
    'db.tablePrefix' => 'afterpay_',
    'db.user' => 'MY_MYSQL_USER',
    'db.pass' => 'MY_MYSQL_PASS'
];
