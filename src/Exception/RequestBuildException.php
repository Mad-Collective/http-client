<?php

namespace Cmp\Http\Exception;

class RequestBuildException extends RuntimeException
{
    /**
     * RequestBuildException constructor.
     *
     * @param \Exception $previous
     */
    public function __construct(\Exception $previous)
    {
        parent::__construct(sprintf('Request build failed: %s', $previous->getMessage()), 0, $previous);
    }
}
