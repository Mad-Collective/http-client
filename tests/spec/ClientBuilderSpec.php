<?php

namespace spec\Cmp\Http;

use Cmp\Http\Client\MultiClient;
use Cmp\Http\Client\ServiceClient;
use Cmp\Http\ClientBuilder;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\RequestFactoryInterface;
use Cmp\Http\Sender\SenderInterface;
use GuzzleHttp\Client;
use phpmock\Mock;
use phpmock\MockBuilder;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Yaml\Yaml;

/**
 * @mixin \Cmp\Http\ClientBuilder
 */
class ClientBuilderSpec extends ObjectBehavior
{
    function let()
    {
        Mock::disableAll();
    }

    function it_fails_to_build_the_config_from_yaml_if_symfony_yaml_is_not_installed()
    {
        $exception = new RuntimeException("You need to install 'symfony/yaml' to use this feature");

        (new MockBuilder())->setNamespace('\Cmp\Http')
            ->setName('class_exists')
            ->setFunction(function($class) {
                if ($class == Yaml::class) {
                    return false;
                }
            })->build()->enable();

        $this->shouldThrow($exception)->duringWithYamlConfig('foo');
    }

    function it_fails_to_build_the_config_from_yaml_if_the_file_is_not_readable()
    {
        $exception = new RuntimeException("The configuration file is not readable");

        (new MockBuilder())->setNamespace('\Cmp\Http')
            ->setName('is_readable')
            ->setFunction(function($class) {
                if ($class == ConsoleLogger::class) {
                    return false;
                }
            })->build()->enable();

        $this->shouldThrow($exception)->duringWithYamlConfig('foo');
    }

    function it_fails_to_build_the_config_if_the_yaml_does_not_have_a_valid_array()
    {
        $exception = new RuntimeException("The configuration is not valid");

        $temp = tempnam(sys_get_temp_dir(), 'temp_');
        file_put_contents($temp, "");

        $this->shouldThrow($exception)->duringWithYamlConfig($temp);
    }

    function it_fails_to_build_the_default_client_if_guzzle_is_not_installed()
    {
        $exception = new RuntimeException("You need to install 'guzzlehttp/guzzle' to use it as a sender");

        (new MockBuilder())->setNamespace('\Cmp\Http')
            ->setName('class_exists')
            ->setFunction(function($class) {
                if ($class == Client::class) {
                    return false;
                }
            })->build()->enable();

        $this->withConfig([]);

        $this->shouldThrow($exception)->duringBuild();
    }

    function it_fails_to_build_the_client_with_the_debug_logger_if_symfony_console_is_not_installed()
    {
        $exception = new RuntimeException("You need to install 'symfony/console' to use this feature");

        (new MockBuilder())->setNamespace('\Cmp\Http')
            ->setName('class_exists')
            ->setFunction(function($class) {
                if ($class == ConsoleLogger::class) {
                    return false;
                }
            })->build()->enable();

        $this->shouldThrow($exception)->duringWithConsoleDebug();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ClientBuilder::class);
    }

    function it_can_built_a_default_client()
    {
        $client = $this->withConfig([])->build();

        $client->shouldBeAnInstanceOf(MultiClient::class);
    }

    function it_can_built_a_service_client()
    {
        $client = $this->withConfig([])->build('foo');

        $client->shouldBeAnInstanceOf(ServiceClient::class);
    }

    function it_fails_if_no_config_is_given()
    {
        $this->shouldThrow(RuntimeException::class)->duringBuild();
    }

    public function it_can_chain_all_methods(RequestFactoryInterface $factory, SenderInterface $sender)
    {
        $temp = tempnam(sys_get_temp_dir(), 'temp_');
        file_put_contents($temp, "foo: bar");

        $this
            ->withConfig([])
            ->withConsoleDebug()
            ->withYamlConfig($temp)
            ->withSender($sender)
            ->withGuzzleSender()
            ->withRequestFactory($factory)
            ->withLogger(new NullLogger())
            ->build()
            ->shouldBeAnInstanceOf(MultiClient::class);

        unlink($temp);
    }
}
