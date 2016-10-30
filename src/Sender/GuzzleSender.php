<?php

namespace Cmp\Http\Sender;

use Cmp\Http\Message\Request;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleSender implements SenderInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * GuzzleHttp constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param Request $request
     *
     * @return ResponseInterface
     */
    public function send(Request $request)
    {
        return $this->client->send($request, $request->getOptions());
    }
}
