<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Common\Traits\Executions;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use Psr\Http\Message\UriInterface;

trait MoveMethodsTrait
{

    public function move(UriInterface $url, array $headers = []): CurlResponseInterface
    {
        $this->curl = $this->prepareMove($url, $headers);
        return $this->exec();
    }

}