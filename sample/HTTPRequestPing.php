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

$composer_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    require_once __DIR__ . '/../test/autoload.php';
}

use Afterpay\SDK\HTTP\Request\Ping as AfterpayPingRequest;

use Afterpay\SDK\Exception\NetworkException as AfterpayNetworkException;
use Afterpay\SDK\Exception\ParsingException as AfterpayParsingException;

if (! headers_sent()) {
    header('Content-Type: text/plain');
}

function tryPing($pingRequest)
{
    try {
        if ($pingRequest->send()) {
            # Success

            echo "Afterpay/HTTP is UP\n";
        } else {
            # A 3xx, 4xx, or 5xx series HTTP Response.
            # Please log the response code,
            # errorCode, errorId and message from the body.

            $pingResponse = $pingRequest->getResponse();
            $responseCode = $pingResponse->getHttpStatusCode();
            $contentType = $pingResponse->getContentTypeSimplified();

            if (is_object($body = $pingResponse->getParsedBody())) {
                $errorCode = $body->errorCode;
                $errorId = $body->errorId;
                $message = $body->message;

                echo "ERROR: Received unexpected HTTP {$responseCode} {$contentType} response from Afterpay with errorCode: {$errorCode}; errorId: {$errorId}; message: {$message}\n";
            }
        }
    } catch (AfterpayNetworkException $e) {
        # This generally indicates a transient network error, such as a connection reset
        # or client timeout.

        $curl_error_number = $e->getCode();
        $curl_error_message = $e->getMessage();

        echo "ERROR: Cannot connect to Afterpay via HTTP; caught Afterpay\SDK\Exception\NetworkException #{$curl_error_number}: '{$curl_error_message}'\n";
    } catch (AfterpayParsingException $e) {
        # This means that the SDK could not process the response
        # according to the Content-Type that the API declared.

        $contentType = $pingRequest->getResponse()->getContentTypeSimplified();
        $json_parsing_error_number = $e->getCode();
        $json_parsing_error_message = $e->getMessage();

        echo "ERROR: Received unparsable {$contentType} response from Afterpay; caught Afterpay\SDK\Exception\ParsingException #{$json_parsing_error_number}: '{$json_parsing_error_message}'\n";
    }
}



/**
 * Make a successful Ping request.
 */

$pingRequest = new AfterpayPingRequest();

tryPing($pingRequest);

# Expected output (regular expression):
/*~
Afterpay\/HTTP is UP
~*/



/**
 * Simulate receiving a 503 response from Afterpay.
 */

$pingRequest = new AfterpayPingRequest();
$pingRequest->setMockMode('alwaysReceiveServiceUnavailable');

tryPing($pingRequest);

# Expected output (regular expression):
/*~
ERROR: Received unexpected HTTP 503 application\/json response from Afterpay with errorCode: service_unavailable_mock; errorId: [0-9a-f]{16}; message: Service Unavailable \(Mock\)
~*/



/**
 * Simulate receiving a 403 response from Cloudflare.
 */

$pingRequest = new AfterpayPingRequest();
$pingRequest->addHeader('User-Agent', 'TestForbidden/1.0');

tryPing($pingRequest);

# Expected output (regular expression):
/*~
ERROR: Received unexpected HTTP 403 text\/html response from Afterpay with errorCode: non_json_response; errorId: ; message: Expected JSON response. Received: text\/html. Cloudflare Ray ID: [0-9a-f]{16}-[A-Z]{3}
~*/



/**
 * Simulate catching a NetworkException.
 */

$pingRequest = new AfterpayPingRequest();
$pingRequest->setMockMode('alwaysThrowNetworkException');

tryPing($pingRequest);

# Expected output (regular expression):
/*~
ERROR: Cannot connect to Afterpay via HTTP; caught Afterpay\\SDK\\Exception\\NetworkException #7: 'Connection timed out after 0 milliseconds \(mock\)'
~*/



/**
 * Simulate catching a ParsingException.
 */

$pingRequest = new AfterpayPingRequest();
$pingRequest->setMockMode('alwaysThrowParsingException');

tryPing($pingRequest);

# Expected output (regular expression):
/*~
ERROR: Received unparsable text\/plain response from Afterpay; caught Afterpay\\SDK\\Exception\\ParsingException #4: 'Syntax error \(mock\)'
~*/
