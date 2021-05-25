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

class GetPaymentByOrderId extends Request
{
    /**
     * @var string $orderId
     *
     * @todo Make a flexible array for all path params similar to body data.
     */
    protected $orderId;

    /**
     * @throws \Afterpay\SDK\Exception\PrerequisiteNotMetException
     */
    protected function beforeSend()
    {
        if (is_null($this->orderId)) {
            throw new PrerequisiteNotMetException('Cannot send a GetPaymentByOrderId Request without an order ID (must call GetPaymentByOrderId::setOrderId before GetPaymentByOrderId::send)');
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
     * @param string $orderId
     * @return \Afterpay\SDK\HTTP\Request\GetPaymentByOrderId
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setOrderId($orderId)
    {
        if (! is_string($orderId)) {
            throw new InvalidArgumentException('Expected string for orderId; ' . gettype($orderId) . ' given');
        } elseif (strlen($orderId) < 1) {
            throw new InvalidArgumentException('Expected non-empty string for orderId; empty string given');
        } elseif (! preg_match('/^[\'a-z0-9!_.%*()~-]+$/i', $orderId)) {
            throw new InvalidArgumentException("Expected well-formed URI component for orderId; '{$orderId}' given");
        }

        $this->orderId = $orderId;

        $this->setUri("/v2/payments/{$this->orderId}");

        return $this;
    }
}
