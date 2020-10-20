<?php

namespace Afterpay\SDK\Test\Unit;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class HTTPTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testBooleanApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment(false);
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected string; boolean given', $e->getMessage());
        }
    }

    public function testNullApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment(null);
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals('Expected string; NULL given', $e->getMessage());
        }
    }

    public function testEmptyStringApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment('');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals("Expected 'sandbox' or 'production'; '' given", $e->getMessage());
        }
    }

    public function testIncorrectCaseStringApiEnvironmentOnHttp()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');

        $this->assertEquals('Sandbox', \Afterpay\SDK\HTTP::getApiEnvironment());

        \Afterpay\SDK\HTTP::setApiEnvironment('SANDBOX');

        $this->assertEquals('SANDBOX', \Afterpay\SDK\HTTP::getApiEnvironment());
    }

    public function testInvalidStringApiEnvironmentOnHttpException()
    {
        try {
            \Afterpay\SDK\HTTP::setApiEnvironment('sbox');
        } catch (\Afterpay\SDK\Exception\InvalidArgumentException $e) {
            $this->assertEquals("Expected 'sandbox' or 'production'; 'sbox' given", $e->getMessage());
        }
    }

    public function testApiEnvironmentInheritance()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Production');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://api.afterpay.com', $pingRequest->getApiEnvironmentUrl());

        $merchant = new \Afterpay\SDK\MerchantAccount([
            'apiEnvironment' => 'Sandbox'
        ]);

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $pingRequest->setMerchantAccount($merchant);

        $this->assertEquals('https://api-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl()); # The Request will use the apiEnvironment of its MerchantAccount instance
        $this->assertEquals('Production', $pingRequest::getApiEnvironment()); # Even though the static property on the parent class hasn't changed
    }

    public function testEuRegionalApiEnvironmentSelection()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');
        \Afterpay\SDK\HTTP::setMerchantId('400100000');
        \Afterpay\SDK\HTTP::setCountryCode('GB');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://api.eu-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl());
    }

    public function testNaRegionalApiEnvironmentSelection()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');
        \Afterpay\SDK\HTTP::setMerchantId('100100000');
        \Afterpay\SDK\HTTP::setCountryCode('US');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://api.us-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl());
    }

    public function testOcRegionalApiEnvironmentSelection()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('Sandbox');
        \Afterpay\SDK\HTTP::setMerchantId('32000');
        \Afterpay\SDK\HTTP::setCountryCode('AU');

        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertEquals('https://api-sandbox.afterpay.com', $pingRequest->getApiEnvironmentUrl());
    }
}
