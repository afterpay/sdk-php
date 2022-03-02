<?php

/**
 * @copyright Copyright (c) 2020-2021 Afterpay Corporate Services Pty Ltd
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

namespace Afterpay\SDK\Test\Unit;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class HTTPTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testBooleanApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment(false);
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected string; boolean given', $e->getMessage());
        }
    }

    public function testNullApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment(null);
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected string; NULL given', $e->getMessage());
        }
    }

    public function testEmptyStringApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment('');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals("Expected 'sandbox' or 'production'; '' given", $e->getMessage());
        }
    }

    public function testIncorrectCaseStringApiEnvironmentOnHttp()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');

        $this->assertEquals('Sandbox', \Afterpay\SDK\HTTP::getApiEnvironment());

        \Afterpay\SDK\HTTP::setApiEnvironment('SANDBOX');

        $this->assertEquals('SANDBOX', \Afterpay\SDK\HTTP::getApiEnvironment());
    }

    public function testInvalidStringApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment('sbox');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals("Expected 'sandbox' or 'production'; 'sbox' given", $e->getMessage());
        }
    }

    public function testApiEnvironmentInheritance()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Production');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://global-api.afterpay.com', $pingRequest->getApiEnvironmentUrl());

        $merchant = new \Afterpay\SDK\MerchantAccount([
            'apiEnvironment' => 'Sandbox'
        ]);

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $pingRequest->setMerchantAccount($merchant);

        $this->assertEquals('https://global-api-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl()); # The Request will use the apiEnvironment of its MerchantAccount instance
        $this->assertEquals('Production', $pingRequest::getApiEnvironment()); # Even though the static property on the parent class hasn't changed
    }

    public function testEuRegionalApiEnvironmentSelection()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');
        \Afterpay\SDK\HTTP::setMerchantId('400100000');
        \Afterpay\SDK\HTTP::setCountryCode('GB');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://global-api-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl());
    }

    public function testNaRegionalApiEnvironmentSelection()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');
        \Afterpay\SDK\HTTP::setMerchantId('100100000');
        \Afterpay\SDK\HTTP::setCountryCode('US');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://global-api-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl());
    }

    public function testOcRegionalApiEnvironmentSelection()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');
        \Afterpay\SDK\HTTP::setMerchantId('32000');
        \Afterpay\SDK\HTTP::setCountryCode('AU');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://global-api-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl());
    }
}
