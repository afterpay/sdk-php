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

class DeferredPaymentCaptureCBTTest extends TestCase
{
    private $countries = ['AU', 'NZ', 'US', 'CA', 'GB', 'ES', 'FR', 'IT'];
    private $currencies = [
        'AU' => 'AUD',
        'NZ' => 'NZD',
        'US' => 'USD',
        'CA' => 'CAD',
        'UK' => 'GBP',
        'ES' => 'EUR',
        'FR' => 'EUR',
        'IT' => 'EUR'
    ];


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ensure that the total `openToCapture` amount can be captured in a single
     * DeferredPaymentCapture Request.
     */
    public function testCaptureFullAmountSuccess201()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        foreach ($this->countries as $countryCode) {
            $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();

            if ($createCheckoutRequest->getMerchantAccountCountryCode() != $countryCode) {
                # Step 1 of 4

                # Create a checkout for 10.00 in all the available currencies.
                $createCheckoutRequest->fillBodyWithMockData($countryCode);
                $createCheckoutRequest->send();

                if ($createCheckoutRequest->getResponse()->getHttpStatusCode() == 201) {
                    $checkoutToken = $createCheckoutRequest->getResponse()->getParsedBody()->token;

                    # Step 2 of 4
    
                    # Simulate a consumer completing the checkout and clicking the confirm button
                    # to commit to the payment schedule.
    
                    $consumerSimulator = new ConsumerSimulator($countryCode);
    
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
    
                    # Capture a 10.00 payment for the order, completing the auth
    
                    $mockData = \Afterpay\SDK\MerchantAccount::generateMockData($countryCode);
    
                    $deferredPaymentCaptureRequest = new \Afterpay\SDK\HTTP\Request\DeferredPaymentCapture();
    
                    $deferredPaymentCaptureRequest
                        ->setOrderId($orderId)
                        ->setAmount('10.00', $mockData[ 'currency' ])
                        ->send()
                    ;
    
                    $deferredPaymentCaptureResponse = $deferredPaymentCaptureRequest->getResponse();
    
                    $this->assertEquals(201, $deferredPaymentCaptureResponse->getHttpStatusCode());
                    $this->assertEquals('0.00', $deferredPaymentCaptureResponse->getParsedBody()->openToCaptureAmount->amount);
                    $this->assertEquals('CAPTURED', $deferredPaymentCaptureResponse->getParsedBody()->paymentState);
                } else {
                    $this->assertEquals(201, $createCheckoutRequest->getResponse()->getHttpStatusCode(),
                        $createCheckoutRequest->getMerchantAccountCountryCode() . ' Merchant with ' . $countryCode . ' Consumer in ' . $this->currencies[$countryCode] .
                        '. Error Message: ' . $createCheckoutRequest->getResponse()->getParsedBody()->message
                    );
                }
            }
        }
    }
}
