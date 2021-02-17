<?php

/**
 * @copyright Copyright (c) 2020-2021 Afterpay Corporate Services Pty Ltd
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

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\Exception\InvalidArgumentException;
use Afterpay\SDK\Exception\PrerequisiteNotMetException;
use Afterpay\SDK\HTTP\Request;

class GetCheckout extends Request
{
    /**
     * @var string $checkoutToken
     *
     * @todo Make a flexible array for all path params similar to body data.
     */
    protected $checkoutToken;

    /**
     * @throws \Afterpay\SDK\Exception\PrerequisiteNotMetException
     */
    protected function beforeSend()
    {
        if (is_null($this->checkoutToken)) {
            throw new PrerequisiteNotMetException('Cannot send a GetCheckout Request without a checkout token (must call GetCheckout::setCheckoutToken before GetCheckout::send)');
        }
    }

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->configureBasicAuth()
        ;
    }

    /**
     * @param string $checkoutToken
     * @return \Afterpay\SDK\HTTP\Request\GetCheckout
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setCheckoutToken($checkoutToken)
    {
        if (! is_string($checkoutToken)) {
            throw new InvalidArgumentException('Expected string for checkoutToken; ' . gettype($checkoutToken) . ' given');
        } elseif (strlen($checkoutToken) < 1) {
            throw new InvalidArgumentException('Expected non-empty string for checkoutToken; empty string given');
        } elseif (! preg_match('/^[a-z0-9-_.~]+$/i', $checkoutToken)) {
            throw new InvalidArgumentException("Expected well-formed URI component for checkoutToken; '{$checkoutToken}' given");
        }

        $this->checkoutToken = $checkoutToken;

        $this->setUri("/v2/checkouts/{$this->checkoutToken}");

        return $this;
    }
}
