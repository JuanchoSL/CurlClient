<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;

class RequestException extends ClientException implements RequestExceptionInterface
{

}