<?php

namespace Cmp\Http\Client;

use Cmp\Http\Client\Traits\ClientTrait;
use Cmp\Http\Client\Traits\RequestBuilderTrait;
use Cmp\Http\Client\Traits\ServiceClientTrait;
use Cmp\Http\Message\Request;
use Cmp\Http\RequestFactoryInterface;
use Cmp\Http\Sender\SenderInterface;
use Psr\Log\LoggerInterface;

/**
 * The service client is only able to access a single service from the configuration
 */
class ServiceClient extends AbstractClient implements ServiceClientInterface
{
    use ClientTrait, 
        ServiceClientTrait, 
        RequestBuilderTrait;

    /**
     * @var string
     */
    private $service;

    /**
     * ServiceClient constructor.
     *
     * @param RequestFactoryInterface $factory
     * @param SenderInterface         $sender
     * @param LoggerInterface         $logger
     * @param string                  $service
     */
    public function __construct(RequestFactoryInterface $factory, SenderInterface $sender, LoggerInterface $logger, $service)
    {
        parent::__construct($factory, $sender, $logger);
        $this->service = $service;
    }

    /**
     * @param string $request
     * @param array  $parameters
     *
     * @return Request
     */
    public function request($request, array $parameters = [])
    {
        return $this->createRequest($this->service, $request, $parameters);
    }
}
