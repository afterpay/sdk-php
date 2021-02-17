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

class DeferredPaymentVoidIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ensure that the total `openToCapture` amount can be voided in a single
     * DeferredPaymentVoid Request by omitting the `amount` parameter.
     */
    public function testVoidFullAmountSuccess201()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Step 1 of 4

        # Create a checkout for 10.00 in the currency of the merchant account.

        $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $createCheckoutRequest
            ->fillBodyWithMockData()
            ->send()
        ;

        $checkoutToken = $createCheckoutRequest->getResponse()->getParsedBody()->token;

        # Step 2 of 4

        # Simulate a consumer completing the checkout and clicking the confirm button
        # to commit to the payment schedule.

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($checkoutToken, '000');

        # Step 3 of 4

        # Create a payment auth with an APPROVED status.
        # This action converts the temporary checkout into a permanent order record.

        $deferredPaymentAuthRequest = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $deferredPaymentAuthRequest
            ->setToken($checkoutToken)
            ->send()
        ;

        $orderId = $deferredPaymentAuthRequest->getResponse()->getParsedBody()->id;

        # Step 4 of 4

        # Void the original amount of 10.00, completing the auth

        $deferredPaymentVoidRequest = new \Afterpay\SDK\HTTP\Request\DeferredPaymentVoid();

        $deferredPaymentVoidRequest
            ->setOrderId($orderId)
            ->send()
        ;

        $deferredPaymentVoidResponse = $deferredPaymentVoidRequest->getResponse();

        $this->assertEquals(201, $deferredPaymentVoidResponse->getHttpStatusCode());
        $this->assertEquals('0.00', $deferredPaymentVoidResponse->getParsedBody()->openToCaptureAmount->amount);
        $this->assertEquals('VOIDED', $deferredPaymentVoidResponse->getParsedBody()->paymentState);
    }

    /**
     * Ensure that a partial amount can be voided by including
     * the `amount` parameter.
     */
    public function testVoidPartialAmountSuccess201()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Step 1 of 4

        # Create a checkout for 10.00 in the currency of the merchant account.

        $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

        $createCheckoutRequest
            ->fillBodyWithMockData()
            ->send()
        ;

        $checkoutToken = $createCheckoutRequest->getResponse()->getParsedBody()->token;

        # Step 2 of 4

        # Simulate a consumer completing the checkout and clicking the confirm button
        # to commit to the payment schedule.

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($checkoutToken, '000');

        # Step 3 of 4

        # Create a payment auth with an APPROVED status.
        # This action converts the temporary checkout into a permanent order record.

        $deferredPaymentAuthRequest = new \Afterpay\SDK\HTTP\Request\DeferredPaymentAuth();

        $deferredPaymentAuthRequest
            ->setToken($checkoutToken)
            ->send()
        ;

        $orderId = $deferredPaymentAuthRequest->getResponse()->getParsedBody()->id;

        # Step 4 of 4

        # Void a partial amount of 2.00, leaving 8.00 open to capture.

        $mockData = \Afterpay\SDK\MerchantAccount::generateMockData(\Afterpay\SDK\HTTP::getCountryCode());

        $deferredPaymentVoidRequest = new \Afterpay\SDK\HTTP\Request\DeferredPaymentVoid();

        $deferredPaymentVoidRequest
            ->setOrderId($orderId)
            ->setAmount('2.00', $mockData[ 'currency' ])
            ->send()
        ;

        $deferredPaymentVoidResponse = $deferredPaymentVoidRequest->getResponse();

        $this->assertEquals(201, $deferredPaymentVoidResponse->getHttpStatusCode());
        $this->assertEquals('8.00', $deferredPaymentVoidResponse->getParsedBody()->openToCaptureAmount->amount);
        $this->assertEquals('AUTH_APPROVED', $deferredPaymentVoidResponse->getParsedBody()->paymentState);
    }
}
