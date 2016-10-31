<?php

namespace Cmp\Http\Message;

use Cmp\Http\Exception\RuntimeException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class Response extends GuzzleResponse
{
    /**
     * @param bool $asArray
     *
     * @return mixed
     */
    public function json($asArray = false)
    {
        $json = json_decode((string) $this->getBody(), $asArray);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf("The response is not a valid json: %s", json_last_error_msg()));
        }

        return $json;
    }

    /**
     * @return mixed
     */
    public function jsonAsArray()
    {
        return $this->json(true);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return Formatter::response($this);
    }
}
