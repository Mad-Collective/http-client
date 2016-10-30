<?php

namespace spec\Cmp\Http\Provider;

use Cmp\Http\Client\MultiClient;
use Cmp\Http\Client\ServiceClient;
use Cmp\Http\ClientBuilder;
use Cmp\Http\Provider\HttpClientServiceProvider;
use Cmp\Http\RequestFactoryInterface;
use Cmp\Http\Sender\SenderInterface;
use GuzzleHttp\Client;
use PhpSpec\ObjectBehavior;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * @mixin HttpClientServiceProvider
 */
class HttpClientServiceProviderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('builderAlias', 'clientAlias');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HttpClientServiceProvider::class);
        $this->shouldHaveType(ServiceProviderInterface::class);
    }

    function it_can_register_a_default_builder()
    {
        $pimple = new Container();
        $pimple->register($this->getWrappedObject(), [
            'http_client.config' => ['foo_service' => []]
        ]);

        Assert::isInstanceOf($pimple['builderAlias'], ClientBuilder::class);
        Assert::eq($pimple['http_client.builder'], $pimple['builderAlias']);

        Assert::isInstanceOf($pimple['clientAlias'], MultiClient::class);
        Assert::eq($pimple['http_client.client'], $pimple['clientAlias']);

        Assert::isInstanceOf($pimple['builderAlias']->build('foo_service'), ServiceClient::class);
    }

    function it_can_register_a_builder_with_a_custom_factory(RequestFactoryInterface $factory)
    {
        $pimple = new Container();
        $pimple->register($this->getWrappedObject(), ['http_client.factory' => $factory->getWrappedObject()]);

        Assert::isInstanceOf($pimple['clientAlias'], MultiClient::class);
    }

    function it_can_register_a_builder_with_yaml_file()
    {
        $yaml = tempnam(sys_get_temp_dir(), 'temp_');
        file_put_contents($yaml, "foo: bar");

        $pimple = new Container();
        $pimple->register($this->getWrappedObject(), ['http_client.yaml' => $yaml]);

        Assert::isInstanceOf($pimple['clientAlias'], MultiClient::class);
    }

    function it_can_register_a_builder_with_custom_sender(SenderInterface $sender)
    {
        $pimple = new Container();
        $pimple->register($this->getWrappedObject(), ['http_client.sender' => $sender->getWrappedObject()]);

        Assert::isInstanceOf($pimple['clientAlias'], MultiClient::class);
    }

    function it_can_register_a_builder_with_custom_guzzle(Client $guzzle)
    {
        $pimple = new Container();
        $pimple->register($this->getWrappedObject(), ['http_client.guzzle' => $guzzle->getWrappedObject()]);

        Assert::isInstanceOf($pimple['clientAlias'], MultiClient::class);
    }

    function it_can_register_a_builder_with_custom_logger(LoggerInterface $logger)
    {
        $pimple = new Container();
        $pimple->register($this->getWrappedObject(), ['http_client.logger' => $logger->getWrappedObject()]);

        Assert::isInstanceOf($pimple['clientAlias'], MultiClient::class);
    }

    function it_can_register_a_builder_with_the_console_debug()
    {
        $pimple = new Container();
        $pimple->register($this->getWrappedObject(), ['http_client.debug' => true]);

        Assert::isInstanceOf($pimple['clientAlias'], MultiClient::class);
    }
}
