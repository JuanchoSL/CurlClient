<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Common\Traits\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

trait BasicMethodsTrait
{

    public function get(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareGet($url, $header);
        return $this->exec();
    }

    public function post(UriInterface $url, $post_elements, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->preparePost($url, $post_elements, $header);
        return $this->exec();
    }

    public function put(UriInterface $url, $put_elements, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->preparePut($url, $put_elements, $header);
        return $this->exec();
    }

    public function patch(UriInterface $url, $patch_elements, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->preparePatch($url, $patch_elements, $header);
        return $this->exec();
    }

    public function delete(UriInterface $url, array $header = []): CurlResponseInterface
    {
        $this->curl = $this->prepareDelete($url, $header);
        return $this->exec();
    }

}
