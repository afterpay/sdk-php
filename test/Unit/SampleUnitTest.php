<?php

namespace Afterpay\SDK\Test\Unit;

require_once __DIR__ . '/../autoload.php';

use Afterpay\SDK\Test\Sample;

class SampleUnitTest extends Sample
{
    protected $expected_files = [
        'ModelConstruction.php',
        'ModelConstructionUsingAssociativeArrays.php',
        'ModelConstructionUsingJsonStrings.php',
        'ModelConstructionUsingMethodCalls.php',
        'ModelConstructionUsingOrderedArguments.php'
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
