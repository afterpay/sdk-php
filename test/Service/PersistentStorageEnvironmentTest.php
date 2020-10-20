<?php

namespace Afterpay\SDK\Test\Service;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class PersistentStorageEnvironmentTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testConnection()
    {
        $this->assertTrue(\Afterpay\SDK\PersistentStorage::testConnection());
    }
}
