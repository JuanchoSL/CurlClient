<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

interface ExtendedHttpMethodsInterface
{

    /**
     * Send an MOVE request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function move(UriInterface $url, array $header = []): CurlResponseInterface;

    /**
     * Send a PROPFIND request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function propfind(UriInterface $url, array $header = []): CurlResponseInterface;

    /**
     * Send a MKCOL request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function mkcol(UriInterface $url, array $header = []): CurlResponseInterface;

}