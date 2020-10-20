<?php

namespace Afterpay\SDK\Test\Integration;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class GetConfigurationIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testUnauthorized401()
    {
        # Set the credentials to something other than null so that they don't get automatically loaded
        # from the `.env.php` file.

        \Afterpay\SDK\HTTP::setMerchantId(false);
        \Afterpay\SDK\HTTP::setSecretKey(false);

        $getConfigurationRequest = new \Afterpay\SDK\HTTP\Request\GetConfiguration();

        $this->assertFalse($getConfigurationRequest->send());
        $this->assertEquals(401, $getConfigurationRequest->getResponse()->getHttpStatusCode());
    }

    public function testSuccessUsingCredentialsFromEnvConfig200()
    {
        # Reset the credentials to null so that they get automatically loaded
        # from the `.env.php` file.

        # Note: API credentials must be configured correctly in your `.env.php` file
        #       for this test to pass, or set as environment variables.

        \Afterpay\SDK\HTTP::setMerchantId(null);
        \Afterpay\SDK\HTTP::setSecretKey(null);

        $getConfigurationRequest = new \Afterpay\SDK\HTTP\Request\GetConfiguration();

        $this->assertTrue($getConfigurationRequest->send());
        $this->assertEquals(200, $getConfigurationRequest->getResponse()->getHttpStatusCode());
    }
}
