<?php

namespace spec\Cmp\Http\Exception;

use Cmp\Http\Exception\RequestExecutionException;
use Cmp\Http\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin RequestExecutionException
 */
class RequestExecutionExceptionSpec extends ObjectBehavior
{
    /** @var \Exception */
    private $previous;

    function let()
    {
        $this->previous = new \Exception('foo'); 
        $this->beConstructedWith($this->previous);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RequestExecutionException::class);
        $this->shouldHaveType(RuntimeException::class);
    }

    function it_has_a_default_message()
    {
        $this->getMessage()->shouldReturn('Request execution failed: foo');
    }

    function it_has_access_to_the_previous_exception()
    {
        $this->getPrevious()->shouldReturn($this->previous);
    }
}
