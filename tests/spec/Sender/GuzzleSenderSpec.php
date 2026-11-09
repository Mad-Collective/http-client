<?php

namespace spec\Cmp\Http\Sender;

use Cmp\Http\Message\Request;
use Cmp\Http\Sender\GuzzleSender;
use Cmp\Http\Sender\SenderInterface;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;

/**
 * @mixin \Cmp\Http\Sender\GuzzleSender
 */
class GuzzleSenderSpec extends ObjectBehavior
{
    function let(ClientInterface $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GuzzleSender::class);
        $this->shouldHaveType(SenderInterface::class);
    }

    function it_can_proxy_the_request_to_guzzle(Request $request, ClientInterface $client, ResponseInterface $response)
    {
        $request->getOptions()->willReturn(['options']);
        $client->send($request, ['options'])->willReturn($response);

        $this->send($request)->shouldReturn($response);
    }
}
