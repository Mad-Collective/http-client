<?php

namespace spec\Cmp\Http\Client;

use Cmp\Http\Client\MultiClient;
use Cmp\Http\Client\MultiClientInterface;
use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;
use Cmp\Http\RequestFactoryInterface;
use Cmp\Http\Sender\SenderInterface;
use Cmp\Http\Integration\Monitor;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * @mixin \Cmp\Http\Client\MultiClient
 */
class MultiClientSpec extends ObjectBehavior
{
    function let(RequestFactoryInterface $factory, SenderInterface $sender, LoggerInterface $logger, Monitor $monitor)
    {
        $this->beConstructedWith($factory, $sender, $logger, $monitor, 'external_requests');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MultiClient::class);
        $this->shouldHaveType(MultiClientInterface::class);
    }

    function it_can_execute_a_request_in_one_step(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse(null, $psrResponse, $sender, $request, $factory);

        $this->execute('service', 'bar', [1])->shouldBeAnInstanceOf(Response::class);
    }

    function it_can_execute_a_request_and_get_the_body(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse('body', $psrResponse, $sender, $request, $factory);

        $this->body('service', 'bar', [1])->shouldBeAnInstanceOf(StreamInterface::class);
    }

    function it_can_execute_a_request_and_get_the_body_as_a_json_object(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse('{"some":"thing"}', $psrResponse, $sender, $request, $factory);

        $this->json('service', 'bar', [1])->shouldBeAnInstanceOf(\stdClass::class);
    }

    function it_can_execute_a_request_and_get_the_body_as_an_array(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse('{"some":"thing"}', $psrResponse, $sender, $request, $factory);

        $this->jsonAsArray('service', 'bar', [1])->shouldReturn(['some' => 'thing']);
    }

    private function configureResponse(
        $body,
        ResponseInterface $psrResponse,
        SenderInterface $sender,
        Request $request,
        RequestFactoryInterface $factory = null
    ) {
        if ($factory) {
            $factory->create('service', 'bar', [1])->willReturn($request);
        }

        $psrResponse->getStatusCode()->willReturn(200);
        $psrResponse->getHeaders()->willReturn([]);
        $psrResponse->getBody()->willReturn($body);
        $psrResponse->getProtocolVersion()->willReturn('1.1');
        $psrResponse->getReasonPhrase()->willReturn('OK');
        $sender->send($request)->willReturn($psrResponse);
    }
}
