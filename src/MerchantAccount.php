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

namespace Afterpay\SDK;

use Afterpay\SDK\Exception\InvalidArgumentException;
use Afterpay\SDK\PersistentStorage;
use Afterpay\SDK\Model\Money;

final class MerchantAccount
{
    public static function generateMockData($countryCode = 'AU', $field = null)
    {
        $data = [];

        switch ($countryCode) {
            case 'CA':
                $data[ 'currency' ] = 'CAD';
                $data[ 'phoneNumber' ] = '1 250 555 0199';
                $data[ 'line1' ] = '111 Wellington St';
                $data[ 'area1' ] = 'Ottawa';
                $data[ 'region' ] = 'ON';
                $data[ 'postcode' ] = 'K1A0A9';
                $data[ 'countryCode' ] = 'CA';
                break;

            case 'ES':
                $data[ 'currency' ] = 'EUR';
                $data[ 'phoneNumber' ] = '620 210 714';
                $data[ 'line1' ] = 'Fuente de los Galápagos';
                $data[ 'line2' ] = 'Parque de El Retiro';
                $data[ 'area1' ] = 'Madrid';
                $data[ 'region' ] = 'Comunidad de Madrid';
                $data[ 'postcode' ] = '28009';
                $data[ 'countryCode' ] = 'ES';
                break;

            case 'FR':
                $data[ 'currency' ] = 'EUR';
                $data[ 'phoneNumber' ] = '06 20 21 07 14';
                $data[ 'line1' ] = '99 Rue de Rivoli';
                $data[ 'area1' ] = 'Paris';
                $data[ 'region' ] = 'Île-de-France';
                $data[ 'postcode' ] = '75004';
                $data[ 'countryCode' ] = 'FR';
                break;

            case 'GB':
            case 'UK':
                $data[ 'currency' ] = 'GBP';
                $data[ 'phoneNumber' ] = '07123456789';
                $data[ 'line1' ] = 'Town Hall';
                $data[ 'area1' ] = 'Manchester';
                $data[ 'postcode' ] = 'M602LA';
                $data[ 'countryCode' ] = 'GB';
                break;

            case 'NZ':
                $data[ 'currency' ] = 'NZD';
                $data[ 'phoneNumber' ] = '64225840132';
                $data[ 'line1' ] = '206 Jervois Rd';
                $data[ 'area1' ] = 'Ponsonby';
                $data[ 'region' ] = 'Auckland';
                $data[ 'postcode' ] = '1011';
                $data[ 'countryCode' ] = 'NZ';
                break;

            case 'IT':
                $data[ 'currency' ] = 'EUR';
                $data[ 'phoneNumber' ] = '300 20210714';
                $data[ 'line1' ] = 'Fontana di Esculapio';
                $data[ 'line2' ] = 'Piazzale del Fiocco';
                $data[ 'area1' ] = 'Rome';
                $data[ 'region' ] = 'Lazio';
                $data[ 'postcode' ] = '00197';
                $data[ 'countryCode' ] = 'IT';
                break;

            case 'US':
                $data[ 'currency' ] = 'USD';
                $data[ 'phoneNumber' ] = '12124200917';
                $data[ 'line1' ] = '222 Kearny Street';
                $data[ 'area1' ] = 'San Francisco';
                $data[ 'region' ] = 'CA';
                $data[ 'postcode' ] = '94108';
                $data[ 'countryCode' ] = 'US';
                break;

            case 'AU':
            default:
                $data[ 'currency' ] = 'AUD';
                $data[ 'phoneNumber' ] = '61420200910';
                $data[ 'line1' ] = 'Level 23';
                $data[ 'line2' ] = '2 Southbank Boulevard';
                $data[ 'area1' ] = 'Southbank';
                $data[ 'region' ] = 'VIC';
                $data[ 'postcode' ] = '3006';
                $data[ 'countryCode' ] = 'AU';
        }

        if (! is_null($field)) {
            return $data[$field];
        }

        return $data;
    }

    /**
     * @var string $merchantId
     */
    private $merchantId;

    /**
     * @var string $secretKey
     */
    private $secretKey;

    /**
     * @var string $countryCode ISO 3166-1 alpha-2 two character country code of the Merchant Account.
     */
    private $countryCode;

    /**
     * @var string $apiEnvironment
     */
    private $apiEnvironment = 'sandbox';

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return \Afterpay\SDK\MerchantAccount
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     * @return \Afterpay\SDK\MerchantAccount
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return \Afterpay\SDK\MerchantAccount
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiEnvironment()
    {
        return $this->apiEnvironment;
    }

    /**
     * @param string $apiEnvironment
     * @return \Afterpay\SDK\MerchantAccount
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setApiEnvironment($apiEnvironment)
    {
        /**
         * @todo Reuse the enumi rules in the Config class instead defining duplicate code here
         *       and in \Afterpay\SDK\HTTP.
         */
        if (! is_string($apiEnvironment)) {
            throw new InvalidArgumentException("Expected string; " . gettype($apiEnvironment) . ' given');
        } elseif (! preg_match('/^sandbox|production$/i', $apiEnvironment)) {
            throw new InvalidArgumentException("Expected 'sandbox' or 'production'; '{$apiEnvironment}' given");
        }

        $this->apiEnvironment = $apiEnvironment;

        return $this;
    }

    /**
     * @return float
     */
    public function getOrderMinimumAsFloat()
    {
        /**
         * @todo introduce an if block here, e.g.:
         *
         *           if (PersistentStorage::isConfigured()) {
         *              $min = PersistentStorage::get('orderMinimum', $this);
         *           } elseif ($this->isSetup()) {
         *              # Make the HTTP request...
         *           } else {
         *              # Throw an Exception - trying to access config but nothing is configured...
         *           }
         */
        $min = PersistentStorage::get('orderMinimum', $this);

        if ($min instanceof Money) {
            return (float) $min->getAmount();
        }

        return -INF;
    }

    /**
     * @return float
     */
    public function getOrderMaximumAsFloat()
    {
        /**
         * @todo introduce an if block here, e.g.:
         *
         *           if (PersistentStorage::isConfigured()) {
         *              $min = PersistentStorage::get('orderMinimum', $this);
         *           } elseif ($this->isSetup()) {
         *              # Make the HTTP request...
         *           } else {
         *              # Throw an Exception - trying to access config but nothing is configured...
         *           }
         */
        $max = PersistentStorage::get('orderMaximum', $this);

        if ($max instanceof Money) {
            return (float) $max->getAmount();
        }

        return INF;
    }

    public function __construct($merchantId = null, $secretKey = null, $apiEnvironment = null)
    {
        if (! is_null($merchantId) && ! is_null($secretKey)) {
            $this->merchantId = $merchantId;
            $this->secretKey = $secretKey;
        }

        if (! is_null($apiEnvironment)) {
            $this->apiEnvironment = $apiEnvironment;
        }
    }

    /**
     * @return bool
     */
    public function isSetup()
    {
        return ! is_null($this->merchantId) && ! is_null($this->secretKey);
    }
}
