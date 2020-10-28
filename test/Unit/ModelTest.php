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

namespace Afterpay\SDK\Test\Unit;

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testZeroForModelAutomaticValidationEnabled()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(0); // falsy
        $this->assertFalse(\Afterpay\SDK\Model::getAutomaticValidationEnabled());
    }

    public function testNonZeroIntegerForModelAutomaticValidationEnabled()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(-1); // truthy
        $this->assertTrue(\Afterpay\SDK\Model::getAutomaticValidationEnabled());
    }

    public function testInvalidModelWithoutAutomaticValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false); // This line is needed because PHPUnit loaded the class in a previous test, and the property is static.
        $consumer = new \Afterpay\SDK\Model\Consumer();
        $consumer->setPhoneNumber(911);
        $this->assertEquals(911, $consumer->getPhoneNumber());
    }

    public function testInvalidTypeForModelWithAutomaticValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled('true'); // truthy

        $consumer = new \Afterpay\SDK\Model\Consumer();

        try {
            $consumer->setPhoneNumber(911);
        } catch (\Exception $e) {
            $this->assertEquals('Afterpay\SDK\Exception\InvalidModelException', get_class($e));
            $this->assertEquals("Expected string for Afterpay\SDK\Model\Consumer::\$phoneNumber; integer given", $e->getMessage());
        }
    }

    public function testInvalidStringLengthForModelWithAutomaticValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(31); // truthy

        $consumer = new \Afterpay\SDK\Model\Consumer();

        try {
            $consumer->setPhoneNumber('................................+');
        } catch (\Exception $e) {
            $this->assertEquals('Afterpay\SDK\Exception\InvalidModelException', get_class($e));
            $this->assertEquals("Expected maximum of 32 characters for Afterpay\SDK\Model\Consumer::\$phoneNumber; 33 characters given", $e->getMessage());
        }
    }

    public function testInvalidStringLengthForModelWithManualValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(''); // falsy

        $consumer = new \Afterpay\SDK\Model\Consumer();
        $consumer->setPhoneNumber('................................++');

        $this->assertFalse($consumer->isValid());

        $errors = $consumer->getValidationErrors();
        $this->assertArrayHasKey('phoneNumber', $errors);
        $this->assertEquals(1, count($errors[ 'phoneNumber' ]));
        $this->assertEquals("Expected maximum of 32 characters for Afterpay\SDK\Model\Consumer::\$phoneNumber; 34 characters given", $errors[ 'phoneNumber' ][0]);
    }

    public function testItemQuantityBelowMinimumWithAutomaticValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

        $item = new \Afterpay\SDK\Model\Item();
        try {
            $item->setQuantity(-2147483649);
        } catch (\Exception $e) {
            $this->assertEquals('Afterpay\SDK\Exception\InvalidModelException', get_class($e));
            $this->assertEquals("Expected minimum of -2147483648 for Afterpay\SDK\Model\Item::\$quantity; -2147483649 given", $e->getMessage());
        }
    }

    public function testItemQuantityExceedsMaximumWithAutomaticValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

        $item = new \Afterpay\SDK\Model\Item();
        try {
            $item->setQuantity(2147483648);
        } catch (\Exception $e) {
            $this->assertEquals('Afterpay\SDK\Exception\InvalidModelException', get_class($e));
            $this->assertEquals("Expected maximum of 2147483647 for Afterpay\SDK\Model\Item::\$quantity; 2147483648 given", $e->getMessage());
        }
    }

    public function testMoneyAmountSetAsFloatWithAutomaticValidationButWithoutAutomaticFormatting()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(false);

        try {
            $money = new \Afterpay\SDK\Model\Money([
                'amount' => 100.00,
                'currency' => 'AUD'
            ]);
        } catch (\Exception $e) {
            $this->assertEquals('Afterpay\SDK\Exception\InvalidModelException', get_class($e));
            $this->assertEquals('Expected string for Afterpay\SDK\Model\Money::$amount; double given', $e->getMessage());
        }
    }

    public function testMoneyAmountSetAsIntegerWithAutomaticValidationButWithoutAutomaticFormatting()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(false);

        try {
            $money = new \Afterpay\SDK\Model\Money([
                'amount' => 100,
                'currency' => 'AUD'
            ]);
        } catch (\Exception $e) {
            $this->assertEquals('Afterpay\SDK\Exception\InvalidModelException', get_class($e));
            $this->assertEquals('Expected string for Afterpay\SDK\Model\Money::$amount; integer given', $e->getMessage());
        }
    }

    public function testMoneyAmountSetAsIntegerWithAutomaticFormatting()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => 0xff,
            'currency' => 'AUD'
        ]);

        $this->assertSame('255.00', $money->getAmount());
    }

    public function testMoneyAmountSetAsFloatWithTooManyDecimalPlacesAndAutomaticFormattingOne()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => 100.995,
            'currency' => 'AUD'
        ]);

        $this->assertSame('101.00', $money->getAmount());
    }

    public function testMoneyAmountSetAsFloatWithTooManyDecimalPlacesAndAutomaticFormattingTwo()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $amountIncludingTax = 50 * 1.1;

        $data = [
            'amount' => $amountIncludingTax,
            'currency' => 'AUD'
        ];

        if (version_compare(phpversion(), '7.1', '>=')) {
            $this->assertSame('{"amount":55.00000000000001,"currency":"AUD"}', json_encode($data));
        } else {
            $this->assertSame('{"amount":55,"currency":"AUD"}', json_encode($data));
        }

        $money = new \Afterpay\SDK\Model\Money($data);

        $this->assertSame('{"amount":"55.00","currency":"AUD"}', json_encode($money));
    }

    public function testMoneyAmountSetAsIncorrectlyFormattedStringWithAutomaticFormattingOne()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => '100',
            'currency' => 'AUD'
        ]);

        $this->assertSame('100.00', $money->getAmount());
    }

    public function testMoneyAmountSetAsIncorrectlyFormattedStringWithAutomaticFormattingTwo()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => '100.000',
            'currency' => 'AUD'
        ]);

        $this->assertSame('100.00', $money->getAmount());
    }

    public function testMoneyAmountSetAsIncorrectlyFormattedStringWithAutomaticFormattingThree()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => '$1,000.009',
            'currency' => 'AUD'
        ]);

        $this->assertSame('1000.01', $money->getAmount());
    }

    public function testMoneyAmountSetAsIncorrectlyFormattedStringWithAutomaticFormattingFour()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => 'aaa1aaa0aaa0aaa.aaa0aaa0aaa.aaa9aaa',
            'currency' => 'AUD'
        ]);

        $this->assertSame('100.01', $money->getAmount());
    }

    public function testMoneyAmountSetAsIncorrectlyFormattedStringWithAutomaticFormattingFive()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => '£0',
            'currency' => 'GBP'
        ]);

        $this->assertSame('0.00', $money->getAmount());
    }

    public function testMoneyAmountSetAsEmptyStringWithAutomaticFormatting()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => '',
            'currency' => 'AUD'
        ]);

        $this->assertSame('0.00', $money->getAmount());
    }

    public function testMoneyAmountSetAsNullWithAutomaticFormatting()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        \Afterpay\SDK\Model::setAutomaticFormattingEnabled(true);

        $money = new \Afterpay\SDK\Model\Money([
            'amount' => null,
            'currency' => 'AUD'
        ]);

        $this->assertSame('0.00', $money->getAmount());
    }

    public function testMoneyAmountNotSetOne()
    {
        $money = new \Afterpay\SDK\Model\Money();

        $this->assertSame('0.00', $money->getAmount());
    }

    public function testMoneyAmountNotSetTwo()
    {
        $money = new \Afterpay\SDK\Model\Money();

        $this->assertEquals('{"amount":"0.00"}', json_encode($money));
    }

    /**
     * @todo When the model is instantiated with data in the constructor,
     *       automatic validation exceptions should be triggered.
     *       Otherwise, isValid should be checked before sending the request.
     */
    /*public function testConsumerEmailNotSetWithAutomaticValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);

        try {
            $consumer = new \Afterpay\SDK\Model\Consumer([]);
        } catch (\Exception $e) {
            $this->assertEquals('Afterpay\SDK\Exception\InvalidModelException', get_class($e));
            $this->assertEquals("Required property missing: Afterpay\SDK\Model\Consumer::\$email", $e->getMessage());
        }
    }*/

    public function testConsumerEmailNotSetWithoutAutomaticValidation()
    {
        \Afterpay\SDK\Model::setAutomaticValidationEnabled(false);

        $consumer = new \Afterpay\SDK\Model\Consumer();

        $this->assertFalse($consumer->isValid());

        $errors = $consumer->getValidationErrors();
        $this->assertArrayHasKey('email', $errors);
        $this->assertEquals(1, count($errors[ 'email' ]));
        $this->assertEquals("Required property missing: Afterpay\SDK\Model\Consumer::\$email", $errors[ 'email' ][ 0 ]);
    }
}
