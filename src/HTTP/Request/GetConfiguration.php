<?php

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\HTTP\Request;

class GetConfiguration extends Request
{
    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->setUri('/v2/configuration')
            ->configureBasicAuth()
        ;
    }
}
