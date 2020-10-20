<?php

namespace Afterpay\SDK\Test\Integration;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class HTTPIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function test404()
    {
        $invalidRequest = new \Afterpay\SDK\HTTP\Request();
        $invalidRequest->setUri('/');
        $this->assertFalse($invalidRequest->send());
        $this->assertEquals(404, $invalidRequest->getResponse()->getHttpStatusCode());
    }
}
