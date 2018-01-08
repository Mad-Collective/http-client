<?php
namespace Cmp\Http\Integration;

use Cmp\Http\Message\Request;
use Cmp\Http\Sender\SenderInterface;
use Psr\Http\Message\ResponseInterface;

class SenderDecorator implements SenderInterface
{
    /**
     * @var SenderInterface
     */
    private $sender;

    /**
     * @var Monitor
     */
    private $monitor;

    /**
     * SenderDecorator constructor.
     *
     * @param SenderInterface $sender
     * @param Monitor         $monitor
     */
    public function __construct(SenderInterface $sender, Monitor $monitor)
    {
        $this->sender = $sender;
        $this->monitor = $monitor;
    }

    /**
     * @param Request $request
     *
     * @return ResponseInterface
     */
    public function send(Request $request)
    {
        $this->monitor->start(['service_key' => $request->getServiceKey(), 'request_key' => $request->getRequestKey()]);
        $response = $this->sender->send($request);
        $this->monitor->end();
        return $response;
    }
}
