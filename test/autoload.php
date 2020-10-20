<?php

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
