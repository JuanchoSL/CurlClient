<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Ftp;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\Contracts\Executions\BasicCurlMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Executions\ListMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Executions\MoveHttpMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Executions\SpecialServersMethodsInterface;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\BasicMethodsTrait;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\ListMethodsTrait;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\MoveMethodsTrait;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\SpecialServersMethodsTrait;

class CurlFtpRequest extends CurlFtpHandler implements
    BasicCurlMethodsInterface,
    SpecialServersMethodsInterface,
    ListMethodsInterface,
    MoveHttpMethodsInterface
{

    use BasicMethodsTrait, SpecialServersMethodsTrait, ListMethodsTrait, MoveMethodsTrait;

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