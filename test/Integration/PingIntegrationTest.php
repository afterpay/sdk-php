<?php

namespace Afterpay\SDK\Test\Integration;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class PingIntegrationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testSuccess()
    {
        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();

        $this->assertTrue($pingRequest->send());
    }
}
