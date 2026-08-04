<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Preparations;

use CurlHandle;
use Psr\Http\Message\UriInterface;

interface ExtendedHttpMethodsInterface
{

    /**
     * prepare a MOVE curlhandle
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare in this request
     * @return CurlHandle CurlHandle response
     */
    public function prepareMove(UriInterface $url, array $header = []): CurlHandle;

    /**
     * prepare a PROPFIND curlhandle
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare in this request
     * @return CurlHandle CurlHandle response
     */
    public function preparePropfind(UriInterface $url, array $header = []): CurlHandle;

    /**
     * prepare a MKCOL curlhandle
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare in this request
     * @return CurlHandle CurlHandle response
     */
    public function prepareMkcol(UriInterface $url, array $header = []): CurlHandle;

}