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

use Afterpay\SDK\HTTP\Request\CreateCheckout as AfterpayCreateCheckoutRequest;
use Afterpay\SDK\HTTP\Request\DeferredPaymentAuth as AfterpayDeferredPaymentAuthRequest;



/**
 * This sample builds an HTML form to simulate the checkout page of an e-commerce platform.
 * A click on the "Proceed to Afterpay" button posts to the current page, which calls the "Create Checkout" API
 * (satisfying only the bare minimum technical requirements) and then (in the event of success) redirects to
 * the Afterpay Checkout URL. If you navigate through the consumer payment flow and click "confirm" to commit
 * to the payment schedule, you should return to this page with new query parameters appended to the URL.
 * The "orderToken" parameter will then be used to submit an "Deferred Payment Auth" Request. Some of the
 * important components of the Response will then be rendered above the HTML form.
 */

$merchant = null;
$error = null;
$order = null;

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
    $createCheckoutRequest = new AfterpayCreateCheckoutRequest([
        'amount' => [ '200', 'AUD' ],
        'consumer' => [ 'email' => 'nobody@example.com' ],
        'merchant' => [
            'redirectConfirmUrl' => $_POST['redirectReturnUrl'],
            'redirectCancelUrl' => $_POST['redirectReturnUrl']
        ]
    ]);

    if (!is_null($merchant)) {
        $createCheckoutRequest
            ->setMerchantAccount($merchant)
        ;
    }

    if ($createCheckoutRequest->send()) {
        header('Location: ' . $createCheckoutRequest->getResponse()->getParsedBody()->redirectCheckoutUrl);
    } else {
        $error = $createCheckoutRequest->getResponse()->getParsedBody();
    }
} elseif (! empty($_GET)) {
    $deferredPaymentAuthRequest = new AfterpayDeferredPaymentAuthRequest([
        'token' => $_GET['orderToken']
    ]);

    if (!is_null($merchant)) {
        $deferredPaymentAuthRequest
            ->setMerchantAccount($merchant)
        ;
    }

    if ($deferredPaymentAuthRequest->send()) {
        $order = $deferredPaymentAuthRequest->getResponse()->getParsedBody();
    } else {
        $error = $deferredPaymentAuthRequest->getResponse()->getParsedBody();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Deferred Payment Auth Request Sample</title>
</head>
<body>
    <?php if ($error) : ?>
        <h3>Error</h3>
        <pre><?php print_r($error); ?></pre>
        <p><a href="HTTPRequestDeferredPaymentAuth.php">Try again</a></p>
    <?php elseif ($order) : ?>
        <h3>Order Record Created</h3>
        <ul>
            <li>ID: <?php echo $order->id; ?></li>
            <li>Status: <?php echo $order->status; ?></li>
            <li>Is Approved? <?php echo $deferredPaymentAuthRequest->getResponse()->isApproved() ? 'YES - Proceed to thank you page.' : 'NO - Return to checkout with payment declined error.'; ?></li>
        </ul>
        <?php if ($deferredPaymentAuthRequest->getResponse()->isApproved()) : ?>
            <p><a href="HTTPRequestDeferredPaymentCapture.php?orderId=<?php echo $order->id; ?>">Capture Payment for this order</a></p>
        <?php else : ?>
            <p><a href="HTTPRequestDeferredPaymentAuth.php">Start again</a></p>
        <?php endif; ?>
    <?php else : ?>
        <h3>Deferred Payment Auth</h3>
        <form method="POST">
            <div>Return here after checkout: <input type="text" name="redirectReturnUrl" value="<?php echo 'http' . ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 's' : '') . '://' . htmlspecialchars($_SERVER['HTTP_HOST']) . (strstr($_SERVER['REQUEST_URI'], '?') ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI']); ?>"></div>
            <div><button type="submit">Proceed to Afterpay</button></div>
        </form>
    <?php endif; ?>
</body>
</html>
