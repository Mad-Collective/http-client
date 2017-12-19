<?php

namespace Cmp\Http\Provider;

use Cmp\Http\ClientBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HttpClientServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    private $builderAlias;

    /**
     * @var string
     */
    private $clientAlias;

    /**
     * HttpClientServiceProvider constructor.
     *
     * @param string $builderAlias
     * @param string $clientAlias
     */
    public function __construct($builderAlias = null, $clientAlias = null)
    {
        $this->builderAlias = $builderAlias;
        $this->clientAlias  = $clientAlias;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple = $this->setupDefaultOptions($pimple);
        $pimple = $this->registerAlias($pimple);

        $pimple['http_client.builder'] = function() use ($pimple) {
            return $this->getBuilder($pimple);
        };

        $pimple['http_client.client'] = function() use ($pimple) {
            return $pimple['http_client.builder']->build();
        };
    }

    /**
     * Builds the container
     * 
     * @param Container $pimple
     *
     * @return Client
     */
    private function getBuilder(Container $pimple)
    {
        $builder = ClientBuilder::create();

        $this->setFactory($builder, $pimple);
        $this->setSender($builder, $pimple);
        $this->setLogger($builder, $pimple);
        $this->setMonitor($builder, $pimple);

        return $builder;
    }

    /**
     * @param ClientBuilder $builder
     * @param Container     $pimple
     */
    private function setFactory(ClientBuilder $builder, Container $pimple)
    {
        if ($pimple['http_client.factory']) {
            $builder->withRequestFactory($pimple['http_client.factory']);
        } elseif ($pimple['http_client.yaml']) {
            $builder->withYamlConfig($pimple['http_client.yaml']);
        } else {
            $builder->withConfig($pimple['http_client.config']);
        }
    }

    /**
     * @param ClientBuilder $builder
     * @param Container     $pimple
     */
    private function setSender(ClientBuilder $builder, Container $pimple)
    {
        if ($pimple['http_client.sender']) {
            $builder->withSender($pimple['http_client.sender']);
        } elseif ($pimple['http_client.guzzle']) {
            $builder->withGuzzleSender($pimple['http_client.guzzle']);
        }
    }

    /**
     * @param ClientBuilder $builder
     * @param Container     $pimple
     */
    private function setLogger(ClientBuilder $builder, Container $pimple)
    {
        if ($pimple['http_client.logger']) {
            $builder->withLogger($pimple['http_client.logger']);
        } elseif ($pimple['http_client.debug']) {
            $builder->withConsoleDebug();
        }
    }

    private function setMonitor(ClientBuilder $builder, Container $pimple)
    {
        if ($pimple['http_client.metric_name'] && $pimple['http_client.monitor']) {
            $builder->withMonitor($pimple['http_client.monitor'], $pimple['http_client.metric_name']);
        } elseif ($pimple['http_client.debug']) {
            $builder->withConsoleDebug();
        }
    }

    /**
     * @param Container $pimple
     *
     * @return Container
     */
    private function setupDefaultOptions(Container $pimple)
    {
        // Options
        $pimple['http_client.config']  = [];
        $pimple['http_client.yaml']    = null;
        $pimple['http_client.logger']  = null;
        $pimple['http_client.sender']  = null;
        $pimple['http_client.guzzle']  = null;
        $pimple['http_client.factory'] = null;
        $pimple['http_client.debug']   = null;
        $pimple['http_client.metric_name'] = null;
        $pimple['http_client.monitor']   = null;

        return $pimple;
    }

    /**
     * @param Container $pimple
     *
     * @return Container
     */
    private function registerAlias(Container $pimple)
    {
        if ($this->builderAlias) {
            $pimple[$this->builderAlias] = function () use ($pimple) {
                return $pimple['http_client.builder'];
            };
        }

        if ($this->clientAlias) {
            $pimple[$this->clientAlias] = function () use ($pimple) {
                return $pimple['http_client.client'];
            };
        }

        return $pimple;
    }
}
