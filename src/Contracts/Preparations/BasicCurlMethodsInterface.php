<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts\Preparations;

use CurlHandle;
use Psr\Http\Message\UriInterface;

interface BasicCurlMethodsInterface
{

    /**
     * prepare a GET curlhandle
     * @param UriInterface $url URL
     * @param array $header Extra headers for prepare into request
     * @return CurlHandle Request response
     */
    public function prepareGet(UriInterface $url, array $header = []): CurlHandle;

    /**
     * prepare a POST curlhandle
     * @param UriInterface $url URL
     * @param mixed $post_elements Fullformatted values to prepare into request
     * @param array<string,string> $header Extra headers for prepare in this request
     * @return CurlHandle Request response
     */
    public function preparePost(UriInterface $url, string $post_elements, array $header = []): CurlHandle;

    /**
     * prepare a PUT curlhandle
     * @param UriInterface $url URL
     * @param mixed $put_elements Fullformatted values to prepare into request
     * @param array<string,string> $header Extra headers for prepare in this request
     * @return CurlHandle Request response
     */
    public function preparePut(UriInterface $url, string $put_elements, array $header = []): CurlHandle;

    /**
     * prepare a PATCH curlhandle
     * @param UriInterface $url URL
     * @param mixed $patch_elements Fullformatted values to prepare into request
     * @param array<string,string> $header Extra headers for prepare in this request
     * @return CurlHandle Request response
     */
    public function preparePatch(UriInterface $url, string $patch_elements, array $header = []): CurlHandle;

    /**
     * prepare a DELETE curlhandle
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for prepare in this request
     * @return CurlHandle Request response
     */
    public function prepareDelete(UriInterface $url, array $header = []): CurlHandle;


}