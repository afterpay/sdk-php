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

namespace Afterpay\SDK\HTTP\Response;

use Afterpay\SDK\Exception\PrerequisiteNotMetException;
use Afterpay\SDK\HTTP\Response;
use Afterpay\SDK\Model\PaymentEvent;
use Afterpay\SDK\Model\Refund;

class DeferredPaymentVoid extends Response
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \Afterpay\SDK\Model\Refund
     * @throws \Afterpay\SDK\Exception\PrerequisiteNotMetException
     */
    public function getRefund()
    {
        if (!$this->isSuccessful()) {
            throw new PrerequisiteNotMetException('Cannot get a Refund for an unsuccessful DeferredPaymentVoid');
        }

        $order = $this->getParsedBody();

        return new Refund($order->refunds[count($order->refunds) - 1]);
    }

    /**
     * @return \Afterpay\SDK\Model\PaymentEvent
     * @throws \Afterpay\SDK\Exception\PrerequisiteNotMetException
     */
    public function getPaymentEvent()
    {
        if (!$this->isSuccessful()) {
            throw new PrerequisiteNotMetException('Cannot get a PaymentEvent for an unsuccessful DeferredPaymentVoid');
        }

        $order = $this->getParsedBody();

        return new PaymentEvent($order->events[count($order->events) - 1]);
    }
}
