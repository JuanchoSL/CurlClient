<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Common\Traits\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

trait ListMethodsTrait
{

    public function list(UriInterface $url, array $headers = []): CurlResponseInterface
    {
        $this->curl = $this->prepareList($url, $headers);
        return $this->exec();
    }

}