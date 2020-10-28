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

namespace Afterpay\SDK\Test\Network;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class HTTPNetworkTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testConnectionTimeout()
    {
        \Afterpay\SDK\HTTP::setApiEnvironment('sandbox');

        $invalidRequest = new \Afterpay\SDK\HTTP\Request();
        $invalidRequest
            ->setUri('/')
            ->setConnectionTimeout(1)
        ;

        try {
            $invalidRequest->send();
        } catch (\Afterpay\SDK\Exception\NetworkException $e) {
            $this->{ method_exists($this, 'assertMatchesRegularExpression') ? 'assertMatchesRegularExpression' : 'assertRegExp' }('/^(7|28)$/', (string) $e->getCode());

            if ($e->getCode() == 7) {
                $this->{ method_exists($this, 'assertMatchesRegularExpression') ? 'assertMatchesRegularExpression' : 'assertRegExp' }('/^Failed to connect to api-sandbox\.afterpay\.com port 443: (Operation|Connection) timed out$/', $e->getMessage());
            } else {
                $this->{ method_exists($this, 'assertMatchesRegularExpression') ? 'assertMatchesRegularExpression' : 'assertRegExp' }('/^((Connection|Resolving) timed out after \d+ milliseconds|remaining timeout of \d+ too small to resolve via SIGALRM method|Connection time-out)$/', $e->getMessage());
            }
        }
    }

    /**
     * This is a potentially flaky test. There doesn't actually appear to be any way to independently define
     * a "read" timeout. The test works by sending a large body (16MB) to the API, with the hope that 1 second
     * is ample time to establish a connection, but not enough time to send the complete body and load the
     * response.
     *
     * Note that any network test has the potential to throw an unexpected exception.
     */
    public function testReadTimeout()
    {
        $invalidRequest = new \Afterpay\SDK\HTTP\Request();
        $invalidRequest
            ->setUri('/')
            ->setHttpMethod('POST')
            ->setTimeout(1000)
            ->setRequestBody(str_repeat('a', 1024 * 1024 * 16)) # 16MB
        ;

        try {
            $invalidRequest->send();

            throw new \Exception('Expected NetworkException not thrown');
        } catch (\Afterpay\SDK\Exception\NetworkException $e) {
            $this->assertEquals(28, $e->getCode());
            $this->{ method_exists($this, 'assertMatchesRegularExpression') ? 'assertMatchesRegularExpression' : 'assertRegExp' }('/^Operation timed out after \d+ milliseconds with \d+( out of \d+)? bytes received$/', $e->getMessage());
        }
    }
}
