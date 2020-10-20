<?php

namespace Afterpay\SDK\Test\Integration;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class GetCheckoutIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    private function maybeGet($key, $array)
    {
        return array_key_exists($key, $array) ? $array[ $key ] : null;
    }

    /**
     * @todo Update this test to support countries/currencies other that AU/AUD!
     */
    public function testSuccess200()
    {
        # Reset the credentials to null to make sure they get automatically loaded
        # (just in case a previous test has set them).

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        $createCheckoutRequest = new \Afterpay\SDK\HTTP\Request\CreateCheckout();
        $mockData = \Afterpay\SDK\MerchantAccount::generateMockData(\Afterpay\SDK\HTTP::getCountryCode());
        $createCheckoutRequest
            ->setAmount('10.00', $mockData[ 'currency' ])
            ->setConsumer([
                'phoneNumber' => $this->maybeGet('phoneNumber', $mockData),
                'givenNames' => 'Test',
                'surname' => 'Test',
                'email' => 'test@example.com'
            ])
            ->setBilling([
                'name' => 'Joe Consumer',
                'line1' => $this->maybeGet('line1', $mockData),
                'line2' => $this->maybeGet('line2', $mockData),
                'area1' => $this->maybeGet('area1', $mockData),
                'region' => $this->maybeGet('region', $mockData),
                'postcode' => $this->maybeGet('postcode', $mockData),
                'countryCode' => $createCheckoutRequest->getCountryCode(),
                'phoneNumber' => $this->maybeGet('phoneNumber', $mockData)
            ])
            ->setShipping([
                'name' => 'Joe Consumer',
                'line1' => $this->maybeGet('line1', $mockData),
                'line2' => $this->maybeGet('line2', $mockData),
                'area1' => $this->maybeGet('area1', $mockData),
                'region' => $this->maybeGet('region', $mockData),
                'postcode' => $this->maybeGet('postcode', $mockData),
                'countryCode' => $createCheckoutRequest->getCountryCode(),
                'phoneNumber' => $this->maybeGet('phoneNumber', $mockData)
            ])
            ->setItems([
                [
                    'name' => 'T-Shirt - Blue - Size M',
                    'sku' => 'TSH0001B1MED',
                    'quantity' => 10,
                    'pageUrl' => 'https://www.example.com/page.html',
                    'imageUrl' => 'https://www.example.com/image.jpg',
                    'price' => [ '10.00', $mockData[ 'currency' ] ],
                    'categories' => [
                        [ 'Clothing', 'T-Shirts', 'Under 25.00' ],
                        [ 'Sale', 'Clothing' ]
                    ]
                ]
            ])
            ->setDiscounts([
                [
                    'displayName' => '20% off SALE',
                    'amount' => [ '24.00', $mockData[ 'currency' ] ]
                ]
            ])
            ->setMerchant([
                'redirectConfirmUrl' => 'http://localhost',
                'redirectCancelUrl' => 'http://localhost'
            ])
            ->setTaxAmount('0.00', $mockData[ 'currency' ])
            ->setShippingAmount('0.00', $mockData[ 'currency' ])
        ;

        $createCheckoutRequest->send();

        $getCheckoutRequest = new \Afterpay\SDK\HTTP\Request\GetCheckout();

        $getCheckoutRequest->setCheckoutToken($createCheckoutRequest->getResponse()->getParsedBody()->token);

        $this->assertTrue($getCheckoutRequest->send());
        $this->assertEquals(200, $getCheckoutRequest->getResponse()->getHttpStatusCode());
    }
}
