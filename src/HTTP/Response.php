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

namespace Afterpay\SDK\HTTP;

use Afterpay\SDK\HTTP;
use Afterpay\SDK\HTTP\Request;
use Afterpay\SDK\Exception\InvalidArgumentException;

class Response extends HTTP
{
    /**
     * @var \Afterpay\SDK\HTTP\Request $request
     */
    protected $request;

    /**
     * @var int $http_status_code
     */
    protected $http_status_code;

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * @param \Afterpay\SDK\HTTP\Request $request
     * @return \Afterpay\SDK\HTTP\Response
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setRequest($request)
    {
        if (! ( $request instanceof Request )) {
            throw new InvalidArgumentException('Object of class Afterpay\SDK\HTTP\Request expected');
        }

        $this->request = $request;

        return $this;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->http_status_code;
    }

    /**
     * @param int $http_status_code
     * @return \Afterpay\SDK\HTTP\Response
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setHttpStatusCode($http_status_code)
    {
        if (! is_int($http_status_code)) {
            throw new InvalidArgumentException('Integer expected; ' . gettype($http_status_code) . ' given');
        }

        $this->http_status_code = $http_status_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getRawLog()
    {
        $str = '';

        $str .= "########## BEGIN RAW HTTP REQUEST  ##########\n";
        $str .= $this->getRequest()->getRaw() . "\n";
        $str .= "########## END RAW HTTP REQUEST    ##########\n";
        $str .= "########## BEGIN RAW HTTP RESPONSE ##########\n";
        $str .= $this->getRaw() . "\n";
        $str .= "########## END RAW HTTP RESPONSE   ##########\n";

        return $this->maybeObfuscate($str);
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->http_status_code >= 200 && $this->http_status_code <= 299;
    }
}
