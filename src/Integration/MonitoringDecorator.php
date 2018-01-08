<?php
namespace Cmp\Http\Integration;

use Cmp\Http\Message\Request;
use Cmp\Http\Sender\SenderInterface;
use Cmp\Monitoring\Monitor;
use Psr\Http\Message\ResponseInterface;

class MonitoringDecorator implements SenderInterface
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
     * @var string
     */
    private $metricName;

    /**
     * SenderDecorator constructor.
     *
     * @param SenderInterface $sender
     * @param Monitor         $monitor
     * @param string          $metricName
     */
    public function __construct(SenderInterface $sender, Monitor $monitor, $metricName)
    {
        $this->sender = $sender;
        $this->monitor = $monitor;
        $this->metricName = $metricName;
    }

    /**
     * @param Request $request
     *
     * @return ResponseInterface
     */
    public function send(Request $request)
    {
        $this->monitor->start($this->metricName, ['service_key' => $request->getServiceKey(), 'request_key' => $request->getRequestKey()]);
        $response = $this->sender->send($request);
        $this->monitor->end($this->metricName);
        return $response;
    }
}
