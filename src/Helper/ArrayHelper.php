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

namespace Afterpay\SDK\Helper;

final class ArrayHelper
{
    /**
     * Get the value of an array key if the key exists.
     * Avoids generating an "Undefined index" notice or "Undefined array key"
     * warning if the key doesn't exist.
     *
     * See https://3v4l.org/RHZpn
     */
    public static function maybeGet($key, $array)
    {
        return array_key_exists($key, $array) ? $array[ $key ] : null;
    }
}
