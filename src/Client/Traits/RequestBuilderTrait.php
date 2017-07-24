<?php

namespace Cmp\Http\Client\Traits;

use Cmp\Http\Exception\RequestBuildException;
use Cmp\Http\Exception\RuntimeException;
use Cmp\Http\Message\Request;
use Cmp\Http\RequestFactoryInterface;
use Psr\Log\LoggerInterface;

trait RequestBuilderTrait
{
    /**
     * @return RequestFactoryInterface
     */
    abstract protected function factory();

    /**
     * @return LoggerInterface
     */
    abstract protected function logger();

    /**
     * Builds a request
     * 
     * @param string $service
     * @param string $requestId
     * @param array  $parameters
     *
     * @return Request
     * 
     * @throws RequestBuildException
     * @throws RuntimeException
     */
    protected function createRequest($service, $requestId, array $parameters = [])
    {
        try {
            return $this->factory()->create($service, $requestId, $parameters);
        } catch (RuntimeException $exception) {
            $this->logBuildError($service, $requestId, $exception);
            throw $exception;
        } catch (\Exception $exception) {
            $this->logBuildError($service, $requestId, $exception);
            throw new RequestBuildException($exception);
        }
    }

    /**
     * Builds a request with json body
     *
     * @param string            $service
     * @param string            $requestId
     * @param \JsonSerializable $jsonSerializable
     *
     * @return Request
     *
     * @throws RequestBuildException
     * @throws RuntimeException
     */
    protected function createRequestFromJson($service, $requestId, \JsonSerializable $jsonSerializable)
    {
        try {
            return $this->factory()->createFromJson($service, $requestId, $jsonSerializable);
        } catch (RuntimeException $exception) {
            $this->logBuildError($service, $requestId, $exception);
            throw $exception;
        } catch (\Exception $exception) {
            $this->logBuildError($service, $requestId, $exception);
            throw new RequestBuildException($exception);
        }
    }

    /**
     * @param string     $service
     * @param string     $request
     * @param \Exception $exception
     */
    private function logBuildError($service, $request, \Exception $exception)
    {
        $this->logger()->error("Error building request {service}.{request}. {message}", [
            'message'   => $exception->getMessage(),
            'service'   => $service,
            'request'   => $request,
            'exception' => $exception
        ]);
    }
}
