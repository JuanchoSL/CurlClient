<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Preparations;

use CurlHandle;
use Psr\Http\Message\UriInterface;

interface SpecialServersMethodsInterface
{

    /**
     * prepare an OPTIONS curlhandle
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare handle
     * @return CurlHandle CurlHandle response
     */
    public function prepareOptions(UriInterface $url, array $header = []): CurlHandle;

    /**
     * prepare a TRACE curlhandle
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare handle
     * @return CurlHandle CurlHandle response
     */
    public function prepareTrace(UriInterface $url, array $header = []): CurlHandle;

    /**
     * prepare a HEAD curlhandle
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare handle
     * @return CurlHandle CurlHandle response
     */
    public function prepareHead(UriInterface $url, array $header = []): CurlHandle;

    public function prepareConnect(UriInterface $url, array $header = []): CurlHandle;

}