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

class CreateCheckoutHTTPTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Afterpay\SDK\HTTP\Request\CreateCheckout $request
     */
    private function populateWithBadMinimumData($request)
    {
        $request->setAmount('0.00', 'AAA');
        $request->setConsumer(['email' => 'a@a.a']);
        $request->setMerchant(['popupOriginUrl' => 'a://a']);
    }

    public function testUnexpectedStringForModeEnumiException()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

        $request = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $this->populateWithBadMinimumData($request);

        try {
            $request->setMode('a');

            throw new \Exception('Expected InvalidModelException not thrown');
        } catch (\Afterpay\SDK\Exception\InvalidModelException $e) {
            $this->assertEquals('Expected one of "STANDARD", "EXPRESS" for Afterpay\SDK\HTTP\Request\CreateCheckout::$mode; "a" given', $e->getMessage());
        }
    }

    public function testStandardAcceptedForMode()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $this->populateWithBadMinimumData($request);

        $request->setMode('STANDARD');

        $this->assertCount(0, $request->getValidationErrors());
    }

    public function testExpressAcceptedForMode()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $this->populateWithBadMinimumData($request);

        $request->setMode('EXPRESS');

        $this->assertCount(0, $request->getValidationErrors());
    }

    public function testModeEnumiCaseInsensitive()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $request = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $this->populateWithBadMinimumData($request);

        $request->setMode('express');

        $this->assertCount(0, $request->getValidationErrors());
    }
}
