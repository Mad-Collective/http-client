<?php

namespace spec\Cmp\Http\Message;

use Cmp\Http\Message\Request;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;

/**
 * @mixin \Cmp\Http\Message\Request
 */
class RequestSpec extends ObjectBehavior
{
    private $method  = 'GET';
    private $uri     = 'http://foo.com/test?john=doe&jane=bar';
    private $headers = ['Content-Type' => 'application/xml'];
    private $body    = '{"body":"contents"}';
    private $version = '1.1';
    private $retries = 5;
    private $options = ['timeout' => 1];

    function let(RequestInterface $request)
    {
        $this->beConstructedWith(
            $this->method,
            $this->uri,
            $this->headers,
            $this->body,
            $this->version,
            $this->retries,
            $this->options
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Request::class);
        $this->shouldHaveType(RequestInterface::class);
    }

    function it_can_get_the_request_options()
    {
        $this->getOptions()->shouldReturn(['timeout' => 1]);
    }

    function it_can_get_one_request_option()
    {
        $this->getOption('timeout')->shouldReturn(1);
    }

    function it_can_default_a_request_option()
    {
        $this->getOption('connect_timeout', 3)->shouldReturn(3);
    }

    function it_can_get_the_request_retries()
    {
        $this->getRetries()->shouldReturn(5);
    }

    function it_can_add_a_query_parameter()
    {
        $this->withQueryParameter('new', 'nice')
            ->getUri()
            ->__toString()
            ->shouldReturn('http://foo.com/test?john=doe&jane=bar&new=nice');
    }

    function it_can_add_a_query_parameter_with_special_chars_to_encode_on_consecutive_calls()
    {
        $this->withQueryParameter('email', 'name+1234@domain.com')
            ->withQueryParameter('new', 'nice')
            ->getUri()
            ->__toString()
            ->shouldReturn('http://foo.com/test?john=doe&jane=bar&email=name%2B1234%40domain.com&new=nice');
    }

    function it_can_overwrite_a_query_parameter()
    {
        $this->withQueryParameter('john', 'not')
            ->getUri()
            ->__toString()
            ->shouldReturn('http://foo.com/test?john=not&jane=bar');
    }

    function it_can_set_post_params()
    {
        $this->withPost(['some' => 'nice', 'things' => 3])
            ->getBody()
            ->__toString()
            ->shouldReturn('some=nice&things=3');
    }

    function it_can_set_a_json_as_body()
    {
        $request = $this->withJsonPost(['some' => 'nice', 'things' => 3]);

        $request
            ->getBody()
            ->__toString()
            ->shouldReturn('{"some":"nice","things":3}');

        $request
            ->getHeaderLine('Content-Type')
            ->shouldReturn('application/json');
    }

    function it_sets_content_type_header_when_setting_post_params()
    {
        $this->beConstructedWith(
            $this->method,
            $this->uri,
            [],
            $this->body,
            $this->version,
            $this->retries,
            $this->options
        );
        $request = $this->withPost(['foo' => 'bar']);
        $request
            ->getHeaderLine('Content-Type')
            ->shouldReturn('application/x-www-form-urlencoded');
    }

    function it_doesnt_overwrite_content_type_header_when_setting_post_params()
    {
        $request = $this->withPost(['foo' => 'bar']);
        $request
            ->getHeaderLine('Content-Type')
            ->shouldNotReturn('application/x-www-form-urlencoded');
    }

    function it_can_be_converted_to_string()
    {
        $message = "\r\n-------------"
                 . "\r\nGET /test?john=doe&jane=bar HTTP/1.1"
                 . "\r\nHost: foo.com"
                 . "\r\nContent-Type: application/xml"
                 . "\r\n\r\n{\"body\":\"contents\"}"
                 . "\r\n-------------"
                 . "\r\n >> Retries: 5"
                 . "\r\n >> Options: "
                 . print_r($this->options, true);

        $this->__toString()->shouldReturn($message);
    }
}
