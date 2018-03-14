<?php

namespace Cmp\Http\Client;

use Cmp\Http\Exception\RequestExecutionException;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;

/**
 * A client that can send requests
 */
interface ClientInterface
{
    /**
     * Executes a request returning back the response
     *
     * @param Request $request
     *
     * @return Response
     * 
     * @throws RequestExecutionException 
     * @throws RuntimeException
     */
    public function send(Request $request);
}
