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
        $this->shouldHaveType(\RuntimeException::class);
    }
}
