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

namespace Afterpay\SDK\Test;

use Afterpay\SDK\Config;
use Afterpay\SDK\MerchantAccount;
use Afterpay\SDK\HTTP;
use Afterpay\SDK\Helper\ArrayHelper;

class ConsumerSimulator
{
    /**
     * @var array $globalBaseUrls
     */
    private $globalBaseUrls = [
        'EU' => [
            'portal' => 'https://portal.sandbox.clearpay.co.uk',
            'portalapi' => 'https://portalapi.eu-sandbox.clearpay.co.uk',
            'pay' => 'https://pay.eu-sandbox.afterpay.com'
        ],
        'NA' => [
            'portal' => 'https://portal.us-sandbox.afterpay.com',
            'portalapi' => 'https://portalapi.us-sandbox.afterpay.com',
            'pay' => 'https://pay.us-sandbox.afterpay.com'
        ],
        'OC' => [
            'portal' => 'https://portal-sandbox.afterpay.com',
            'portalapi' => 'https://portalapi-sandbox.afterpay.com',
            'pay' => 'https://pay-sandbox.afterpay.com'
        ]
    ];

    /**
     * @var resource $ch
     */
    private $ch;

    private $countryCode;
    private $portalBaseUrl;
    private $portalapiBaseUrl;
    private $payBaseUrl;
    private $regionCode;
    private $merchantCurrency;
    private $consumerEmail;
    private $consumerPassword;
    private $storedCookies;
    private $traceId;
    private $preferredCardToken;

    /**
     * @param string $url
     * @param array $headers
     * @param string $postbody
     */
    private function curlInit($url, $headers = [], $postbody = null)
    {
        $this->ch = curl_init();

        # Boolean options
        if (!is_null($postbody)) {
            curl_setopt($this->ch, CURLOPT_POST, true);
        } else {
            curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);

        # String options
        curl_setopt($this->ch, CURLOPT_URL, $url);
        if (!empty($this->storedCookies)) {
            curl_setopt($this->ch, CURLOPT_COOKIE, implode('; ', $this->storedCookies));
        }
        if (!is_null($postbody)) {
            $headers[] = 'Content-Length: ' . strlen($postbody);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postbody);
        }

        # Array options
        if (!empty($headers)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }

        $composer_json = Config::get('composerJson');
        $php_version_str = phpversion();
        $curl_version_arr = curl_version();
        $curl_version_str = $curl_version_arr[ 'version' ];
        $merchantId = Config::get('merchantId');

        $ua_str = "afterpay-sdk-php/{$composer_json->version}";
        $ua_str .= ' (ConsumerSimulator';
        $ua_str .= "; PHP/{$php_version_str}";
        $ua_str .= "; cURL/{$curl_version_str}";
        $ua_str .= "; Merchant/{$merchantId}";
        $ua_str .= ')';

