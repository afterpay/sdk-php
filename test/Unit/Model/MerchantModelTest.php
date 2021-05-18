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

namespace Afterpay\SDK\Test\Unit\Model;

require_once __DIR__ . '/../../autoload.php';

use PHPUnit\Framework\TestCase;

class MerchantModelTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testPopupOriginUrlNotRequiredIfRedirectConfirmAndCancelUrlsProvided()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $merchant = new \Afterpay\SDK\Model\Merchant();
        $merchant->setRedirectConfirmUrl('a://a');
        $merchant->setRedirectCancelUrl('a://a');

        $this->assertCount(0, $merchant->getValidationErrors());
    }

    public function testRedirectConfirmAndCancelUrlsNotRequiredIfPopupOriginUrlProvided()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $merchant = new \Afterpay\SDK\Model\Merchant();
        $merchant->setPopupOriginUrl('a://a');

        $this->assertCount(0, $merchant->getValidationErrors());
    }

    public function testRedirectConfirmUrlRequiredIfRedirectCancelUrlProvided()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $merchant = new \Afterpay\SDK\Model\Merchant();
        $merchant->setRedirectCancelUrl('a://a');
        $errors = $merchant->getValidationErrors();

        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('redirectConfirmUrl', $errors);
        $this->assertCount(1, $errors['redirectConfirmUrl']);
        $this->assertEquals('redirectConfirmUrl and redirectCancelUrl are required if popupOriginUrl is not provided', $errors['redirectConfirmUrl'][0]);
    }

    public function testRedirectCancelUrlRequiredIfRedirectConfirmUrlProvided()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $merchant = new \Afterpay\SDK\Model\Merchant();
        $merchant->setRedirectConfirmUrl('a://a');
        $errors = $merchant->getValidationErrors();

        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('redirectCancelUrl', $errors);
        $this->assertCount(1, $errors['redirectCancelUrl']);
        $this->assertEquals('redirectConfirmUrl and redirectCancelUrl are required if popupOriginUrl is not provided', $errors['redirectCancelUrl'][0]);
    }

    public function testEitherRedirectConfirmAndCancelUrlsOrPopupOriginUrlRequired()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $merchant = new \Afterpay\SDK\Model\Merchant();
        $errors = $merchant->getValidationErrors();

        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('redirectConfirmUrl', $errors);
        $this->assertCount(1, $errors['redirectConfirmUrl']);
        $this->assertEquals('redirectConfirmUrl and redirectCancelUrl are required if popupOriginUrl is not provided', $errors['redirectConfirmUrl'][0]);
        $this->assertArrayHasKey('redirectCancelUrl', $errors);
        $this->assertCount(1, $errors['redirectCancelUrl']);
        $this->assertEquals('redirectConfirmUrl and redirectCancelUrl are required if popupOriginUrl is not provided', $errors['redirectCancelUrl'][0]);
        $this->assertArrayHasKey('popupOriginUrl', $errors);
        $this->assertCount(1, $errors['popupOriginUrl']);
        $this->assertEquals('popupOriginUrl is required if redirectConfirmUrl and redirectCancelUrl are not provided', $errors['popupOriginUrl'][0]);
    }
}
