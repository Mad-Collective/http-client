<?php

namespace Cmp\Http\Message;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Http\Message\UriInterface;

class Request extends GuzzleRequest
{
    /**
     * @var int
     */
    private $retries;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var string
     */
    private $serviceKey;

    /**
     * @var string
     */
    private $requestKey;

    /**
     * Request constructor.
     *
     * @param string                   $method
     * @param UriInterface|string|null $uri
     * @param array                    $headers
     * @param null                     $body
     * @param string                   $version
     * @param int                      $retries
     * @param array                    $options
     * @param string                   $serviceKey
     * @param string                   $requestKey
     */
    public function __construct(
        $method,
        $uri = null,
        array $headers = [],
        $body = null,
        $version = '1.1',
        $retries = 0,
        array $options = [],
        $serviceKey = null,
        $requestKey = null
    ) {
        parent::__construct($method, (string) $uri, $headers, $body, $version);
        $this->retries = $retries;
        $this->options = $options;
        $this->serviceKey = $serviceKey;
        $this->requestKey = $requestKey;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * @return int
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Request
     */
    public function withQueryParameter($key, $value)
    {
        $query = $this->getUri()->getQuery();
        parse_str($query, $params);
        $params[$key] = $value;

        return new self(
            $this->getMethod(),
            $this->getUri()->withQuery(http_build_query($params)),
            $this->getHeaders(),
            $this->getBody(),
            $this->getProtocolVersion(),
            $this->getRetries(),
            $this->getOptions(),
            $this->getServiceKey(),
            $this->getRequestKey()
        );
    }

    /**
     * @param array $params
     *
     * @return Request
     */
    public function withPost(array $params)
    {
        $request = $this;
        if ($this->isJson()) {
            $body = json_encode($params);
        } else {
            $body = http_build_query($params);
            if (!$this->hasHeader('Content-Type')) {
                $request = $this->withHeader('Content-Type', 'application/x-www-form-urlencoded');
            }
        }

        return new self(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $body,
            $request->getProtocolVersion(),
            $request->getRetries(),
            $request->getOptions(),
            $this->getServiceKey(),
            $this->getRequestKey()
        );
    }

    /**
     * @param array $params
     *
     * @return Request
     */
    public function withJsonPost(array $params)
    {
        $request = $this->withHeader('Content-Type', 'application/json');

        return $request->withPost($params);
    }

    /**
     * checks if the request should be send as a json
     *
     * @return bool
     */
    public function isJson()
    {
        return strpos($this->getHeaderLine('Content-Type'), 'application/json') === 0;
    }

    /**
     * @return string
     */
    public function getRequestKey()
    {
        return $this->requestKey;
    }

    /**
     * @return string
     */
    public function getServiceKey()
    {
        return $this->serviceKey;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $message  = Formatter::request($this);
        $message .= "\r\n >> Retries: {$this->getRetries()}";
        $message .= "\r\n >> Options: ";
        $message .= print_r($this->getOptions(), true);

        return $message;
    }
}