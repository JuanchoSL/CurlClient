<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

interface SpecialServersMethodsInterface
{

    /**
     * Send an OPTIONS request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function options(UriInterface $url, array $header = []): CurlResponseInterface;

    /**
     * Send an TRACE request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function trace(UriInterface $url, array $header = []): CurlResponseInterface;
    
    /**
     * Send a HEAD request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function head(UriInterface $url, array $header = []): CurlResponseInterface;
    
    public function connect(UriInterface $url, array $header = []): CurlResponseInterface;

}