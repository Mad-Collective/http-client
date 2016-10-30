<?php

namespace Cmp\Http\Exception;

class RequestExecutionException extends RuntimeException
{
    /**
     * RequestException constructor.
     *
     * @param \Exception $previous
     */
    public function __construct(\Exception $previous)
    {
        parent::__construct(sprintf('Request execution failed: %s', $previous->getMessage()), 0, $previous);
    }
}
