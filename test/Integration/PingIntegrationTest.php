<?php

/**
 * @copyright Copyright (c) 2020 Afterpay Limited Group
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

    public function testUserAgentHeader()
    {
        \Afterpay\SDK\HTTP::addPlatformDetail('testUserAgentHeader', '1.0.0-beta+exp.sha.5114f85');
        $pingRequest = new \Afterpay\SDK\HTTP\Request\Ping();
        \Afterpay\SDK\HTTP::clearPlatformDetails();

        $pingRequest->send();

        $headers_str = $pingRequest->getRawHeaders();
        $pattern_str = '/^User-Agent: afterpay-sdk-php\/[\d.]+ \(testUserAgentHeader\/1\.0\.0-beta\+exp\.sha\.5114f85; PHP\/[^ ;]+; cURL\/[\d.]+/im';

        $this->assertMatchesRegularExpression($pattern_str, $headers_str);
    }
}
