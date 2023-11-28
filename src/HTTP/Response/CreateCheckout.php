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

/**
 * This file needs to be here to avoid a debris issue
 * See INC-1751; #apt-im-01124
 */

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
     * Updates the redirectCheckoutUrl to include the deviceId from iQ Pixel.
     *
     * WARNING: This method manipulates the raw HTTP response!
     *
     * @return \Afterpay\SDK\HTTP\Response\CreateCheckout
     */
    public function afterReceive()
    {
        if ($this->isSuccessful()) {
            $bodyObj = $this->getParsedBody();

            if (!is_null($bodyObj)) {
                // Append iQ Pixel device ID
                $cookieName = "apt_pixel";

                if (isset($_COOKIE[$cookieName])) {
                    $decodedCookie = base64_decode($_COOKIE[$cookieName], true);

                    if ($decodedCookie) {
                        $cookieObj = json_decode($decodedCookie, false);

                        if (isset($cookieObj->deviceId)) {
                            $bodyObj->redirectCheckoutUrl .= "&deviceId={$cookieObj->deviceId}";

                            $this->setRawBody(json_encode($bodyObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                        }
                    }
                }
            }
        }

        return $this;
    }
}
