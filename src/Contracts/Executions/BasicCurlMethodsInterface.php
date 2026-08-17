<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

interface BasicCurlMethodsInterface
{

    /**
     * Send a GET request to the url
     * @param UriInterface $url URL
     * @param array $header Extra headers for send into request
     * @return CurlResponseInterface Request response
     */
    public function get(UriInterface $url, array $header = []): CurlResponseInterface;

    /**
     * Send a POST request to the URL
     * @param UriInterface $url URL
     * @param mixed $post_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function post(UriInterface $url, $post_elements, array $header = []): CurlResponseInterface;

    /**
     * Send a PUT request to the URL
     * @param UriInterface $url URL
     * @param mixed $put_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function put(UriInterface $url, $put_elements, array $header = []): CurlResponseInterface;

    /**
     * Send a PATCH request to the URL
     * @param UriInterface $url URL
     * @param mixed $patch_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function patch(UriInterface $url, $patch_elements, array $header = []): CurlResponseInterface;

    /**
     * Send a DELETE request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function delete(UriInterface $url, array $header = []): CurlResponseInterface;


}