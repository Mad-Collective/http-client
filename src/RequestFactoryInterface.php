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
    public function create($serviceKey, $requestKey, array $parameters = []);

    /**
     * Builds a request based on the service and request name, using provided $jsonSerializable as request body
     *
     * @param string            $serviceKey
     * @param string            $requestKey
     * @param \JsonSerializable $jsonSerializableBody
     * @return mixed
     */
    public function createFromJson($serviceKey, $requestKey, \JsonSerializable $jsonSerializableBody);
}