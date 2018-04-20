<?php

namespace Cmp\Http;

use Cmp\Http\Exception\ConfigurationException;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Request;

class RequestFactory implements RequestFactoryInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * ConfigFactory constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Builds a request based on the service and request name, with the given parameters
     *
     * @param string $serviceKey
     * @param string $requestKey
     * @param array  $parameters
     *
     * @return Request
     */
    public function create($serviceKey, $requestKey, array $parameters = [])
    {
        $config       = $this->getParamOrFail($this->config, $serviceKey);
        $service      = $this->getServiceParams($config);
        $request      = $this->getRequestParams($config, $requestKey, $service);
        $uri          = $this->buildUri($service['endpoint'], $request['path'], $request['query']);
        $placeholders = $this->getPlaceholders($parameters);
        $values       = array_values($parameters);
        $body         = $this->buildBody($request['body'], $request['headers'], $placeholders, $values, $parameters);

        return $this->buildRequest(
            $request['method'],
            $this->replace($uri, $placeholders, $values),
            $this->replaceAll($request['headers'], $placeholders, $values),
            $body,
            $request['version'],
            $request['retries'],
            $request['options'],
            $serviceKey,
            $requestKey
        );
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $headers
     * @param mixed  $body
     * @param string $version
     * @param int    $retries
     * @param array  $options
     *
     * @return Request
     */
    protected function buildRequest(
        $method,
        $uri,
        array $headers,
        $body = null,
        $version = '1.1',
        $retries = 0,
        array $options = [],
        $serviceKey,
        $requestKey
    ) {
        return new Request($method, $uri, $headers, $body, (string)$version, $retries, $options, $serviceKey, $requestKey);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function getServiceParams(array $config)
    {
        return [
            'endpoint' => $this->getParamOrFail($config, 'endpoint'),
            'headers'  => $this->getParam($config, 'headers', []),
            'query'    => $this->getParam($config, 'query', []),
            'body'     => $this->getParam($config, 'body', []),
            'version'  => $this->getParam($config, 'version', '1.1'),
            'retries'  => $this->getParam($config, 'retries', 0),
            'options'  => $this->getParam($config, 'options', []),
        ];
    }

    /**
     * @param array  $config
     * @param string $requestKey
     * @param array  $service
     *
     * @return array
     */
    private function getRequestParams(array $config, $requestKey, array $service)
    {
        $config = $this->getParamOrFail($config, 'requests');
        $config = $this->getParamOrFail($config, $requestKey);

        return [
            'path'    => $this->getParamOrFail($config, 'path'),
            'method'  => $this->getParam($config, 'method', 'GET'),
            'headers' => array_merge($service['headers'], $this->getParam($config, 'headers', [])),
            'query'   => array_merge($service['query'], $this->getParam($config, 'query', [])),
            'body'    => array_merge($service['body'], $this->getParam($config, 'body', [])),
            'version' => $this->getParam($config, 'version', $service['version']),
            'retries' => $this->getParam($config, 'retries', $service['retries']),
            'options' => array_merge($service['options'], $this->getParam($config, 'options', [])),
        ];
    }

    /**
     * @param string $endpoint
     * @param string $path
     * @param array  $query
     *
     * @return string
     */
    private function buildUri($endpoint, $path, array $query)
    {
        $queryString = '';
        if (count($query) > 0) {
            $queryString = '?'.urldecode(http_build_query($query));
        }

        return $endpoint.$path.$queryString;
    }

    /**
     * @param array $post
     * @param array $headers
     * @param array $placeholders
     * @param array $values
     *
     * @return string
     */
    private function buildBody(array $post, array &$headers, array $placeholders, array $values, array $parameters)
    {
        if (count($post) == 0) {
            return null;
        }

        $post = array_merge($parameters, $this->replacePreservingType($post, $placeholders, $values));

        if ($this->isJson($headers)) {
            return json_encode($post);
        }

        if(!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        return http_build_query($post);
    }

    /**
     * @param array  $config
     * @param string $param
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getParam(array $config, $param, $default = null)
    {
        return isset($config[$param])  ? $config[$param] : $default;
    }

    /**
     * @param array  $config
     * @param string $param
     *
     * @return mixed
     */
    private function getParamOrFail(array $config, $param)
    {
        if (!isset($config[$param])) {
            throw new RuntimeException("The configuration is missing the required parameter: $param");
        }

        return $config[$param];
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    private function getPlaceholders(array $parameters)
    {
        $keys = [];

        foreach (array_keys($parameters) as $key) {
            $keys[] = strtoupper(sprintf('${%s}', $key));
        }

        return $keys;
    }

    /**
     * @param string|array $option
     * @param array        $placeholders
     * @param array        $values
     *
     * @return string
     */
    private function replace($option, array $placeholders, array $values)
    {
        return str_replace($placeholders, $values, $option);
    }

    /**
     * @param array $options
     * @param array $placeholders
     * @param array $values
     *
     * @return array
     */
    private function replaceAll(array $options, array $placeholders, array $values)
    {
        foreach ($options as $key => $option) {
            $options[$key] = $this->replace($option, $placeholders, $values);
        }

        return $options;
    }

    /**
     * @param array $options
     * @param array $placeholders
     * @param array $values
     *
     * @return array
     */
    private function replacePreservingType(array $options, array $placeholders, array $values)
    {
        foreach ($options as $key => $option) {
            $plKey = array_search($option, $placeholders);
            if ($plKey !== false) {
                $options[$key] = $values[$plKey];
                continue;
            }
            $options[$key] = $this->replace($option, $placeholders, $values);
        }

        return $options;
    }

    /**
     * @param array $headers
     *
     * @return bool
     */
    private function isJson(array $headers)
    {
        if (!isset($headers['Content-Type'])) {
            return false;
        }

        // http://greenbytes.de/tech/webdav/rfc2616.html#rfc.section.14.17
        return strpos($headers['Content-Type'], 'application/json') === 0;
    }
}