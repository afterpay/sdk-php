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
use Afterpay\SDK\Model\Money;

final class Item extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'name' => [
            'type' => 'string',
            'length' => 255,
            'required' => true
        ],
        'sku' => [
            'type' => 'string',
            'length' => 128
        ],
        'quantity' => [
            'type' => 'integer',
            'min' => self::SIGNED_32BIT_INT_MIN,
            'max' => self::SIGNED_32BIT_INT_MAX
        ],
        'pageUrl' => [
            'type' => 'string',
            'length' => 2048
        ],
        'imageUrl' => [
            'type' => 'string',
            'length' => 2048
        ],
        'price' => [
            'type' => Money::class,
            'required' => true
        ],
        'categories' => [
            'type' => 'array'
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