        curl_setopt($this->ch, CURLOPT_USERAGENT, $ua_str);
    }

    /**
     * @param \StdClass $responseObj
     */
    private function curlSendAndClose(&$responseObj)
    {
        $responseObj->rawResponseStr = curl_exec($this->ch);
        $responseObj->requestRawHeaders = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
        $responseObj->curlErrno = curl_errno($this->ch);
        $responseObj->curlError = curl_error($this->ch);
        $responseObj->responseHttpStatusCode = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE);
        $responseObj->responseContentType = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);

        curl_close($this->ch);

        $this->ch = null;
    }

    /**
     * @param \StdClass $responseObj
     */
    private function parseResponse(&$responseObj)
    {
        $rs = str_replace("\r\n", "\n", $responseObj->rawResponseStr); # Warning: this manipulates the raw response data!

        $response_parts = explode("\n\n", $rs);

        $response_header_groups = [];
        $response_headers = [];

        if (stripos($rs, 'HTTP/1.1 100') === 0 || stripos($rs, 'HTTP/2 100') === 0) {
            $response_header_groups[] = array_shift($response_parts);
        }

        $response_header_groups[] = array_shift($response_parts);

        /**
         * @todo Simplify this. We don't need to store all headers and then separately extract cookies.
         *       It would be simpler to extract cookies directly from the raw header string.
         */
        for ($i = 0; $i < count($response_header_groups); $i++) {
            $group_parts = explode("\n", $response_header_groups[$i]);
            for ($j = 0; $j < count($group_parts); $j++) {
                $strpos = strpos($group_parts[$j], ':');
                if ($strpos !== false) {
                    $header_key = trim(strtolower(substr($group_parts[$j], 0, $strpos)));
                    $header_value = trim(substr($group_parts[$j], $strpos + 1));
                    if (array_key_exists($header_key, $response_headers)) {
                        if (is_string($response_headers[$header_key])) {
                            $response_headers[$header_key] = [$response_headers[$header_key]];
                        }
                        $response_headers[$header_key][] = $header_value;
                    } else {
                        $response_headers[$header_key] = $header_value;
                    }
                }
            }
        }

        # Extract cookies
        if (array_key_exists('set-cookie', $response_headers)) {
            if (is_string($response_headers['set-cookie'])) {
                # One cookie
                $cookies = [$response_headers['set-cookie']];
            } elseif (is_array($response_headers['set-cookie'])) {
                # Multiple cookies
                $cookies = $response_headers['set-cookie'];
            }

            for ($i = 0; $i < count($cookies); $i++) {
                $cookie_parts = explode(';', $cookies[$i]);
                $this->storedCookies[] = $cookie_parts[0];
            }
        }

        $response_body = implode("\n", $response_parts);

        $responseObj->responseHeaders = $response_headers;
        $responseObj->responseRawBody = $response_body;

        if (preg_match('/application\/json/', $responseObj->responseContentType)) {
            $responseObj->responseParsedBody = json_decode($responseObj->responseRawBody);
        }
    }

    /**
     * @param string $url
     * @param array $headers
     * @param string $postbody
     * @return \StdClass $responseObj
     */
    private function sendAndLoad($url, $headers = [], $postbody = null)
    {
        $responseObj = new \StdClass();

        $this->curlInit($url, $headers, $postbody);
        $this->curlSendAndClose($responseObj);
        $this->parseResponse($responseObj);

        return $responseObj;
    }

    private function parseCountryCode($updateMerchantCurrency = false)
    {
        switch ($this->countryCode) {
            case 'AU':
                $this->regionCode = 'OC';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'AUD';
                }
                break;

            case 'CA':
                $this->regionCode = 'NA';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'CAD';
                }
                break;

            case 'ES':
                $this->regionCode = 'EU';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'EUR';
                }
                break;

            case 'FR':
                $this->regionCode = 'EU';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'EUR';
                }
                break;

            case 'GB':
            case 'UK':
                $this->regionCode = 'EU';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'GBP';
                }
                break;

            case 'IT':
                $this->regionCode = 'EU';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'EUR';
                }
                break;

            case 'NZ':
                $this->regionCode = 'OC';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'NZD';
                }
                break;

            case 'US':
                $this->regionCode = 'NA';
                if ($updateMerchantCurrency) {
                    $this->merchantCurrency = 'USD';
                }
                break;
        }

        $this->portalBaseUrl = $this->globalBaseUrls[$this->regionCode]['portal'];
        $this->portalapiBaseUrl = $this->globalBaseUrls[$this->regionCode]['portalapi'];
        $this->payBaseUrl = $this->globalBaseUrls[$this->regionCode]['pay'];
    }

    /**
     * During this step, the consumer may be redirected from the merchant's region to their own.
     */
    private function lookup($consumerEmail, $checkoutToken)
    {
        $url = "{$this->portalapiBaseUrl}/portal/consumers/emails/lookup";

        $postheaders = [
            'Content-Type: application/json'
        ];

        $data = [
            'email' => $consumerEmail,
            'transactionToken' => $checkoutToken
        ];
        $postbody = json_encode($data);

        $responseObj = $this->sendAndLoad($url, $postheaders, $postbody);

        if ($responseObj->responseHttpStatusCode != 200) {
            if (preg_match('/^3/', $responseObj->responseHttpStatusCode)) {
                throw new \Exception("Received an HTTP {$responseObj->responseHttpStatusCode} redirect to '{$responseObj->responseHeaders['location']}' during lookup");
            }
            if (is_object($responseObj->responseParsedBody) && property_exists($responseObj->responseParsedBody, 'errorId')) {
                throw new \Exception("Received an HTTP {$responseObj->responseHttpStatusCode} response during lookup (errorId: \"{$responseObj->responseParsedBody->errorId}\")");
            }
            throw new \Exception("Received an HTTP {$responseObj->responseHttpStatusCode} response during lookup");
        } elseif (is_object($responseObj->responseParsedBody) && property_exists($responseObj->responseParsedBody, 'countryCode')) {
            if (strtoupper($responseObj->responseParsedBody->countryCode) == $this->countryCode) {
                # Consumer countryCode matches the Merchant countryCode.
                return;
            }

            $this->countryCode = strtoupper($responseObj->responseParsedBody->countryCode);

            $this->parseCountryCode(false);

            return;
        }

        throw new \Exception('lookup did not complete as expected');
    }

    /**
     * @param string $username
     * @param string $password
     * @throws \Exception
     */
    private function login($username, $password)
    {
        $url = "{$this->portalapiBaseUrl}/portal/consumers/auth/login";
        $postheaders = [
            'Content-Type: application/x-www-form-urlencoded'
        ];
        $postbody = http_build_query([
            'username' => $username,
            'password' => $password
        ]);

        $responseObj = $this->sendAndLoad($url, $postheaders, $postbody);

        if ($responseObj->responseParsedBody) {
            if (property_exists($responseObj->responseParsedBody, 'user') && $responseObj->responseParsedBody->user->requires2fa) {
                throw new \Exception('user.requires2fa');
            } elseif ($responseObj->responseHttpStatusCode == 200 && $responseObj->responseParsedBody->status == 'success') {
                return;
            } else {
                throw new \Exception("login did not complete as expected. Received HTTP {$responseObj->responseHttpStatusCode} response with raw body (truncated to 512 characters): " . substr($responseObj->responseRawBody, 0, 512));
            }
        }

        throw new \Exception('login did not complete as expected');
    }

    /**
     * @throws \Exception
     */
    private function request2faCode()
    {
        $url = "{$this->portalapiBaseUrl}/portal/consumers/auth/2fa/code";
        $postheaders = [
            'Content-Type: application/json'
        ];
        $postbody = json_encode([
            'sendBy' => 'SMS'
        ]);

        $responseObj = $this->sendAndLoad($url, $postheaders, $postbody);

        if ($responseObj->responseHttpStatusCode == 201) {
            return;
        }

        throw new \Exception('request2faCode did not complete as expected');
    }

    /**
     * @throws \Exception
     */
    private function validate2faCode()
    {
        $url = "{$this->portalapiBaseUrl}/portal/consumers/auth/2fa/check-code";
        $postheaders = [
            'Content-Type: application/json'
        ];
        $postbody = json_encode([
            'code' => '111111',
            'profilingSessionId' => ''
        ]);

        $responseObj = $this->sendAndLoad($url, $postheaders, $postbody);

        if ($responseObj->responseHttpStatusCode === 201) {
            return;
        }

        throw new \Exception('validate2faCode did not complete as expected');
    }

    /**
     * @param string $checkoutToken
     * @throws \Exception
     */
    private function startConsumerCheckout($checkoutToken)
    {
        $url = "{$this->portalapiBaseUrl}/portal/consumers/checkout/{$checkoutToken}/start";
        $postheaders = [
            'Content-Type: application/json'
        ];
        $postbody = json_encode([
            'deviceDetails' => new \StdClass()
        ]);

        $responseObj = $this->sendAndLoad($url, $postheaders, $postbody);

        if (property_exists($responseObj, 'responseParsedBody') && is_object($responseObj->responseParsedBody)) {
            if (!property_exists($responseObj->responseParsedBody, 'traceId')) {
                if (property_exists($responseObj->responseParsedBody, 'errorId')) {
                    throw new \Exception("startConsumerCheckout did not complete as expected. Received HTTP {$responseObj->responseHttpStatusCode} response with raw body (truncated to 512 characters): " . substr($responseObj->responseRawBody, 0, 512));
                }
                throw new \Exception("startConsumerCheckout response did not contain expected traceId. Actual properties: " . implode(', ', array_keys(get_object_vars($responseObj->responseParsedBody))));
            }
            $this->traceId = $responseObj->responseParsedBody->traceId;
            if (is_object($responseObj->responseParsedBody->preferredCard)) {
                $this->preferredCardToken = $responseObj->responseParsedBody->preferredCard->token;
            }

            if ($responseObj->responseHttpStatusCode == 200) {
                return;
            }
        } elseif ($responseObj->curlErrno) {
            throw new \Exception("startConsumerCheckout triggered cURL error #{$responseObj->curlErrno}: '{$responseObj->curlError}'");
        }

        throw new \Exception('startConsumerCheckout did not complete as expected');
    }

    /**
     * @param string $csc
     * @throws \Exception
     */
    private function setupPurchase($csc)
    {
        $url = "{$this->payBaseUrl}/topaz/paylater/purchase/setup";
        $postheaders = [
            'Content-Type: application/json'
        ];
        $data = [
            'cardSecurityCode' => $csc,
            'traceId' => $this->traceId
        ];
        if ($csc === '000' && !is_null($this->preferredCardToken)) {
            $data['token'] = $this->preferredCardToken;
        } else {
            $data['cardHolderName'] = 'TEST TEST';
            $data['cardNumber'] = '4000000000009979';
            $data['cardExpiryMonth'] = '01';
            $data['cardExpiryYear'] = date('y', strtotime('next year'));
            $countryCode = HTTP::getCountryCode();
            $mockData = MerchantAccount::generateMockData($countryCode);
            $data['address'] = [
                'city' => ArrayHelper::maybeGet('area1', $mockData),
                'country' => $countryCode,
                'postcode' => ArrayHelper::maybeGet('postcode', $mockData),
                'state' => ArrayHelper::maybeGet('region', $mockData),
                'street1' => ArrayHelper::maybeGet('line1', $mockData)
            ];
        }
        $postbody = json_encode($data);

        $responseObj = $this->sendAndLoad($url, $postheaders, $postbody);

        if ($responseObj->responseHttpStatusCode == 200) {
            return;
        }

        throw new \Exception('setupPurchase did not complete as expected');
    }

    /**
     * @param string $checkoutToken
     * @throws \Exception
     */
    private function confirmConsumerCheckout($checkoutToken)
    {
        $url = "{$this->portalapiBaseUrl}/portal/consumers/checkout/{$checkoutToken}/confirm";
        $postheaders = [
            'Content-Type: application/json'
        ];
        $postbody = '{}';

        $responseObj = $this->sendAndLoad($url, $postheaders, $postbody);

        if ($responseObj->responseHttpStatusCode != 200) {
            throw new \Exception("Received an HTTP {$responseObj->responseHttpStatusCode} response during confirmConsumerCheckout. Raw body (truncated to 512 characters): " . substr($responseObj->responseRawBody, 0, 512));
        } elseif (is_object($responseObj->responseParsedBody)) {
            if ($responseObj->responseParsedBody->status != 'SUCCESS') {
                throw new \Exception("Encountered a status of '{$responseObj->responseParsedBody->status}' during confirmConsumerCheckout");
            } elseif ($responseObj->responseParsedBody->callbackUrlQueryArgs != "&status=SUCCESS&orderToken={$checkoutToken}") {
                throw new \Exception("Encountered unexpected callbackUrlQueryArgs during confirmConsumerCheckout");
            }

            return;
        }

        throw new \Exception('confirmConsumerCheckout did not complete as expected');
    }

    /**
     * Note: When the ConsumerSimulator is first instantiated, it will configure itself to align with the merchant's region.
     *       As the "consumer" traverses the checkout screenflow, they may be identified as belonging to a different region,
     *       and the API URLs will be reconfigured accordingly.
     */
    public function __construct($countryCode = null)
    {
        $this->countryCode = $countryCode;
        if (is_null($countryCode)) {
            $this->countryCode = HTTP::getCountryCode();
        }

        $this->parseCountryCode(true);

        $this->consumerEmail = Config::get('test.consumerEmail');
        $this->consumerPassword = Config::get('test.consumerPassword');
        $this->storedCookies = [];
    }

    /**
     * @param string $checkoutToken
     * @param string $csc   Card Security Code (in Sandbox, "000" entered here will later simulate
     *                      an APPROVED status, and "051" a DECLINED status)
     * @throws \Exception
     */
    public function confirmPaymentSchedule($checkoutToken, $csc)
    {
        $lowerCountryCode = strtolower($this->countryCode);
        $url = "{$this->portalBaseUrl}/{$lowerCountryCode}/checkout/?token={$checkoutToken}";
        $responseObj = $this->sendAndLoad($url);

        $this->lookup($this->consumerEmail, $checkoutToken);

        try {
            $this->login($this->consumerEmail, $this->consumerPassword);
        } catch (\Exception $e) {
            if ($e->getMessage() == "user.requires2fa") {
                $this->request2faCode();
                $this->validate2faCode();
            } else {
                throw $e;
            }
        }

        $this->startConsumerCheckout($checkoutToken);
        $this->setupPurchase($csc);
        $this->confirmConsumerCheckout($checkoutToken);
    }
}
