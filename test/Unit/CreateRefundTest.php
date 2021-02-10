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

namespace Afterpay\SDK\Test\Unit;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class CreateRefundTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testMissingOrderIdException()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        try {
            $createRefundRequest->send();

            throw new \Exception('Expected PrerequisiteNotMetException not thrown');
        } catch (\Afterpay\SDK\Exception\PrerequisiteNotMetException $e) {
            $this->assertEquals('Cannot send a CreateRefund Request without an Order ID (must call CreateRefund::setOrderId before CreateRefund::send)', $e->getMessage());
        }
    }

    public function testNullOrderIdException()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        try {
            $createRefundRequest->setOrderId(null);

            throw new \Exception('Expected InvalidArgumentException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected integer or numeric string for orderId; NULL given', $e->getMessage());
        }
    }

    public function testArrayOrderIdException()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        try {
            $createRefundRequest->setOrderId([]);

            throw new \Exception('Expected InvalidArgumentException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected integer or numeric string for orderId; array given', $e->getMessage());
        }
    }

    public function testFloatOrderIdException()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        try {
            $createRefundRequest->setOrderId(0.0);

            throw new \Exception('Expected InvalidArgumentException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected integer or numeric string for orderId; double given', $e->getMessage());
        }
    }

    public function testInvalidStringOrderIdException()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        try {
            $createRefundRequest->setOrderId('10a');

            throw new \Exception('Expected InvalidArgumentException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals("Expected integer or numeric string for orderId; '10a' given", $e->getMessage());
        }
    }

    public function testLongOrderIdException()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        try {
            $createRefundRequest->setOrderId(PHP_INT_MAX + 1); # PHP_INT_MAX is "usually" 9223372036854775807 in 64-bit systems; 2147483647 in 32-bit systems.

            throw new \Exception('Expected InvalidArgumentException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected integer or numeric string for orderId; double given', $e->getMessage());
        }
    }

    public function testNegativeIntegerOrderIdOk()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        $createRefundRequest->setOrderId(-1);

        $this->assertEquals('/v2/payments/1/refund', $createRefundRequest->getUri());
    }

    public function testBigIntegerOrderIdOk()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        $createRefundRequest->setOrderId(PHP_INT_MAX);

        $this->assertEquals('/v2/payments/9223372036854775807/refund', $createRefundRequest->getUri());
    }

    public function testNumericStringOrderIdOk()
    {
        $createRefundRequest = new \Afterpay\SDK\HTTP\Request\CreateRefund();

        $createRefundRequest->setOrderId('123456789');

        $this->assertEquals('/v2/payments/123456789/refund', $createRefundRequest->getUri());
    }
}
