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

class UpdatePaymentByOrderId extends Request
{
    /**
     * @var array $data
     */
    protected $data = [
        'merchantReference' => [
            'type' => 'string',
            'length' => 128,
            'required' => true
        ]
    ];

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
            throw new PrerequisiteNotMetException('Cannot send an UpdatePaymentByOrderId Request without an Order ID (must call UpdatePaymentByOrderId::setOrderId before UpdatePaymentByOrderId::send)');
        }
    }

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setHttpMethod('PUT')
            ->configureBasicAuth()
        ;
    }

    /**
     * @param string $orderId
     * @return \Afterpay\SDK\HTTP\Request\UpdatePaymentByOrderId
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setOrderId($orderId)
    {
        if (is_int($orderId)) {
            $orderId = (string) abs($orderId);
        } elseif (is_string($orderId) && ! preg_match('/^\d+$/', $orderId)) {
            throw new InvalidArgumentException("Expected numeric orderId; '{$orderId}' given");
        } elseif (! is_string($orderId)) {
            throw new InvalidArgumentException('Expected numeric orderId; ' . gettype($orderId) . ' given');
        }

        $this->orderId = $orderId;

        $this->setUri("/v2/payments/{$this->orderId}");

        return $this;
    }
}
