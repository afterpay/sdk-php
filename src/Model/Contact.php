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

final class Contact extends Model
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
        'line1' => [
            'type' => 'string',
            'length' => 128,
            'required' => true
        ],
        'line2' => [
            'type' => 'string',
            'length' => 128
        ],
        'area1' => [
            'type' => 'string',
            'length' => 128
        ],
        'area2' => [
            'type' => 'string',
            'length' => 128
        ],
        'region' => [
            'type' => 'string',
            'length' => 128
        ],
        'postcode' => [
            'type' => 'string',
            'length' => 128,
            'required' => true
        ],
        'countryCode' => [
            'type' => 'string',
            'length' => 2,
            'required' => true
        ],
        'phoneNumber' => [
            'type' => 'string',
            'length' => 32
        ]
    ];

    /*public function __construct( ... $args )
    {
        parent::__construct( ... $args );
    }*/
}
