<?php

namespace Cmp\Http;

use Cmp\Http\Client\MultiClient;
use Cmp\Http\Client\ServiceClient;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Sender\GuzzleSender;
use Cmp\Http\Sender\SenderInterface;
use Cmp\Monitoring\Monitor;
use Cmp\Monitoring\NullMonitor;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;

class ClientBuilder
{
    /**
     * @var SenderInterface
     */
    private $sender;

    /**
     * @var RequestFactoryInterface
     */
    private $factory;

    /**
     * @var LoggerInterface|null
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
     * Returns a new instance to start start building the client
     * 
     * @return ClientBuilder
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Setups the sender to use to execute the http requests
     *
     * @param SenderInterface $sender
     *
     * @return $this
     */
    public function withSender(SenderInterface $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Automatically generates a sender to use guzzle as a http client behind the scenes
     * 
     * IMPORTANT: you need to install 'guzzlehttp/guzzle' to use this feature
     * 
     * @param GuzzleClientInterface|null $client
     *
     * @return $this
     */
    public function withGuzzleSender(GuzzleClientInterface $client = null)
    {
        $this->sender = $this->buildGuzzleSender($client);

        return $this;
    }

    /**
     * Setups the requests configuration
     *
     * @param array $config
     *
     * @return $this
     */
    public function withConfig(array $config)
    {
        $this->factory = new RequestFactory($config);

        return $this;
    }

    /**
     * Setups the requests factory
     *
     * @param RequestFactoryInterface $factory
     *
     * @return $this
     */
    public function withRequestFactory(RequestFactoryInterface $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Allows you load the configuration from a yaml file
     * 
     * IMPORTANT: you need to install 'symfony/yaml' to use this feature
     * 
     * @param string $file
     *
     * @return $this
     */
    public function withYamlConfig($file)
    {
        if (!class_exists(Yaml::class)) {
            throw new RuntimeException("You need to install 'symfony/yaml' to use this feature");
        }

        if (!is_readable($file)) {
            throw new RuntimeException("The configuration file is not readable");
        }

        $config = Yaml::parse(file_get_contents($file));
        if (!is_array($config)) {
            throw new RuntimeException("The configuration is not valid");
        }

        $this->withConfig($config);

        return $this;
    }

    /**
     * Sets a logger to use
     * 
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function withLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param Monitor $monitor
     * @param string  $metricName
     *
     * @return $this
     */
    public function withMonitor(Monitor $monitor, $metricName)
    {
        $this->metricName = $metricName;
        $this->monitor = $monitor;
        return $this;
    }

    /**
     * Setups a logger to output debug information to the console
     *
     * @return $this
     */
    public function withConsoleDebug()
    {
        if (!class_exists(ConsoleLogger::class)) {
            throw new RuntimeException("You need to install 'symfony/console' to use this feature");
        }

        $this->logger = new ConsoleLogger(new ConsoleOutput(), [LogLevel::DEBUG => ConsoleOutput::VERBOSITY_NORMAL]);

        return $this;
    }

    /**
     * Builds the client with the configured values
     *
     * @param string|null $service
     *
     * @return Client
     */
    public function build($service = null)
    {
        if (!$this->sender) {
            $this->sender = $this->buildGuzzleSender();
        }

        if (!$this->factory) {
            throw new RuntimeException("You need to provide a configuration or a factory for requests");
        }

        if (!$this->logger) {
            $this->logger = new NullLogger();
        }

        if (!$this->monitor || !$this->metricName) {
            $this->monitor = new NullMonitor();
            $this->metricName = 'external_requests';
        }

        return $this->buildClient($this->factory, $this->sender, $this->logger, $this->monitor, $this->metricName, $service);
    }

    /**
     * @param GuzzleClientInterface|null $client
     *
     * @return GuzzleClientSender
     */
    private function buildGuzzleSender(GuzzleClientInterface $client = null)
    {
        if (!$client) {
            if (!class_exists(GuzzleClient::class)) {
                throw new RuntimeException("You need to install 'guzzlehttp/guzzle' to use it as a sender");
            }

            $client = new GuzzleClient();
        }

        return new GuzzleSender($client);
    }

    /**
     * @param RequestFactoryInterface $factory
     * @param SenderInterface         $sender
     * @param LoggerInterface         $logger
     * @param Monitor                 $monitor
     * @param string                  $metricName
     * @param string|null             $service
     *
     * @return MultiClient|ServiceClient
     */
    private function buildClient(
        RequestFactoryInterface $factory,
        SenderInterface $sender,
        LoggerInterface $logger,
        Monitor $monitor,
        $metricName,
        $service
    ) {
        return $service !== null
            ? new ServiceClient($factory, $sender, $logger, $monitor, $metricName, $service)
            : new MultiClient($factory, $sender, $logger, $monitor, $metricName);
    }
}
