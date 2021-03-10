<?php

namespace Afterpay\SDK\HTTP\Response;

use Afterpay\SDK\HTTP\Response;
use Afterpay\SDK\MerchantAccount;

class CreateCheckout extends Response
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This method is called immediately after the HTTP response is received.
     *
     * Intended only for the `downgrade-to-api-v1` branch, as a workaround for v1 responses
     * missing the `redirectCheckoutUrl` property.
     *
     * WARNING: This method manipulates the raw HTTP response!
     *
     * @param MerchantAccount $merchantAccount
     *
     * @return \Afterpay\SDK\HTTP\Response\CreateCheckout
     */
    public function afterReceive(MerchantAccount $merchantAccount)
    {
        if ($this->isSuccessful()) {
            $obj = $this->getParsedBody();
            $countryCode = $merchantAccount->getCountryCode();
            $apiEnvironment = $merchantAccount->getApiEnvironment();
            $sandbox_suffix = '.sandbox';
            $prefix = "portal";
            $tld = 'afterpay.com/' . strtolower($countryCode);
            $tokenParam = '?token=';

            if (strlen($countryCode) == 2) {
                if (preg_match('/ES|FR|IT/', $countryCode)) {
                    $prefix = 'checkout';
                    $tld = 'clearpay.com'; # Southern Europe
                    $sandbox_suffix = '.sandbox';
                    $tokenParam = '';
                }
                if ($countryCode == 'GB') {
                    $tld = 'clearpay.co.uk/uk';
                }
            }
            if (!is_null($obj)) {
                if (strtolower($apiEnvironment) === 'production') {
                    $obj->redirectCheckoutUrl = "https://{$prefix}.{$tld}/checkout/{$tokenParam}{$obj->token}";
                } else {
                    $obj->redirectCheckoutUrl = "https://{$prefix}{$sandbox_suffix}.{$tld}/checkout/{$tokenParam}{$obj->token}";
                }
                $this->setRawBody(json_encode($obj));
            }
        }

        return $this;
    }
}
