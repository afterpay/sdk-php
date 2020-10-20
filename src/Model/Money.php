<?php

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;

final class Money extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'amount' => [
            'type' => 'string',
            'default' => '0.00',
            'required' => true
        ],
        'currency' => [
            'type' => 'string',
            'length' => 3,
            'required' => true
        ]
    ];

    protected function filterBeforeSetAmount(...$args)
    {
        if (Model::getAutomaticFormattingEnabled()) {
            if (count($args) == 1) {
                $amount = & $args[ 0 ];
                $amount_type = gettype($amount);
                $matches = [];

                if (in_array($amount_type, [ 'integer', 'double' ])) {
                    $amount = number_format($amount, 2, '.', '');
                } elseif ($amount_type == 'string' && preg_match('/\d/', $amount)) {
                    $str = preg_replace('/[^0-9.]+/', '', $amount);
                    $arr = explode('.', $str);
                    if (count($arr) > 1) {
                        $str = array_shift($arr) . '.' . array_shift($arr);
                        if (count($arr) > 0) {
                            $str .= implode('', $arr);
                        }
                    } else {
                        $str = $arr[ 0 ];
                    }
                    $num = (float) $str;
                    $amount = number_format($num, 2, '.', '');
                } elseif (empty($amount)) {
                    $amount = number_format(0, 2, '.', '');
                }
            }
        }

        return $args;
    }

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
