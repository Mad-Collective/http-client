<?php

namespace Cmp\Http\Client;

use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;
use Psr\Http\Message\StreamInterface;

/**
 * A multi-client can create and send requests from any service defined in the configuration.
 */
interface MultiClientInterface extends ClientInterface
{
    /**
     * Creates a request from the configuration and immediately executes it 
     *
     * @param string $service
     * @param string $request
     * @param array  $parameters
     *
     * @return Response
     */
    public function execute($service, $request, array $parameters = []);

    /**
     * Creates a request from the configuration and immediately executes it returning only the body
     *
     * @param string $service
     * @param string $request
     * @param array  $parameters
     *
     * @return StreamInterface
     */
    public function body($service, $request, array $parameters = []);

    /**
     * Creates a request from the configuration and immediately executes it returning only the body parsed as json
     *
     * @param string $service
     * @param string $request
     * @param array  $parameters
     *
     * @return mixed
     */
    public function json($service, $request, array $parameters = []);

    /**
     * Creates a request from the configuration and immediately executes it returning only the body parsed as json
     *
     * @param string $service
     * @param string $request
     * @param array  $parameters
     *
     * @return mixed
     */
    public function jsonAsArray($service, $request, array $parameters = []);

    /**
     * Creates a request based on the configuration
     *
     * @param string $service     The name of the service
     * @param string $request     The name of the request
     * @param array  $parameters  The parameters to substitute for the placeholders
     *
     * @return Request
     */
    public function request($service, $request, array $parameters = []);
}
