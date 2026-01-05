<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Http;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\CurlResponse;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote services as APIs
 */
class CurlHttpRequest extends CurlHttpHandler
{

    protected CurlHandle $curl;

    /**
     *
     * @var array<string,mixed>
     */
    private array $response_info = [];

    /**
     * Send the prepared request
     * @return CurlResponseInterface Request response
     */
    protected function exec(): CurlResponseInterface
    {
        return parent::execute($this->curl);
    }

    /**
     * Send a GET request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function get(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareGet($url, $header);
        return $this->exec();
    }

    /**
     * Send a POST request to the URL
     * @param UriInterface $url URL
     * @param mixed $post_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function post(UriInterface $url, $post_elements, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->preparePost($url, $post_elements, $header);
        return $this->exec();
    }

    /**
     * Send a PUT request to the URL
     * @param UriInterface $url URL
     * @param mixed $put_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function put(UriInterface $url, $put_elements, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->preparePut($url, $put_elements, $header);
        return $this->exec();
    }

    /**
     * Send a PATCH request to the URL
     * @param UriInterface $url URL
     * @param mixed $patch_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function patch(UriInterface $url, $patch_elements, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->preparePut($url, $patch_elements, $header);
        return $this->exec();
    }

    /**
     * Send a DELETE request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function delete(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareDelete($url, $header);
        return $this->exec();
    }

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
     * Send an TRACE request to the URL
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
        //$result = ($this->return_transfer) ? explode("\n", (string) $result_execution) : $result_execution;
        $this->response_info = curl_getinfo($this->curl);
        return new CurlResponse($result_execution . "\r\n\r\n", $this->response_info);
    }

}