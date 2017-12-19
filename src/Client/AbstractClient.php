<?php

namespace Cmp\Http\Client;

use Cmp\Http\RequestFactoryInterface;
use Cmp\Http\Sender\SenderInterface;
use Cmp\Http\Integration\Monitor;
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
     * @var Monitor
     */
    private $monitor;

    /**
     * @var string
     */
    private $metricName;

    /**
     * AbstractClient constructor.
     *
     * @param RequestFactoryInterface $factory
     * @param SenderInterface         $sender
     * @param LoggerInterface         $logger
     * @param Monitor                 $monitor
     * @param string                  $metricName
     */
    public function __construct(RequestFactoryInterface $factory, SenderInterface $sender, LoggerInterface $logger, Monitor $monitor, $metricName)
    {
        $this->factory    = $factory;
        $this->sender     = $sender;
        $this->logger     = $logger;
        $this->monitor    = $monitor;
        $this->metricName = $metricName;
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

    /**
     * @return Monitor
     */
    protected function monitor()
    {
        return $this->monitor;
    }

    /**
     * @return string
     */
    protected function getMetricName()
    {
        return $this->metricName;
    }
}
