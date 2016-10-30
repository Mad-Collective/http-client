<?php

namespace spec\Cmp\Http\Exception;

use Cmp\Http\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin RuntimeException
 */
class RuntimeExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RuntimeException::class);
    }

    function it_has_a_default_message()
    {
        $this->getMessage()->shouldReturn('Runtime exception on http client library');
    }
}
