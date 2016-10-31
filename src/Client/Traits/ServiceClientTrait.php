<?php

namespace Cmp\Http\Client\Traits;

use Cmp\Http\Exception\RequestExecutionException;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Request;

/**
 * Use this trait in your custom client to have a default implementation for the shortcuts
 */
trait ServiceClientTrait
{
    /**
     * Creates a request based on the configuration
     *
     * @param string $request     The name of the request
     * @param array  $parameters  The parameters to substitute for the placeholders
     *
     * @return Request
     */
    abstract public function request($request, array $parameters = []);

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
    abstract public function send(Request $request);

    /**
     * {@inheritdoc}
     */
    public function execute($request, array $parameters = [])
    {
        return $this->send($this->request($request, $parameters));
    }

    /**
     * {@inheritdoc}
     */
    public function body($request, array $parameters = [])
    {
        return $this->execute($request, $parameters)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function json($request, array $parameters = [])
    {
        return $this->execute($request, $parameters)->json();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonAsArray($request, array $parameters = [])
    {
        return $this->execute($request, $parameters)->jsonAsArray();
    }
}
