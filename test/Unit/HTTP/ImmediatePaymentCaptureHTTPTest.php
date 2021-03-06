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

class ImmediatePaymentCaptureHTTPTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testUnexpectedStringForExpectedBooleanException()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

        $request = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        try {
            $request->setIsCheckoutAdjusted('a');

            throw new \Exception('Expected InvalidModelException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidModelException $e) {
            $this->assertEquals('Expected boolean for Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture::$isCheckoutAdjusted; string given', $e->getMessage());
        }
    }

    public function testBooleanDataTypeAccepted()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted(true);

        $this->assertCount(0, $request->getValidationErrors());
    }

    public function testStringYesAcceptedForBooleanTrue()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted('Yes');

        $this->assertCount(0, $request->getValidationErrors());
        $this->assertTrue($request->getIsCheckoutAdjusted());
    }

    public function testStringNoAcceptedForBooleanFalse()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted('No');

        $this->assertCount(0, $request->getValidationErrors());
        $this->assertFalse($request->getIsCheckoutAdjusted());
    }

    public function testStringTrueAcceptedForBooleanTrue()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted('TRUE');

        $this->assertCount(0, $request->getValidationErrors());
        $this->assertTrue($request->getIsCheckoutAdjusted());
    }

    public function testStringFalseAcceptedForBooleanFalse()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        $request->setToken('a');
        $request->setIsCheckoutAdjusted('false');

        $this->assertCount(0, $request->getValidationErrors());
        $this->assertFalse($request->getIsCheckoutAdjusted());
    }
}
