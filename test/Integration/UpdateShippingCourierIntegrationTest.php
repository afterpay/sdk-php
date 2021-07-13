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

class UpdateShippingCourierIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testOk200()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        # Note: Timestamps are always converted by the API to UTC / Zulu time.
        # Below, $originalTimestamp is the value sent to the API in the request, and
        # $convertedTimestamp is the corresponding value received back from the API in response.

        $originalTimestamp = '2020-01-01T00:59:59+01:00';
        $convertedTimestamp = '2019-12-31T23:59:59.000Z';

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
        # Note: The CVC of "000" will simulate an APPROVED status in the next step.

        $consumerSimulator = new ConsumerSimulator();

        $consumerSimulator->confirmPaymentSchedule($checkoutToken, '000');

        # Step 3 of 4

        # Capture payment to convert the temporary checkout into a permanent order record.
        # Note: In this section we assert that the courier properties are not already what
        # we intend to change them to.

        $immediatePaymentCaptureRequest = new \Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture();

        $immediatePaymentCaptureRequest
            ->setToken($checkoutToken)
            ->send()
        ;

        $immediatePaymentCaptureResponse = $immediatePaymentCaptureRequest->getResponse();

        $immediatePaymentCaptureResponseBody = $immediatePaymentCaptureResponse->getParsedBody();

        $orderId = $immediatePaymentCaptureResponseBody->id;

        if (property_exists($immediatePaymentCaptureResponseBody->orderDetails, 'courier')) {
            if (property_exists($immediatePaymentCaptureResponseBody->orderDetails->courier, 'shippedAt')) {
                $this->assertNotEquals($originalTimestamp, $immediatePaymentCaptureResponseBody->orderDetails->courier->shippedAt);
            }
            if (property_exists($immediatePaymentCaptureResponseBody->orderDetails->courier, 'name')) {
                $this->assertNotEquals('a', $immediatePaymentCaptureResponseBody->orderDetails->courier->name);
            }
            if (property_exists($immediatePaymentCaptureResponseBody->orderDetails->courier, 'tracking')) {
                $this->assertNotEquals('a', $immediatePaymentCaptureResponseBody->orderDetails->courier->tracking);
            }
            if (property_exists($immediatePaymentCaptureResponseBody->orderDetails->courier, 'priority')) {
                $this->assertNotEquals('EXPRESS', $immediatePaymentCaptureResponseBody->orderDetails->courier->priority);
            }
        }

        # Step 4 of 4

        # Call UpdateShippingCourier using the order ID returned by the API in the previous step.
        # The expectation is that the a second Payment object will be returned, with the only differences
        # being the properties of the orderDetails.courier object that were altered.
        # Note: Since we modified the response for Immediate Payment Capture to include an extra
        # property, we will need to remove that property before comparing with the Update Shipping
        # Courier response.

        $updateShippingCourierRequest = new \Afterpay\SDK\HTTP\Request\UpdateShippingCourier();

        $updateShippingCourierRequest
            ->setOrderId($orderId)
            ->setShippedAt($originalTimestamp)
            ->setName('a')
            ->setTracking('a')
            ->setPriority('EXPRESS')
            ->send()
        ;

        $updateShippingCourierResponse = $updateShippingCourierRequest->getResponse();

        $updateShippingCourierResponseBody = $updateShippingCourierResponse->getParsedBody();

        $this->assertEquals(200, $updateShippingCourierResponse->getHttpStatusCode());
        $this->assertEquals($convertedTimestamp, $updateShippingCourierResponseBody->orderDetails->courier->shippedAt);
        $this->assertEquals('a', $updateShippingCourierResponseBody->orderDetails->courier->name);
        $this->assertEquals('a', $updateShippingCourierResponseBody->orderDetails->courier->tracking);
        $this->assertEquals('EXPRESS', $updateShippingCourierResponseBody->orderDetails->courier->priority);

        unset($immediatePaymentCaptureResponseBody->orderDetails->courier);
        unset($updateShippingCourierResponseBody->orderDetails->courier);
        unset($immediatePaymentCaptureResponseBody->merchantPortalOrderUrl);

        $this->assertEquals($immediatePaymentCaptureResponseBody, $updateShippingCourierResponseBody);
    }
}
