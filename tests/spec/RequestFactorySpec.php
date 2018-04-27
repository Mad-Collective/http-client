<?php

namespace spec\Cmp\Http;

use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Request;
use Cmp\Http\RequestFactory;
use Cmp\Http\RequestFactoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @mixin \Cmp\Http\RequestFactory
 */
class RequestFactorySpec extends ObjectBehavior
{
    private $config;

    function let()
    {
        $this->config = Yaml::parse($this->getConfigSample());
        $this->beConstructedWith($this->config);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RequestFactory::class);
        $this->shouldHaveType(RequestFactoryInterface::class);
    }

    function it_can_build_a_request()
    {
        $request = $this->create('service', 'request_1', [
            'header_1' => 'replaced_header_1',
            'header_3' => 'replaced_header_3',
            'path'     => 'replaced_path',
            'query_2'  => 'replaced_query_2',
            'body_2'   => 'replaced_body_2'
        ]);

        $request->shouldBeAnInstanceOf(Request::class);

        $request->getHeaders()->shouldReturn([
            'Host'         => ['service.com'],
            'Content-Type' => ['application/json'],
            'header_1'     => ['replaced_header_1'],
            'header_3'     => ['replaced_header_3']
        ]);

        $request->getUri()
            ->__toString()
            ->shouldReturn(
                'http://service.com/v1/request_1/replaced_path?'
                .'query_1=query_one&query_2=replaced_query_2&query_3=three'
            );

        $request->getBody()->__toString()->shouldBe('{"header_1":"replaced_header_1","header_3":"replaced_header_3","path":"replaced_path","query_2":"replaced_query_2","body_1":"body_one","body_2":"replaced_body_2","body_3":"body_three"}');

        $request->getProtocolVersion()->shouldBe('1.3');
        $request->getRetries()->shouldBe(4);
        $request->getOptions()->shouldBe([
            'timeout' => 5,
            'auth'    => ['user', 'password'],
            'json'    => true,
        ]);
    }

    function it_can_build_a_proper_json_encoded_request()
    {
        $request = $this->create('service', 'request_1', [
            'header_1' => 'replaced_header_1',
            'header_3' => 'replaced_header_3',
            'path'     => 'replaced_path',
            'query_2'  => 'replaced_query_2',
            'body_2'   => 'replaced_"body"_2'
        ]);

        $request->shouldBeAnInstanceOf(Request::class);

        $request->getHeaders()->shouldReturn([
            'Host'         => ['service.com'],
            'Content-Type' => ['application/json'],
            'header_1'     => ['replaced_header_1'],
            'header_3'     => ['replaced_header_3']
        ]);

        $request->getUri()
            ->__toString()
            ->shouldReturn(
                'http://service.com/v1/request_1/replaced_path?'
                .'query_1=query_one&query_2=replaced_query_2&query_3=three'
            );
        $request->getBody()->__toString()->shouldBe('{"header_1":"replaced_header_1","header_3":"replaced_header_3","path":"replaced_path","query_2":"replaced_query_2","body_1":"body_one","body_2":"replaced_\"body\"_2","body_3":"body_three"}');
        $request->getProtocolVersion()->shouldBe('1.3');
        $request->getRetries()->shouldBe(4);
        $request->getOptions()->shouldBe([
            'timeout' => 5,
            'auth'    => ['user', 'password'],
            'json'    => true,
        ]);
    }

    function it_will_preserve_the_type_for_json_encoded_requests()
    {
        $request = $this->create('service', 'request_3', [
            'body_1' => 555,
            'body_2' => 555,
        ]);

        $request->shouldBeAnInstanceOf(Request::class);
        Assert::eq(
            json_decode($request->getBody()->__toString()->getWrappedObject(), true),
            [
                'body_1' => 'extra 555',
                'body_2' => 555,
            ]
        );

    }

    function it_can_build_a_post_request_with_the_correct_header()
    {
        $request = $this->create('service', 'request_2', [
            'body_1' => 'body 1',
            'body_2' => 'body,2',
            'body_3' => 'body/3',
        ]);

        $request->shouldBeAnInstanceOf(Request::class);

        $request->getHeaders()->shouldReturn([
            'Host'         => ['service.com'],
            'Content-Type' => ['application/xml'],
            'header_1'     => ['${HEADER_1}'],
        ]);

        $request->getBody()->__toString()->shouldBe('body_1=extra+body+1&body_3=body%2F3&body_2=body%2C2');
    }

    function it_can_build_a_post_request_with_default_value_when_content_type_is_not_set()
    {
        $request = $this->create('missing_content_type', 'request', [
            'body_1' => 'body 1',
            'body_2' => 'body,2',
        ]);

        $request->shouldBeAnInstanceOf(Request::class);

        $request->getHeaders()->shouldReturn([
            'Host'         => ['service.com'],
            'Content-Type' => ['application/x-www-form-urlencoded']
        ]);
        $request->getBody()->__toString()->shouldBe('body_1=extra+body+1&body_2=body%2C2');
    }

    function it_fails_if_a_service_is_missing()
    {
        $this->shouldThrow(RuntimeException::class)->duringCreate('unknown', 'foo');
    }

    function it_fails_if_an_endpoint_is_missing()
    {
        $this->shouldThrow(RuntimeException::class)->duringCreate('no_endpoint', 'foo');
    }

    function it_fails_if_the_service_has_not_requests()
    {
        $this->shouldThrow(RuntimeException::class)->duringCreate('no_requests', 'foo');
    }

    function it_fails_if_the_request_is_not_defined()
    {
        $this->shouldThrow(RuntimeException::class)->duringCreate('missing_request', 'foo');
    }

    function it_not_overriding_placehoders_with_extra_parms()
    {
        $request = $this->create('service', 'request_4', [
            'body_mandatory' => 'body 1',
            'body_collision' => 'body 2',
            'body_extra' => 'body 3',
            'body_2' =>""
        ]);

        $request->shouldBeAnInstanceOf(Request::class);

        $request->getHeaders()->shouldReturn([
            'Host'         => ['service.com'],
            'Content-Type' => ['application/json'],
            'header_1'     => ['${HEADER_1}'],
        ]);

        $request->getBody()->__toString()->shouldBe('{"body_extra":"body 3","body_1":"body_one","body_2":"","body_mandatory":"body 1","body_wtf":"body 2","body_wtf2":"body 2"}');
    }

    private function getConfigSample()
    {
        return <<<'YAML'
service:
  endpoint: http://service.com/v1
  query:
    query_1: query_one
    query_2: ${QUERY_2}
  body:
    body_1: body_one
    body_2: ${BODY_2}
  headers:
    Content-Type: application/xml
    header_1: ${HEADER_1}
  version: 1.2
  retries: 3
  options:
    timeout: 2
    auth: ['user', 'password']
  requests:
    request_1:
      path: /request_1/${PATH}
      method: POST
      query:
        query_2: ${QUERY_2}
        query_3: three
      body:
        body_3: body_three
      headers:
        Content-Type: application/json
        header_3: ${HEADER_3}
      version: 1.3
      retries: 4
      options: 
        timeout: 5
        json: true
    request_2:
      path: /request_2
      method: POST
      body:
        body_1: extra ${BODY_1}
        body_2: ${BODY_2}
    request_3:
      headers:
        Content-Type: application/json    
      path: /request_2
      method: POST
      body: {body_1: 'extra ${BODY_1}', body_2: '${BODY_2}'}
    request_4:
      headers:
        Content-Type: application/json    
      path: /request_2
      method: POST
      body: {body_mandatory: '${BODY_MANDATORY}', body_wtf: '${BODY_COLLISION}', body_wtf2: '${BODY_COLLISION}'}

no_endpoint:
  foo: bar

no_requests:
  endpoint: http://no_requests.com/v1

missing_request:
  endpoint: http://no_requests.com/v1
  requests:
    no_this_one:
      path: /foo
      
missing_content_type:
  endpoint: http://service.com/v1
  requests:
      request:
        path: /request
        method: POST
        query:
          query_3: three
        body:
            body_1: extra ${BODY_1}
            body_2: ${BODY_2}
        version: 1.3
        retries: 4
        options: 
        timeout: 5
        json: true   
YAML;
    }
}
