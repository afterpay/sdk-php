<?php

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\Exception\InvalidArgumentException;
use Afterpay\SDK\Exception\PrerequisiteNotMetException;
use Afterpay\SDK\HTTP\Request;

class GetCheckout extends Request
{
    /**
     * @var string $checkoutToken
     *
     * @todo Make a flexible array for all path params similar to body data.
     */
    protected $checkoutToken;

    /**
     * @throws \Afterpay\SDK\Exception\PrerequisiteNotMetException
     */
    protected function beforeSend()
    {
        if (is_null($this->checkoutToken)) {
            throw new PrerequisiteNotMetException('Cannot send a GetCheckout Request without a checkout token (must call GetCheckout::setCheckoutToken before GetCheckout::send)');
        }
    }

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->configureBasicAuth()
        ;
    }

    /**
     * @param string $checkoutToken
     * @return \Afterpay\SDK\HTTP\Request\GetCheckout
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setCheckoutToken($checkoutToken)
    {
        if (! is_string($checkoutToken)) {
            throw new InvalidArgumentException('Expected string for checkoutToken; ' . gettype($checkoutToken) . ' given');
        } elseif (strlen($checkoutToken) < 1) {
            throw new InvalidArgumentException('Expected non-empty string for checkoutToken; empty string given');
        } elseif (! preg_match('/^[a-z0-9-_.~]+$/i', $checkoutToken)) {
            throw new InvalidArgumentException("Expected well-formed URI component for checkoutToken; '{$checkoutToken}' given");
        }

        $this->checkoutToken = $checkoutToken;

        $this->setUri("/v2/checkouts/{$this->checkoutToken}");

        return $this;
    }
}
