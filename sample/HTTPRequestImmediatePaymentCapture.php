<?php

$composer_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    require_once __DIR__ . '/../test/autoload.php';
}

use Afterpay\SDK\HTTP\Request\CreateCheckout as AfterpayCreateCheckoutRequest;
use Afterpay\SDK\HTTP\Request\ImmediatePaymentCapture as AfterpayImmediatePaymentCaptureRequest;



/**
 * This sample builds an HTML form to simulate the checkout page of an e-commerce platform.
 * A click on the "Proceed to Afterpay" button posts to the current page, which calls the "Create Checkout" API
 * (satisfying only the bare minimum technical requirements) and then (in the event of success) redirects to
 * the Afterpay Checkout URL. If you navigate through the consumer payment flow and click "confirm" to commit
 * to the payment schedule, you should return to this page with new query parameters appended to the URL.
 * The "orderToken" parameter will then be used to submit an "Immediate Payment Capture" Request. Some of the
 * important components of the Response will then be rendered above the HTML form.
 */

$error = null;
$order = null;

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
    } else {
        $error = $immediatePaymentCaptureRequest->getResponse()->getParsedBody();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Immediate Payment Capture Request Sample</title>
</head>
<body>
    <?php if ($error) : ?>
        <pre><?php print_r($error); ?></pre>
    <?php elseif ($order) : ?>
        <h3>Order Record Created</h3>
        <ul>
            <li>ID: <?php echo $order->id; ?></li>
            <li>Status: <?php echo $order->status; ?></li>
            <li>Is Approved? <?php echo $immediatePaymentCaptureRequest->getResponse()->isApproved() ? 'YES - Proceed to thank you page.' : 'NO - Return to checkout with payment declined error.'; ?></li>
        </ul>
        <p><em>Note: Payment is only captured if the status of the order record is approved. You can check this with <code>Afterpay\SDK\HTTP\Response\ImmediatePaymentCapture::isApproved</code>.</em></p>
    <?php endif; ?>
    <form method="POST">
        <div>Return here after checkout: <input type="text" name="redirectReturnUrl" value="<?php echo 'http' . ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 's' : '') . '://' . htmlspecialchars($_SERVER['HTTP_HOST']) . (strstr($_SERVER['REQUEST_URI'], '?') ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI']); ?>"></div>
        <div><button type="submit">Proceed to Afterpay</button></div>
    </form>
</body>
</html>
