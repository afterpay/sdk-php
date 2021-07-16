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

class ListPaymentsIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testListPaymentsByTokenArray()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Step 1 of 4

        # Create two checkouts, each for 10.00 in the currency of the merchant account.

        $tokens = [];

        for ($i = 0; $i <= 1; $i++) {
            $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

            $createCheckoutRequest
                ->fillBodyWithMockData()
                ->send()
            ;

            $tokens[$i] = $createCheckoutRequest->getResponse()->getParsedBody()->token;
        }

        # Step 2 of 4

        # Simulate consumers completing the checkouts and clicking the confirm buttons
        # to commit to the payment schedules.
        # Note: The CVC of "000" will simulate APPROVED statuses in the next step.

        for ($i = 0; $i <= 1; $i++) {
            $consumerSimulator = new ConsumerSimulator();

            $consumerSimulator->confirmPaymentSchedule($tokens[$i], '000');
        }

        # Step 3 of 4

        # Capture payments to convert the temporary checkouts into permanent order records.

        # Note: Since we modified the response for Immediate Payment Capture to include an extra
        # property, we will need to remove that property before comparing with the List Payments
        # response.

        $immediatePaymentCaptureResponseBodies = [];

        for ($i = 0; $i <= 1; $i++) {
            $immediatePaymentCaptureRequest = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

            $immediatePaymentCaptureRequest
                ->setToken($tokens[$i])
                ->send()
            ;

            $response = $immediatePaymentCaptureRequest->getResponse();
            $response->removeEventCreationTimestamps();

            $responseBody = $response->getParsedBody();
            unset($responseBody->merchantPortalOrderUrl);

            $immediatePaymentCaptureResponseBodies[$i] = $responseBody;
        }

        # Step 4 of 4

        # Call ListPayments using the checkout tokens returned by the API in Step 1.
        # The expectation is that the same Payment objects will be returned again.

        # Note: There is a delay between the order being created and indexed by the search
        # service in a correct state. A wait time of 30 seconds is added to account for this.

        # Note: Default order is by createdAt descending (newest first), so orders will be
        # returned in the opposite order from what they were created in. The array will
        # therefore be reversed for comparison.

        sleep(30);

        $listPaymentsRequest = new \Afterpay\SDK\HTTP\Request\ListPayments();

        $listPaymentsRequest
            ->setTokens($tokens)
            ->send()
        ;

        $listPaymentsResponse = $listPaymentsRequest->getResponse();
        $listPaymentsResponse->removeEventCreationTimestamps();
        $listPaymentsResponseBody = $listPaymentsResponse->getParsedBody();

        $this->assertEquals(200, $listPaymentsResponse->getHttpStatusCode());
        $this->assertEquals(2, $listPaymentsResponseBody->totalResults);
        $this->assertEquals(array_reverse($immediatePaymentCaptureResponseBodies), $listPaymentsResponseBody->results);
    }

    public function testListPaymentsByStatusArray()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Step 1 of 4

        # Create two checkouts, each for 10.00 in the currency of the merchant account.

        $fromCreatedDate = gmdate('c');

        $tokens = [];

        for ($i = 0; $i <= 1; $i++) {
            $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

            $createCheckoutRequest
                ->fillBodyWithMockData()
                ->send()
            ;

            $tokens[$i] = $createCheckoutRequest->getResponse()->getParsedBody()->token;
        }

        # Step 2 of 4

        # Simulate consumers completing the checkouts and clicking the confirm buttons
        # to commit to the payment schedules.
        # Note: The CVC of "000" will simulate an APPROVED status in the next step.
        # Note: The CVC of "051" will simulate a DECLINED status in the next step.

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($tokens[0], '000');

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($tokens[1], '051');

        # Step 3 of 4

        # Capture payments to convert the temporary checkouts into permanent order records.
        # Note: A permanent order record is created even if the payment is declined.

        # Note: Since we modified the response for Immediate Payment Capture to include an extra
        # property, we will need to remove that property before comparing with the List Payments
        # response.

        $immediatePaymentCaptureResponseBodies = [];

        for ($i = 0; $i <= 1; $i++) {
            $immediatePaymentCaptureRequest = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

            $immediatePaymentCaptureRequest
                ->setToken($tokens[$i])
                ->send()
            ;

            $response = $immediatePaymentCaptureRequest->getResponse();
            $response->removeEventCreationTimestamps();

            $responseBody = $response->getParsedBody();
            unset($responseBody->merchantPortalOrderUrl);

            $immediatePaymentCaptureResponseBodies[$i] = $responseBody;
        }

        # Step 4 of 4

        # Call ListPayments using the timestamps recorded above and a status of "APPROVED".
        # The expectation is that only one of the two orders placed within the date range
        # will be returned.

        # Note: There is a delay between the order being created and indexed by the search
        # service in a correct state. A wait time of 30 seconds is added to account for this.

        sleep(30);

        $toCreatedDate = gmdate('c');

        $listPaymentsRequest = new \Afterpay\SDK\HTTP\Request\ListPayments();

        $listPaymentsRequest
            ->setFromCreatedDate($fromCreatedDate)
            ->setToCreatedDate($toCreatedDate)
            ->setStatuses(['approved'])
            ->send()
        ;

        $listPaymentsResponse = $listPaymentsRequest->getResponse();
        $listPaymentsResponse->removeEventCreationTimestamps();
        $listPaymentsResponseBody = $listPaymentsResponse->getParsedBody();

        unset($immediatePaymentCaptureResponseBodies[0]->merchantPortalOrderUrl);

        $this->assertEquals(200, $listPaymentsResponse->getHttpStatusCode());
        $this->assertEquals(1, $listPaymentsResponseBody->totalResults);
        $this->assertEquals($immediatePaymentCaptureResponseBodies[0], $listPaymentsResponseBody->results[0]);
    }
}
