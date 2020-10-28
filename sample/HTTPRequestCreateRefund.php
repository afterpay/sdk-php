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

$composer_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    require_once __DIR__ . '/../test/autoload.php';
}

use Afterpay\SDK\HTTP\Request\CreateCheckout as AfterpayCreateCheckoutRequest;
use Afterpay\SDK\HTTP\Request\CreateRefund as AfterpayCreateRefundRequest;
use Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture as AfterpayImmediatePaymentCaptureRequest;



/**
 * This sample matches the "Immediate Payment Capture" sample, except that if the payment is approved,
 * a partial refund is automatically submitted.
 */

$error = null;
$order = null;
$refund = null;

if (! empty($_POST)) {
    $createCheckoutRequest = new AfterpayCreateCheckoutRequest([
        'amount' => [ '200', 'AUD' ],
        'consumer' => [ 'email' => 'nobody@example.com' ],
        'merchant' => [
            'redirectConfirmUrl' => $_POST['redirectReturnUrl'],
            'redirectCancelUrl' => $_POST['redirectReturnUrl']
        ]
    ]);

    if ($createCheckoutRequest->send()) {
        header('Location: ' . $createCheckoutRequest->getResponse()->getParsedBody()->redirectCheckoutUrl);
    } else {
        $error = $createCheckoutRequest->getResponse()->getParsedBody();
    }
} elseif (! empty($_GET)) {
    $immediatePaymentCaptureRequest = new AfterpayImmediatePaymentCaptureRequest([
        'token' => $_GET['orderToken']
    ]);

    if ($immediatePaymentCaptureRequest->send()) {
        $order = $immediatePaymentCaptureRequest->getResponse()->getParsedBody();

        if ($immediatePaymentCaptureRequest->getResponse()->isApproved()) {
            $refundRequest = new AfterpayCreateRefundRequest([
                'amount' => [
                    'amount' => '1.50',
                    'currency' => 'AUD'
                ]
            ]);

            $refundRequest->setOrderId($order->id);

            if ($refundRequest->send()) {
                $refund = $refundRequest->getResponse()->getParsedBody();
            }
        } else {
            $error = [ 'Can\'t create a refund for a declined order.' ];
        }
    } else {
        $error = $immediatePaymentCaptureRequest->getResponse()->getParsedBody();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Refund Request Sample</title>
</head>
<body>
    <?php if ($error) : ?>
        <pre><?php print_r($error); ?></pre>
    <?php elseif ($refund) : ?>
        <h3>Refund Successfully Processed</h3>
        <ul>
            <li>Refund ID: <?php echo $refund->refundId; ?></li>
        </ul>
    <?php endif; ?>
    <form method="POST">
        <div>Return here after checkout: <input type="text" name="redirectReturnUrl" value="<?php echo 'http' . ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 's' : '') . '://' . htmlspecialchars($_SERVER['HTTP_HOST']) . (strstr($_SERVER['REQUEST_URI'], '?') ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI']); ?>"></div>
        <div><button type="submit">Proceed to Afterpay</button></div>
    </form>
</body>
</html>
