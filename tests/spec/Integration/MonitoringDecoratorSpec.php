<?php

namespace spec\Cmp\Http\Integration;

use Cmp\Http\Integration\MonitoringDecorator;
use Cmp\Monitoring\Monitor;
use Cmp\Http\Sender\SenderInterface;
use PhpSpec\ObjectBehavior;
use Cmp\Http\Message\Request;
use Psr\Http\Message\ResponseInterface;

class MonitoringDecoratorSpec extends ObjectBehavior
{
    function let(SenderInterface $sender, Monitor $monitor)
    {
        $this->beConstructedWith($sender, $monitor, 'test_metric');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MonitoringDecorator::class);
    }

    function it_sends_metrics(SenderInterface $sender, Monitor $monitor, Request $request, ResponseInterface $response)
    {
        $request->getRequestKey()->willReturn('new_user');
        $request->getServiceKey()->willReturn('ss');
        $sender->send($request)->willReturn($response);
        $monitor->start('test_metric', ['service_key' => 'ss', 'request_key' => 'new_user'])->shouldBeCalled();
        $monitor->end('test_metric')->shouldBeCalled();
        $this->send($request)->shouldReturn($response);
    }
}
