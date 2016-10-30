<?php

namespace Cmp\Http\Exception;

class RuntimeException extends \RuntimeException
{
    protected $message = "Runtime exception on http client library";
}
