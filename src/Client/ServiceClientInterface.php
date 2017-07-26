<?php

namespace Cmp\Http\Client;


use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;
use Psr\Http\Message\StreamInterface;

/**
 * A service client can create only requests from a single service
 */
interface ServiceClientInterface extends ClientInterface
{
    /**
     * Creates a request from the configuration and immediately executes it 
     *
     * @param string $request
     * @param array  $parameters
     *
     * @return Response
     */
    public function execute($request, array $parameters = []);

    /**
     * Creates a request from the configuration, using json serializable input at body and immediately executes it
     *
     * @param string           $request
     * @param \JsonSerializable $jsonSerializable
     *
     * @return Response
     */
    public function executeFromJson($request, \JsonSerializable $jsonSerializable);

    /**
     * Creates a request from the configuration and immediately executes it returning only the body
     *
     * @param string $request
     * @param array  $parameters
     *
     * @return StreamInterface
     */
    public function body($request, array $parameters = []);

    /**
     * Creates a request from the configuration and immediately executes it returning only the body parsed as json
     *
     * @param string $request
     * @param array  $parameters
     *
     * @return mixed
     */
    public function json($request, array $parameters = []);

    /**
     * Creates a request from the configuration and immediately executes it returning only the body parsed as json
     *
     * @param string $request
     * @param array  $parameters
     *
     * @return mixed
     */
    public function jsonAsArray($request, array $parameters = []);

    /**
     * Creates a request based on the configuration
     *
     * @param string $request     The name of the request
     * @param array  $parameters  The parameters to substitute for the placeholders
     *
     * @return Request
     */
    public function request($request, array $parameters = []);
}
