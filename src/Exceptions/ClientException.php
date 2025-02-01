<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

class ClientException extends \Exception implements ClientExceptionInterface
{
    protected RequestInterface $request;

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}