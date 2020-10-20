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
