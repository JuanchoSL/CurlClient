<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

interface ListMethodsInterface
{

    /**
     * Send a GET request to the URL, but using as a directory,in order to list files from ftps or emails
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function list(UriInterface $url, array $header = []): CurlResponseInterface;
}