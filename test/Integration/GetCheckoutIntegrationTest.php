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

namespace Afterpay\SDK\Test\Integration;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class GetCheckoutIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testSuccess200()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $createCheckoutRequest
            ->fillBodyWithMockData()
            ->send()
        ;

        $getCheckoutRequest = new \Afterpay\SDK\HTTP\Request\GetCheckout();

        $getCheckoutRequest->setCheckoutToken($createCheckoutRequest->getResponse()->getParsedBody()->token);

        $this->assertTrue($getCheckoutRequest->send());
        $this->assertEquals(200, $getCheckoutRequest->getResponse()->getHttpStatusCode());
    }
}
