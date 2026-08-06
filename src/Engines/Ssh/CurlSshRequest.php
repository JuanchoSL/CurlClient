<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Ssh;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\Contracts\Executions\BasicCurlMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Executions\ListMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Executions\MoveHttpMethodsInterface;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\BasicMethodsTrait;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\ListMethodsTrait;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\MoveMethodsTrait;

/**
 * Perform cURL request to remote ftp servers
 */
class CurlSshRequest extends CurlSshHandler implements BasicCurlMethodsInterface, ListMethodsInterface, MoveHttpMethodsInterface
{
    use BasicMethodsTrait, ListMethodsTrait, MoveMethodsTrait;

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

}