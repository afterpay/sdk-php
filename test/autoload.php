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

spl_autoload_register(function ($class) {
    if (preg_match('/^Afterpay\\\SDK\\\Test/', $class)) {
        $file = realpath(dirname(dirname(__FILE__)) . str_replace([ 'Afterpay\\SDK\\Test', '\\' ], [ '/test', '/' ], $class) . '.php');
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (preg_match('/^Afterpay\\\SDK/', $class)) {
        $file = realpath(dirname(dirname(__FILE__)) . str_replace([ 'Afterpay\\SDK', '\\' ], [ '/src', '/' ], $class) . '.php');
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
