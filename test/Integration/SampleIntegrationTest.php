<?php

namespace Afterpay\SDK\Test\Integration;

require_once __DIR__ . '/../autoload.php';

use Afterpay\SDK\Test\Sample;

class SampleIntegrationTest extends Sample
{
    protected $expected_files = [
        'HTTPRequestGetConfigurationWithPersistence.php',
        'HTTPRequestPing.php'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function testSample()
    {
        $this->compareExpectedOutputToActualOutput();
    }
}
