<?php

namespace Cmp\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Formatter
{
    /**
     * Returns a formatted message string.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    public static function request(RequestInterface $request)
    {
        return self::format(
            $request,
            "{$request->getMethod()} {$request->getRequestTarget()} HTTP/{$request->getProtocolVersion()}"
        );
    }

    /**
     * Returns a formatted message string.
     *
     * @param ResponseInterface $response
     *
     * @return string
     */
    public static function response(ResponseInterface $response)
    {
        return self::format(
            $response, 
            "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()} {$response->getReasonPhrase()}"
        );
    }

    /**
     * @param MessageInterface $message
     * @param string           $main
     *
     * @return string
     */
    private static function format(MessageInterface $message, $main)
    {
        $result  = "\r\n-------------";
        $result .= "\r\n{$main}";

        foreach ($message->getHeaders() as $name => $values) {
            $result .= "\r\n{$name}: " . implode(', ', $values);
        }

        $result .= "\r\n\r\n{$message->getBody()}";
        $result .= "\r\n-------------";

        return $result;
    }
}
