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

namespace Afterpay\SDK\Test\Integration;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;
use Afterpay\SDK\Test\ConsumerSimulator;

class ReversePaymentByTokenIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testNoContent204()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Step 1 of 4

        # Create a checkout for 20.00 in the currency of the merchant account.

        $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $createCheckoutRequest
            ->fillBodyWithMockData()
            ->send()
        ;

        $checkoutToken = $createCheckoutRequest->getResponse()->getParsedBody()->token;

        # Step 2 of 4

        # Simulate a consumer completing the checkout and clicking the confirm button
        # to commit to the payment schedule.
        # Note: The CVC of "000" will simulate an APPROVED status in the next step.

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($checkoutToken, '000');

        # Step 3 of 4

        # Capture payment to convert the temporary checkout into a permanent order record.
        # Note, this operation will be completed synchronously. However, in practice, it is not
        # necessary to wait for the order response to load before sending the reversal request.

        $immediatePaymentCaptureRequest = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        $immediatePaymentCaptureRequest
            ->setToken($checkoutToken)
            ->send()
        ;

        # Step 4 of 4

        # Call ReversePaymentByToken using the checkout token returned by the API in Step 1.
        # The expectation is that the request will be accepted, and the reversal process to be
        # initiated asynchronously. (A successful response does not indicate that the reversal
        # has been completed.)

        $reversePaymentByTokenRequest = new \Afterpay\SDK\HTTP\Request\ReversePaymentByToken();

        $reversePaymentByTokenRequest
            ->setToken($checkoutToken)
            ->send()
        ;

        $reversePaymentByTokenResponse = $reversePaymentByTokenRequest->getResponse();

        $this->assertEquals(204, $reversePaymentByTokenResponse->getHttpStatusCode());
        $this->assertEmpty($reversePaymentByTokenResponse->getRawBody());
    }
}
