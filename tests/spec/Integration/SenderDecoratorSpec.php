<?php

namespace spec\Cmp\Http\Integration;

use Cmp\Http\Integration\Monitor;
use Cmp\Http\Sender\SenderInterface;
use PhpSpec\ObjectBehavior;
use Cmp\Http\Message\Request;
use Psr\Http\Message\ResponseInterface;

class SenderDecoratorSpec extends ObjectBehavior
{
    function let(SenderInterface $sender, Monitor $monitor)
    {
        $this->beConstructedWith($sender, $monitor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Cmp\Http\Integration\SenderDecorator');
    }

    function it_sends_metrics(SenderInterface $sender, Monitor $monitor, Request $request, ResponseInterface $response)
    {
        $request->getRequestKey()->willReturn('new_user');
        $request->getServiceKey()->willReturn('ss');
        $sender->send($request)->willReturn($response);
        $monitor->start(['service_key' => 'ss', 'request_key' => 'new_user'])->shouldBeCalled();
        $monitor->end()->shouldBeCalled();
        $this->send($request)->shouldReturn($response);
    }
}
