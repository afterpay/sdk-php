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

use Afterpay\SDK\Helper\StringHelper as AfterpayStringHelper;
use Afterpay\SDK\Model\Payment as AfterpayPayment;
use Afterpay\SDK\HTTP\Request\DeferredPaymentVoid as AfterpayDeferredPaymentVoidRequest;



/**
 * This sample demonstrates voiding the open-to-capture remainder of an open auth.
 *
 * For example use cases of the Void endpoint, see:
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
    $voidPaymentRequest = new AfterpayDeferredPaymentVoidRequest();

    if (! empty($_POST[ 'requestId' ])) {
        $voidPaymentRequest->setRequestId($_POST[ 'requestId' ]);
    }
    if (! empty($_POST[ 'amount' ][ 'amount' ]) || ! empty($_POST[ 'amount' ][ 'currency' ])) {
        $voidPaymentRequest->setAmount($_POST[ 'amount' ][ 'amount' ], $_POST[ 'amount' ][ 'currency' ]);
    }

    $voidPaymentRequest->setOrderId($_POST[ 'orderId' ]);

    if (!is_null($merchant)) {
        $voidPaymentRequest
            ->setMerchantAccount($merchant)
        ;
    }

    if ($voidPaymentRequest->send()) {
        $order = new AfterpayPayment($voidPaymentRequest->getResponse()->getParsedBody());
        $refund = $voidPaymentRequest->getResponse()->getRefund();
        $paymentEvent = $voidPaymentRequest->getResponse()->getPaymentEvent();
    } else {
        $error = $voidPaymentRequest->getResponse()->getParsedBody();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Deferred Payment Void Request Sample</title>
</head>
<body>
    <?php if ($error) : ?>
        <h3>Error</h3>
        <pre><?php print_r($error); ?></pre>
        <p><a href="HTTPRequestDeferredPaymentAuth.php">Try a new auth</a></p>
    <?php elseif ($paymentEvent) : ?>
        <h3>Payment Void Successful</h3>
        <ul>
            <li>Void Event ID: <?php echo $paymentEvent->getId(); ?></li>
            <li>Refund ID: <?php echo $refund->getRefundId(); ?></li>
            <li>Timestamp: <?php echo $paymentEvent->getCreated(); ?></li>
            <li>Open to Capture: <?php echo $order->getOpenToCaptureAmount()->toString(); ?></li>
            <li>Auth Expiry: <?php echo $order->getEvents()[0]->getExpires(); ?></li>
        </ul>
        <p><a href="HTTPRequestDeferredPaymentCapture.php?orderId=<?php echo urlencode($_GET['orderId']) ?>">Capture Payment for this order</a></p>
        <p><a href="HTTPRequestDeferredPaymentAuth.php">Start again</a></p>
    <?php endif; ?>
    <h3>Deferred Payment Void</h3>
    <form method="POST">
        <p>Path params:</p>
        <div>Order ID: <input type="text" name="orderId" value="<?php echo urlencode($_GET['orderId']) ?>"></div>
        <p>Body params:</p>
        <p><em>Note: Clear the Amount and Currency fields to void the total "Open to Capture" remainder for the order.</em></p>
        <div>Request ID: <input type="text" name="requestId" value="<?php echo AfterpayStringHelper::generateUuid(); ?>"></div>
        <div>Amount: <input type="text" name="amount[amount]" value="200.00"></div>
        <div>Currency: <input type="text" name="amount[currency]" value="AUD"></div>
        <div><button type="submit">Submit</button></div>
    </form>
</body>
</html>
