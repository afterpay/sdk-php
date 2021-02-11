<?php

/**
 * @copyright Copyright (c) 2021 Afterpay Corporate Services Pty Ltd
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

use Afterpay\SDK\HTTP\Request\DeferredPaymentCapture as AfterpayDeferredPaymentCaptureRequest;



/**
 * This sample demonstrates capturing a full or partial payment for an open auth.
 * Before you can capture a payment, you need to have created a checkout, amd for the consumer to have
 * completed the checkout screenflow and confirmed the payment schedule.
 * See HTTPRequestDeferredPaymentAuth.php for a sample that satisfies these prerequisites.
 * 
 * A typical use case for partial payment capture is where a shipment is despatched for a portion of an
 * order. For a more detailed explanation and additional use cases, see:
 *  - https://developers.afterpay.com/afterpay-online/reference#deferred-payment-flow
 */

$merchant = null;
$error = null;
$paymentEvent = null;

/**
 * Remember, if you have not configured merchant credentials in your .env.php file, you can specify them
 * manually for every request. Uncomment the following lines and replace the "MERCHANT_ID" and "SECRET_KEY"
 * placeholders with your Sandbox credentials to use this method.
 */

/*$merchant = new Afterpay\SDK\MerchantAccount();

$merchant
    ->setMerchantId('MERCHANT_ID')
    ->setSecretKey('SECRET_KEY')
;*/

if (! empty($_POST)) {
    $capturePaymentRequest = new AfterpayDeferredPaymentCaptureRequest([
        'amount' => [ $_POST['amount']['amount'], $_POST['amount']['currency'] ]
    ]);

    $capturePaymentRequest->setOrderId($_POST['orderId']);

    if (!is_null($merchant)) {
        $capturePaymentRequest
            ->setMerchantAccount($merchant)
        ;
    }

    if ($capturePaymentRequest->send()) {
        $paymentEvent = $capturePaymentRequest->getResponse()->getPaymentEvent();
    } else {
        $error = $capturePaymentRequest->getResponse()->getParsedBody();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Deferred Payment Capture Request Sample</title>
</head>
<body>
    <?php if ($error) : ?>
        <h3>Error</h3>
        <pre><?php print_r($error); ?></pre>
        <p><a href="HTTPRequestDeferredPaymentAuth.php">Try a new auth</a></p>
    <?php elseif ($paymentEvent) : ?>
        <h3>Payment Capture Successful</h3>
        <ul>
            <li>ID: <?php echo $paymentEvent->getId(); ?></li>
            <li>Timestamp: <?php echo $paymentEvent->getCreated(); ?></li>
        </ul>
        <p><a href="HTTPRequestDeferredPaymentAuth.php">Start again</a></p>
    <?php endif; ?>
    <h3>Deferred Payment Capture</h3>
    <form method="POST">
        <div>Order ID: <input type="text" name="orderId" value="<?php echo $_GET['orderId'] ?>"></div>
        <div>Amount: <input type="text" name="amount[amount]" value="200.00"></div>
        <div>Currency: <input type="text" name="amount[currency]" value="AUD"></div>
        <div><button type="submit">Submit</button></div>
    </form>
</body>
</html>
