<?php declare(strict_types=1);

namespace JuanchoSL\CurlCLient\Engines\Common\Traits\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\CurlResponse;
use Psr\Http\Message\UriInterface;

trait SpecialServersMethodsTrait{
	
	
    /**
     * Send an OPTIONS request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function options(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareOptions($url, $header);
        return $this->exec();
    }

    /**
     * Send a TRACE request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function trace(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareTrace($url, $header);
        return $this->exec();
    }
    
    /**
     * Send a HEAD request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function head(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareHead($url, $header);
        $result_execution = curl_exec($this->curl);
        $this->response_info = curl_getinfo($this->curl);
        return new CurlResponse($result_execution . "\r\n\r\n", $this->response_info);
    }
    
    public function connect(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareConnect($url, $header);
        return $this->exec();
    }
}