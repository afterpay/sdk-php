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

namespace Afterpay\SDK\Test\Unit\HTTP;

require_once __DIR__ . '/../../autoload.php';

use PHPUnit\Framework\TestCase;

class DeferredPaymentAuthHTTPTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testUnexpectedStringForExpectedBooleanException()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

        $request = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        try {
            $request->setIsCheckoutAdjusted('a');

            throw new \Exception('Expected InvalidModelException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidModelException $e) {
            $this->assertEquals('Expected boolean for Afterpay\SDK\HTTP\Request\DeferredPaymentAuth::$isCheckoutAdjusted; string given', $e->getMessage());
        }
    }

    public function testBooleanDataTypeAccepted()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted(true);

        $this->assertCount(0, $request->getValidationErrors());
    }

    public function testIntegerOneAcceptedForBooleanTrue()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted(1);

        $this->assertCount(0, $request->getValidationErrors());
        $this->assertTrue($request->getIsCheckoutAdjusted());
    }

    public function testIntegerZeroAcceptedForBooleanFalse()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted(0);

        $this->assertCount(0, $request->getValidationErrors());
        $this->assertFalse($request->getIsCheckoutAdjusted());
    }

    public function testIntegerTwoForBooleanException()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

        $request = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        try {
            $request->setIsCheckoutAdjusted(2);

            throw new \Exception('Expected InvalidModelException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidModelException $e) {
            $this->assertEquals('Expected boolean for Afterpay\SDK\HTTP\Request\DeferredPaymentAuth::$isCheckoutAdjusted; integer given', $e->getMessage());
        }
    }

    public function testEmptyStringAcceptedForBooleanFalse()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted('');

        $this->assertCount(0, $request->getValidationErrors());
        $this->assertFalse($request->getIsCheckoutAdjusted());
    }
}
