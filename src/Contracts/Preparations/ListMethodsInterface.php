<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Preparations;

use CurlHandle;
use Psr\Http\Message\UriInterface;

interface ListMethodsInterface
{

    /**
     * prepare a GET curlhandle, but using as a directory,in order to list files from ftps or emails
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare handle
     * @return CurlHandle CurlHandle response
     */
    public function prepareList(UriInterface $url, array $header = []): CurlHandle;
}