<?php

namespace Cmp\Http\Sender;

use Cmp\Http\Message\Request;
use Psr\Http\Message\ResponseInterface;

interface SenderInterface
{
    /**
     * @param \Cmp\Http\Message\Request $request
     *
     * @return ResponseInterface
     */
    public function send(Request $request);
}
