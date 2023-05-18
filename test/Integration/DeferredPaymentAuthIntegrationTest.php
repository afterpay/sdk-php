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

class DeferredPaymentAuthIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testApproved201()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Step 1 of 3

        # Create a checkout for 20.00 in the currency of the merchant account.

        $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $createCheckoutRequest
            ->fillBodyWithMockData()
            ->send()
        ;

        $checkoutToken = $createCheckoutRequest->getResponse()->getParsedBody()->token;

        # Step 2 of 3

        # Simulate a consumer completing the checkout and clicking the confirm button
        # to commit to the payment schedule.

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($checkoutToken, '000');

        # Step 3 of 3

        # Create a payment auth with an APPROVED status.
        # This action converts the temporary checkout into a permanent order record.

        $deferredPaymentAuthRequest = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $deferredPaymentAuthRequest
            ->setToken($checkoutToken)
            ->send()
        ;

        $deferredPaymentAuthResponse = $deferredPaymentAuthRequest->getResponse();
        $deferredPaymentAuthResponseBody = $deferredPaymentAuthResponse->getParsedBody();

        $this->assertEquals(201, $deferredPaymentAuthResponse->getHttpStatusCode());
        $this->assertEquals('APPROVED', $deferredPaymentAuthResponseBody->status);

        /**
         * Also verify that the parsed response can be used to instantiate a Payment object.
         *
         * @since 1.3.7
         */

        $payment = new \Afterpay\SDK\Model\Payment($deferredPaymentAuthResponseBody);
    }

    public function testDeclined402()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Step 1 of 3

        # Create a checkout for 20.00 in the currency of the merchant account.

        $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $createCheckoutRequest
            ->fillBodyWithMockData()
            ->send()
        ;

        $checkoutToken = $createCheckoutRequest->getResponse()->getParsedBody()->token;

        # Step 2 of 3

        # Simulate a consumer completing the checkout and clicking the confirm button
        # to commit to the payment schedule.
        # During this process, the consumer selects a card with a CSC of "051".

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($checkoutToken, '051');

        # Step 3 of 3

        # Create a payment auth with a DECLINED status.
        # This action converts the temporary checkout into a permanent order record.

        $deferredPaymentAuthRequest = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $deferredPaymentAuthRequest
            ->setToken($checkoutToken)
            ->send()
        ;

        $deferredPaymentAuthResponse = $deferredPaymentAuthRequest->getResponse();

        $this->assertEquals(402, $deferredPaymentAuthResponse->getHttpStatusCode());
        $this->assertEquals('DECLINED', $deferredPaymentAuthResponse->getParsedBody()->status);
    }
}
