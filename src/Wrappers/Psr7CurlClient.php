<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Wrappers;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\Headers;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Psr7CurlClient
{
    public function execute(RequestInterface $request): ResponseInterface
    {
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
        }
        if (!empty($result)) {
            $response = new ResponseFactory;
            $message = $response->createResponse($result->getResponseCode(), Headers::getMessage((int) $result->getResponseCode()))->withBody((new StreamFactory)->createStream($result->getBody()));
            foreach ($result->getAllInfo() as $key => $value) {
                $key = str_replace('_', '-', $key);
                if (str_starts_with(strtoupper($key), 'HTTP-')) {
                    $key = substr($key, 5);
                }
                $message = $message->withAddedHeader($key, $value);
            }
            return $message;
        }
        throw new DestinationUnreachableException("The request to {$request->getUri()->__tostring()} failure");
    }
}