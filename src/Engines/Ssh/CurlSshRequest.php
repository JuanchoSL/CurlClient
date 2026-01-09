<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Ssh;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote ftp servers
 */
class CurlSshRequest extends CurlSshHandler
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

    public function list(UriInterface $url): CurlResponseInterface
    {
        $this->curl = $this->prepareList($url);
        return $this->exec();
    }

    public function get(UriInterface $url): CurlResponseInterface
    {
        $this->curl = $this->prepareGet($url);
        return $this->exec();
    }
    public function patch(UriInterface $url, string $data): CurlResponseInterface
    {
        $this->curl = $this->preparePatch($url, $data);
        return $this->exec();
    }
    public function put(UriInterface $url, string $data): CurlResponseInterface
    {
        $this->curl = $this->preparePut($url, $data);
        return $this->exec();
    }
    public function post(UriInterface $url, string $data): CurlResponseInterface
    {
        $this->curl = $this->preparePost($url, $data);
        return $this->exec();
    }
    public function delete(UriInterface $url): CurlResponseInterface
    {
        $this->curl = $this->prepareDelete($url);
        return $this->exec();
    }
}