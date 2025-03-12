<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Wrappers;

use ArrayAccess;
use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\CurlClient\Exceptions\NetworkException;
use JuanchoSL\CurlClient\Exceptions\RequestException;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\Headers;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrCurlClient implements ClientInterface
{

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (!$request->getBody()->isSeekable()) {
            $exception = new RequestException("The sended body is not seekable");
            $exception->setRequest($request);
            throw $exception;
        }
        $headers = [];
        foreach ($request->getHeaders() as $header => $values) {
            $headers[$header] = $request->getHeaderLine($header);
        }
        $client = new CurlRequest();
        switch (strtoupper($request->getMethod())) {
            case RequestMethodInterface::METHOD_GET:
                $result = $client->get((string) $request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_POST:
                $result = $client->post((string) $request->getUri(), $request->getBody()->getContents(), $headers);
                break;
            case RequestMethodInterface::METHOD_PATCH:
                $result = $client->patch((string) $request->getUri(), $request->getBody()->getContents(), $headers);
                break;
            case RequestMethodInterface::METHOD_PUT:
                $result = $client->put((string) $request->getUri(), $request->getBody()->getContents(), $headers);
                break;
            case RequestMethodInterface::METHOD_DELETE:
                $result = $client->delete((string) $request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_HEAD:
                $result = $client->head((string) $request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_OPTIONS:
                $result = $client->options((string) $request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_TRACE:
                $result = $client->trace((string) $request->getUri(), $headers);
                break;
            default:
                $exception = new RequestException("The method '{$request->getMethod()}' is not supported");
                $exception->setRequest($request);
                throw $exception;
        }
        if (empty($result) || $result->getResponseCode() == 0) {
            $exception = new NetworkException("The request to '{$request->getUri()->__tostring()}' failure");
            $exception->setRequest($request);
            throw $exception;
        }
        $response = new ResponseFactory;
        $message = $response->createResponse($result->getResponseCode(), Headers::getMessage((int) $result->getResponseCode()))->withBody((new StreamFactory)->createStream($result->getBody()));
        foreach ($result->getHeaders() as $key => $value) {
            $key = str_replace('_', '-', $key);
            if (str_starts_with(strtoupper($key), 'HTTP-')) {
                $key = substr($key, 5);
            }
            if (is_numeric($value)) {
                $value = (string) $value;
            }
            $message = $message->withAddedHeader($key, $value);
        }

        return $message;
    }
}