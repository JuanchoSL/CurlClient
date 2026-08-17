<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Email;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\Contracts\Executions\BasicCurlMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Executions\ListMethodsInterface;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\BasicMethodsTrait;
use JuanchoSL\CurlClient\Engines\Common\Traits\Executions\ListMethodsTrait;

/**
 * Perform cURL request to remote ftp servers
 */
class CurlEmailRequest extends CurlEmailHandler implements BasicCurlMethodsInterface, ListMethodsInterface
{
    use BasicMethodsTrait, ListMethodsTrait;

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