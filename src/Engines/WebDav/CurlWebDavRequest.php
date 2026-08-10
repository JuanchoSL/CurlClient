<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\WebDav;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\Contracts\Executions\BasicCurlMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Executions\ExtendedHttpMethodsInterface;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\BasicMethodsTrait;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\MoveMethodsTrait;
use Psr\Http\Message\UriInterface;

class CurlWebDavRequest extends CurlWebDavHandler implements BasicCurlMethodsInterface, ExtendedHttpMethodsInterface
{

    use BasicMethodsTrait, MoveMethodsTrait;
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

    public function mkcol(UriInterface $url, array $headers = []): CurlResponseInterface
    {
        $this->curl = $this->prepareMkcol($url, $headers);
        return $this->exec();
    }
    public function propfind(UriInterface $url, array $headers = []): CurlResponseInterface
    {
        $this->curl = $this->preparePropfind($url, $headers);
        return $this->exec();
    }
}