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

    /**
     * @return string
     */
    public function toString()
    {
        return "{$this->getAmount()} {$this->getCurrency()}";
    }

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
