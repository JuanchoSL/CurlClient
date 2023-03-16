<?php

declare(strict_types=1);

namespace JuanchoSL\CurlClient;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;

/**
 * Group the cURL response data in order to use from other services
 */
class CurlResponse implements CurlResponseInterface
{

    /**
     *
     * @var array<string,mixed>
     */
    private array $last_info;
    private mixed $body;

    /**
     * Default constructor, set the responsed body and the info from the request
     * @param mixed $body The resturned body from the request
     * @param array<string,mixed> $info The xtra info returned from the request
     */
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
     * @return array<string,string>
     */
    public function getAllInfo(): array
    {
        return $this->last_info;
    }

}
