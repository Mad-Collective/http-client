<?php

namespace Cmp\Http\Client;

use Cmp\Http\RequestFactoryInterface;
use Cmp\Http\Sender\SenderInterface;
use Psr\Log\LoggerInterface;

/**
 * The abstract client provide access to the basic dependencies of the clients
 */
abstract class AbstractClient
{
    /**
     * @var RequestFactoryInterface
     */
    private $factory;

    /**
     * @var SenderInterface
     */
    private $sender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RequestBuilder constructor.
     *
     * @param RequestFactoryInterface $factory
     * @param SenderInterface         $sender
     * @param LoggerInterface         $logger
     */
    public function __construct(RequestFactoryInterface $factory, SenderInterface $sender, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->sender  = $sender;
        $this->logger  = $logger;
    }

    /**
     * @return RequestFactoryInterface
     */
    protected function factory()
    {
        return $this->factory;
    }

    /**
     * @return SenderInterface
     */
    protected function sender()
    {
        return $this->sender;
    }

    /**
     * @return LoggerInterface
     */
    protected function logger()
    {
        return $this->logger;
    }
}
