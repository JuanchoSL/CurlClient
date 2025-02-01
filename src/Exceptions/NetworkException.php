<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Exceptions;

use Psr\Http\Client\NetworkExceptionInterface;

class NetworkException extends ClientException implements NetworkExceptionInterface
{

}