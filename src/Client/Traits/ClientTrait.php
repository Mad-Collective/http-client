<?php

namespace Cmp\Http\Client\Traits;

use Cmp\Http\Exception\RequestExecutionException;
use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;
use Cmp\Http\Sender\SenderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Use this trait in your custom client to have a default implementation for the shortcuts
 */
trait ClientTrait
{
    /**
     * @return SenderInterface
     */
    abstract protected function sender();

    /**
     * @return LoggerInterface
     */
    abstract protected function logger();

    /**
     * Executes a request returning back the response
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws RequestExecutionException
     * @throws RuntimeException
     */
    public function send(Request $request)
    {
        $this->logger()->debug('Sending request. {request}', [
            'request'  => (string) $request,
        ]);

        $response = $this->sendOrRetry($request, $request->getRetries());

        $this->logger()->debug('Response received. {response}', [
            'request'  => (string) $request,
            'response' => (string) $response,
        ]);

        return $response;
    }

    /**
     * @param Request $request
     * @param int     $retriesLeft
     *
     * @return Response
     */
    private function sendOrRetry(Request $request, $retriesLeft)
    {
        try {
            return $this->fromPsrResponse($this->sender()->send($request));
        } catch (\Exception $exception) {
            $this->logErrorSendingRequest($request, $retriesLeft, $exception);
            if ($retriesLeft > 0) {
                return $this->sendOrRetry($request, --$retriesLeft);
            }

            throw new RequestExecutionException($exception);
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @return Response
     */
    private function fromPsrResponse(ResponseInterface $response)
    {
        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }

    /**
     * @param Request    $request
     * @param string     $retriesLeft
     * @param \Exception $exception
     */
    private function logErrorSendingRequest(Request $request, $retriesLeft, \Exception $exception)
    {
        $this->logger()->error("Error sending request: {message}. {retries_left} retries left. Request {request}", [
            'message'      => $exception->getMessage(),
            'request'      => (string) $request,
            'exception'    => (string) $exception,
            'retries_left' => $retriesLeft,
        ]);
    }
}
