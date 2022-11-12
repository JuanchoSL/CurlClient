<?php

declare(strict_types=1);

namespace JuanchoSL\CurlClient;

class CurlResponse
{

    private $last_info;
    private $body;

    public function __construct(mixed $body, array $info)
    {
        $this->last_info = $info;
        $this->body = $body;
    }

    /**
     * Return the HTTP RESPONSE CODE from the request result
     * @return int the http code response
     */
    public function getResponseCode(): int
    {
        return $this->last_info['http_code'];
    }

    /**
     * Return the CONTENT-TYPE HEADER from the request result
     * @return string The content-type header value
     */
    public function getContentType(): string
    {
        return $this->last_info['content_type'];
    }

    /**
     * Return the body content from the request result
     * @return mixed The request response body
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Retrieve ALL available info
     * @return array
     */
    public function getAllInfo(): array
    {
        return $this->last_info;
    }

}
