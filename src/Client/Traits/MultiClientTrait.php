<?php

namespace Cmp\Http\Client\Traits;

use Cmp\Http\Exception\RequestExecutionException;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;

/**
 * Use this trait in your custom client to have a default implementation for the shortcuts
 */
trait MultiClientTrait
{
    /**
     * Creates a request based on the configuration
     *
     * @param string $service     The name of the service
     * @param string $request     The name of the request
     * @param array  $parameters  The parameters to substitute for the placeholders
     *
     * @return Request
     */
    abstract public function request($service, $request, array $parameters = []);

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
    public function execute($service, $request, array $parameters = [])
    {
        return $this->send($this->request($service, $request, $parameters));
    }

    /**
     * {@inheritdoc}
     */
    public function body($service, $request, array $parameters = [])
    {
        return $this->execute($service, $request, $parameters)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function json($service, $request, array $parameters = [])
    {
        return $this->execute($service, $request, $parameters)->json();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonAsArray($service, $request, array $parameters = [])
    {
        return $this->execute($service, $request, $parameters)->jsonAsArray();
    }
}
