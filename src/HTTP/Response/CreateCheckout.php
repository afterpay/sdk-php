<?php

namespace Afterpay\SDK\HTTP\Response;

use Afterpay\SDK\HTTP\Response;

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
     * @return \Afterpay\SDK\HTTP\Response\CreateCheckout
     */
    public function afterReceive()
    {
        if ($this->isSuccessful()) {
            $obj = $this->getParsedBody();

            if (!is_null($obj)) {
                if (strtolower(\Afterpay\SDK\HTTP::getApiEnvironment()) == 'production') {
                    $portalDomain = 'portal.afterpay.com';
                } else {
                    $portalDomain = 'portal.sandbox.afterpay.com';
                }
                $obj->redirectCheckoutUrl = "https://{$portalDomain}/checkout/?token={$obj->token}";

                $this->setRawBody(json_encode($obj));
            }
        }

        return $this;
    }
}
