<?php

namespace spec\Cmp\Http\Client;

use Cmp\Http\Client\ServiceClient;
use Cmp\Http\Client\ServiceClientInterface;
use Cmp\Http\Exception\RequestBuildException;
use Cmp\Http\Exception\RequestExecutionException;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;
use Cmp\Http\RequestFactoryInterface;
use Cmp\Http\Sender\SenderInterface;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * @mixin ServiceClient
 */
class ServiceClientSpec extends ObjectBehavior
{
    function let(RequestFactoryInterface $factory, SenderInterface $sender, LoggerInterface $logger)
    {
        $this->beConstructedWith($factory, $sender, $logger, 'service');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ServiceClient::class);
        $this->shouldHaveType(ServiceClientInterface::class);
    }

    function it_can_create_requests(RequestFactoryInterface $factory, Request $request)
    {
        $factory->create('service', 'bar', [1])->willReturn($request);
        $this->request('bar', [1])->shouldReturn($request);
    }

    function it_handle_errors_creating_requests(RequestFactoryInterface $factory, LoggerInterface $logger)
    {
        $exception = new RuntimeException("foo");
        $factory->create('service', 'bar', [1])->willThrow($exception);

        $this->shouldThrow($exception)->duringRequest('bar', [1]);

        $logger->error("Error building request {service}.{request}. {message}", [
            'message'   => Argument::any(),
            'service'   => 'service',
            'request'   => 'bar',
            'exception' => $exception
        ]);
    }

    function it_wraps_unknown_exceptions(RequestFactoryInterface $factory)
    {
        $exception = new \Exception("foo");
        $factory->create('service', 'bar', [1])->willThrow($exception);

        $this->shouldThrow(RequestBuildException::class)->duringRequest('bar', [1]);
    }

    function it_can_send_requests(
        SenderInterface $sender,
        Request $request,
        ResponseInterface $psrResponse,
        LoggerInterface $logger
    ) {
        $this->configureResponse(null, $psrResponse, $sender, $request);

        $this->send($request)->shouldBeAnInstanceOf(Response::class);
        $logger->debug('Sending request. {request}', ['request' => $request])->shouldHaveBeenCalled();
        $logger->debug('Response received. {response}', [
            'request'  => $request,
            'response' => Argument::is(Response::class),
        ]);
    }

    function it_can_execute_a_request_in_one_step(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse(null, $psrResponse, $sender, $request, $factory);

        $this->execute('bar', [1])->shouldBeAnInstanceOf(Response::class);
    }

    function it_can_execute_a_request_with_json_in_one_step(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse,
        \JsonSerializable $jsonSerializable
    ) {
        $params = ['key' => 'value'];
        $jsonSerializable->jsonSerialize()->willReturn($params);

        $this->configureResponse(null, $psrResponse, $sender, $request, $factory, []);
        $request->getRetries()->willReturn(1);
        $request->withJsonPost($params)->willReturn($request);

        $this->executeFromJson('bar', $jsonSerializable)->shouldBeAnInstanceOf(Response::class);
    }

    function it_can_execute_a_request_and_get_the_body(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse('body', $psrResponse, $sender, $request, $factory);

        $this->body('bar', [1])->shouldBeAnInstanceOf(StreamInterface::class);
    }

    function it_can_execute_a_request_and_get_the_body_as_a_json_object(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse('{"some":"thing"}', $psrResponse, $sender, $request, $factory);

        $this->json('bar', [1])->shouldBeAnInstanceOf(\stdClass::class);
    }

    function it_can_execute_a_request_and_get_the_body_as_an_array(
        RequestFactoryInterface $factory,
        Request $request,
        SenderInterface $sender,
        ResponseInterface $psrResponse
    ) {
        $this->configureResponse('{"some":"thing"}', $psrResponse, $sender, $request, $factory);

        $this->jsonAsArray('bar', [1])->shouldReturn(['some' => 'thing']);
    }

    function it_can_retry_a_failed_request(Request $request, SenderInterface $sender, LoggerInterface $logger)
    {
        $firstException  = new ServerException("first try", $request->getWrappedObject());
        $secondException = new TransferException("second try");
        $finalException  = new RequestExecutionException($secondException);

        $request->getRetries()->willReturn(1);
        $request->__toString()->willReturn('request');

        // Throw 2 exceptions
        $sender->send($request)->will(function () use ($sender, $request, $firstException, $secondException) {
            $sender->send($request)->willThrow($secondException);
            throw $firstException;
        });

        $this->shouldThrow($finalException)->duringSend($request);

        $logger->error(Argument::any(), Argument::withEntry('message', 'first try'))->shouldHaveBeenCalled();
        $logger->error(Argument::any(), Argument::withEntry('message', 'second try'))->shouldHaveBeenCalled();
    }

    function it_doesnt_retry_a_failed_request(Request $request, SenderInterface $sender, LoggerInterface $logger)
    {
        $exception  = new \Exception("generic exception");
        $finalException  = new RequestExecutionException($exception);

        $request->getRetries()->willReturn(1);
        $request->__toString()->willReturn('request');

        // Throw 1 exception
        $sender->send($request)->will(function () use ($sender, $request, $exception) {
            throw $exception;
        });

        $this->shouldThrow($finalException)->duringSend($request);

        $logger->error(Argument::any(), Argument::withEntry('message', 'generic exception'))->shouldHaveBeenCalled();
    }

    private function configureResponse(
        $body,
        ResponseInterface $psrResponse,
        SenderInterface $sender,
        Request $request,
        RequestFactoryInterface $factory = null,
        $parameters = [1]
    ) {
        if ($factory) {
            $factory->create('service', 'bar', $parameters)->willReturn($request);
        }

        $psrResponse->getStatusCode()->willReturn(200);
        $psrResponse->getHeaders()->willReturn([]);
        $psrResponse->getBody()->willReturn($body);
        $psrResponse->getProtocolVersion()->willReturn('1.1');
        $psrResponse->getReasonPhrase()->willReturn('OK');
        $sender->send($request)->willReturn($psrResponse);
    }
}
