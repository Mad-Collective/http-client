<?php

namespace spec\Cmp\Http\Message;

use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Response;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;

/**
 * @mixin \Cmp\Http\Message\Response
 */
class ResponseSpec extends ObjectBehavior
{
    private $status  = 200;
    private $headers = ['Content-Type' => 'application/json'];
    private $body    = '{"malformed"}';
    private $version = '1.1';
    private $reason  = 'OK';

    function let()
    {
        $this->beConstructedWith(
            $this->status,
            $this->headers,
            $this->body,
            $this->version,
            $this->reason
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Response::class);
        $this->shouldHaveType(ResponseInterface::class);
    }

    function it_throws_an_exception_when_trying_to_parse_an_invalid_json()
    {
        $this->shouldThrow(RuntimeException::class)->duringJsonAsArray();
    }

    function it_can_be_converted_to_string()
    {
        $message = "\r\n-------------"
                 . "\r\nHTTP/1.1 200 OK"
                 . "\r\nContent-Type: application/json"
                 . "\r\n\r\n{\"malformed\"}"
                 . "\r\n-------------";

        $this->__toString()->shouldReturn($message);
    }
}
