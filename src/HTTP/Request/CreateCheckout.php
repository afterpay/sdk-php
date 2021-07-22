<?php

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\HTTP\Request;
use Afterpay\SDK\Model\Consumer;
use Afterpay\SDK\Model\Contact;
use Afterpay\SDK\Model\Discount;
use Afterpay\SDK\Model\Item;
use Afterpay\SDK\Model\Merchant;
use Afterpay\SDK\Model\Money;
use Afterpay\SDK\Model\ShippingCourier;

class CreateCheckout extends Request
{
    /**
     * @var array $data
     */
    protected $data = [
        'totalAmount' => [
            'type' => Money::class,
            'required' => false
        ],
        'amount' => [
            'type' => Money::class,
            'required' => false
        ],
        'consumer' => [
            'type' => Consumer::class,
            'required' => false
        ],
        'billing' => [
            'type' => Contact::class
        ],
        'shipping' => [
            'type' => Contact::class
        ],
        'courier' => [
            'type' => ShippingCourier::class
        ],
        'description' => [
            'type' => 'string',
            'length' => 256
        ],
        'mode' => [
            'type' => 'string',
            'length' => 256
        ],
        'items' => [
            'type' => Item::class . '[]'
        ],
        'discounts' => [
            'type' => Discount::class . '[]'
        ],
        'merchant' => [
            'type' => Merchant::class,
            'required' => true
        ],
        'merchantReference' => [
            'type' => 'string',
            'length' => 128
        ],
        'taxAmount' => [
            'type' => Money::class
        ],
        'shippingAmount' => [
            'type' => Money::class
        ],
        'purchaseCountry' => [
            'type' => 'string',
            'length' => 2
        ]
    ];

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setUri('/v1/orders')
            ->setHttpMethod('POST')
            ->configureBasicAuth()
        ;
    }

    /**
     * Populates the purchaseCountry field based on the merchantAccount countryCode
     */
    protected function beforeSend()
    {
        if (is_null($this->getPurchaseCountry())) {
            $this->setPurchaseCountry($this->getMerchantAccount()->getCountryCode());
        }
    }
}
