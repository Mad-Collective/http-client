<?php

namespace Cmp\Http;

use Cmp\Http\Message\Request;

interface RequestFactoryInterface
{
    /**
     * Builds a request based on the service and request name, with the given parameters
     * 
     * @param string $serviceKey
     * @param string $requestKey
     * @param array  $parameters
     *
     * @return Request
     */
    public function create($serviceKey, $requestKey, array $parameters);
}
