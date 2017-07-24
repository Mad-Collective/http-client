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
        $body         = $this->buildBody($request['body'], $request['headers'], $placeholders, $values);

        return $this->buildRequest(
            $request['method'],
            $this->replace($uri, $placeholders, $values),
            $this->replaceAll($request['headers'], $placeholders, $values),
            $body,
            $request['version'],
            $request['retries'],
            $request['options']
        );
    }

    /**
     * Builds a request based on the service and request name, using provided $jsonSerializable as request body
     *
     * @param string            $serviceKey
     * @param string            $requestKey
     * @param \JsonSerializable $jsonSerializableBody
     * @return mixed
     */
    public function createFromJson($serviceKey, $requestKey, \JsonSerializable $jsonSerializableBody) {
        $config       = $this->getParamOrFail($this->config, $serviceKey);
        $service      = $this->getServiceParams($config);
        $request      = $this->getRequestParams($config, $requestKey, $service);
        $uri          = $this->buildUri($service['endpoint'], $request['path'], $request['query']);
        $placeholders = $this->getPlaceholdersFromUriAndHeader($uri, $request);

        $values = $this->getPlaceholderValuesFromJson($placeholders, $jsonSerializableBody);
        $this->headerIsJsonOrFail($request['headers']);

        return $this->buildRequest(
            $request['method'],
            $this->replace($uri, $placeholders, $values),
            $this->replaceAll($request['headers'], $placeholders, $values),
            json_encode($jsonSerializableBody),
            $request['version'],
            $request['retries'],
            $request['options']
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
        array $options = []
    ) {
        return new Request($method, $uri, $headers, $body, (string)$version, $retries, $options);
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
    private function buildBody(array $post, array &$headers, array $placeholders, array $values)
    {
        if (count($post) == 0) {
            return null;
        }

        if ($this->isJson($headers)) {
            return $this->replace(json_encode($post), $placeholders, $values);
        }

        if(!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        $post = $this->replaceAll($post, $placeholders, $values);

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
     * @param array $parameters
     * @return array
     */
    private function getPlaceholdersInArray(array $parameters = [])
    {
        $keys = [];

        foreach ($parameters as $parameter) {
            $keys[] = $this->getPlaceholdersInString($parameter);
        }

        return $keys;
    }

    /**
     * @param string $parameter
     * @return array
     */
    private function getPlaceholdersInString($parameter)
    {
        $keys = [];
        preg_match_all('/\${[a-zA-z._0-9]+}/', $parameter, $keys);

        return $keys;
    }

    /**
     * @param string $option
     * @param array  $placeholders
     * @param array  $values
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

    /**
     * @param array $headers
     */
    private function headerIsJsonOrFail(array $headers) {
        if (!$this->isJson($headers)) {
            throw new  RuntimeException("Content-Type header is not equal to \'application/json'");
        }
    }

    /**
     * @param string $uri
     * @param array  $request
     * @return array
     */
    private function getPlaceholdersFromUriAndHeader($uri, $request) {
        $result = [];
        $placeholders = $this->getPlaceholdersInString($uri);
        $placeholders[] = $this->getPlaceholdersInArray($request['headers']);

        array_walk_recursive($placeholders,function($v, $k) use (&$result){ $result[] = $v; });

        return $result;
    }

    /**
     * @param array $placeholders
     * @param \JsonSerializable $jsonSerializable
     * @return array
     */
    private function getPlaceholderValuesFromJson(array $placeholders = [], \JsonSerializable $jsonSerializable) {
        $json = \GuzzleHttp\json_encode($jsonSerializable);
        $jsonAsArray = \GuzzleHttp\json_decode($json, true);
        $values = [];

        foreach ($placeholders as $placeholder) {
            $keysArray = explode('.', $placeholder);

            if (!$keysArray) {
                $keysArray = [$placeholder];
            }

            $currentArray = $jsonAsArray;
            $currentValue = [];

            foreach ($keysArray as $key) {

                $key = str_replace('${', '', $key);
                $key = str_replace('}', '', $key);
                $key = strtolower($key);

                if (isset($currentArray[$key])) {

                    $currentValue = $currentArray[$key];
                    if (is_array($currentValue)) {
                        $currentArray = $currentValue;
                    }
                }
            }

            if (!is_array($currentValue) && !empty($currentValue)) {
                $values[$placeholder] = $currentValue;
            } else {
                throw new RuntimeException("Could not find any matching value for placeholder: $placeholder");
            }
        }

        return $values;
    }
}
