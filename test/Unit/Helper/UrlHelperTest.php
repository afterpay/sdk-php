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

namespace Afterpay\SDK\Test\Unit\Helper;

require_once __DIR__ . '/../../autoload.php';

use Afterpay\SDK\Helper\UrlHelper;
use PHPUnit\Framework\TestCase;

class UrlHelperTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testAuSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'AU', 'sandbox');

        $this->assertEquals('https://portal.sandbox.afterpay.com/au/merchant/order/0', $url);
    }

    public function testAuProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'AU', 'production');

        $this->assertEquals('https://portal.afterpay.com/au/merchant/order/0', $url);
    }

    public function testCaSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'CA', 'sandbox');

        $this->assertEquals('https://portal.sandbox.afterpay.com/ca/merchant/order/0', $url);
    }

    public function testCaProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'CA', 'production');

        $this->assertEquals('https://portal.afterpay.com/ca/merchant/order/0', $url);
    }

    public function testEsSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'ES', 'sandbox');

        $this->assertEquals('https://merchant.sandbox.clearpay.com/orders/details/0', $url);
    }

    public function testEsProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'ES', 'production');

        $this->assertEquals('https://merchant.clearpay.com/orders/details/0', $url);
    }

    public function testFrSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'FR', 'sandbox');

        $this->assertEquals('https://merchant.sandbox.clearpay.com/orders/details/0', $url);
    }

    public function testFrProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'FR', 'production');

        $this->assertEquals('https://merchant.clearpay.com/orders/details/0', $url);
    }

    public function testGbSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'GB', 'sandbox');

        $this->assertEquals('https://portal.sandbox.clearpay.co.uk/uk/merchant/order/0', $url);
    }

    public function testGbProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'GB', 'production');

        $this->assertEquals('https://portal.clearpay.co.uk/uk/merchant/order/0', $url);
    }

    public function testItSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'IT', 'sandbox');

        $this->assertEquals('https://merchant.sandbox.clearpay.com/orders/details/0', $url);
    }

    public function testItProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'IT', 'production');

        $this->assertEquals('https://merchant.clearpay.com/orders/details/0', $url);
    }

    public function testNzSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'NZ', 'sandbox');

        $this->assertEquals('https://portal.sandbox.afterpay.com/nz/merchant/order/0', $url);
    }

    public function testNzProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'NZ', 'production');

        $this->assertEquals('https://portal.afterpay.com/nz/merchant/order/0', $url);
    }

    public function testUkSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'UK', 'sandbox');

        $this->assertEquals('https://portal.sandbox.clearpay.co.uk/uk/merchant/order/0', $url);
    }

    public function testUkProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'UK', 'production');

        $this->assertEquals('https://portal.clearpay.co.uk/uk/merchant/order/0', $url);
    }

    public function testUsSandboxMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'US', 'sandbox');

        $this->assertEquals('https://portal.sandbox.afterpay.com/us/merchant/order/0', $url);
    }

    public function testUsProductionMerchantPortalUrl()
    {
        $url = UrlHelper::generateMerchantPortalOrderUrl('0', 'US', 'production');

        $this->assertEquals('https://portal.afterpay.com/us/merchant/order/0', $url);
    }
}
