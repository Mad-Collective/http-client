<?php

namespace Cmp\Http\Client;

use Cmp\Http\Client\Traits\ClientTrait;
use Cmp\Http\Client\Traits\MultiClientTrait;
use Cmp\Http\Client\Traits\RequestBuilderTrait;
use Cmp\Http\Message\Request;

class MultiClient extends AbstractClient implements MultiClientInterface
{
    use ClientTrait,
        MultiClientTrait,
        RequestBuilderTrait;

    /**
     * @param string $service
     * @param string $request
     * @param array  $parameters
     *
     * @return Request
     */
    public function request($service, $request, array $parameters = [])
    {
        return $this->createRequest($service, $request, $parameters);
    }
}
