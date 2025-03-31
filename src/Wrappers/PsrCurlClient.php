<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Wrappers;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\HttpData\Exceptions\NetworkException;
use JuanchoSL\HttpData\Exceptions\RequestException;
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;
use JuanchoSL\HttpData\Bodies\Creators\UrlencodedCreator;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\Headers;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class PsrCurlClient implements ClientInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;

    public function sendRequestWithBody(RequestInterface $request, array $data): ResponseInterface
    {
        $content_type = strtolower($request->getHeaderLine('content-type'));
        switch (substr($content_type, 0, strpos($content_type, ';'))) {
            case 'application/json':
                $body = json_encode($data);
                break;
            case 'application/x-www-form-urlencoded':
                $body = (new UrlencodedCreator)->appendData($data);
                break;
            case 'multipart/form-data':
                $boundary = md5(uniqid());
                $body = (new MultipartCreator($boundary))->appendData($data);
                $request = $request->withHeader('content-type', "multipart/form-data; boundary={$boundary}");
                break;
        }
        return $this->sendRequest($request->withBody((new StreamFactory)->createStream((string) $body)));
    }

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
        $client = new CurlRequest([CURLOPT_REQUEST_TARGET => $request->getRequestTarget()]);
        switch (strtoupper($request->getMethod())) {
            case RequestMethodInterface::METHOD_GET:
                $result = $client->get((string) $request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_POST:
                $result = $client->post((string) $request->getUri(), (string) $request->getBody(), $headers);
                break;
            case RequestMethodInterface::METHOD_PATCH:
                $result = $client->patch((string) $request->getUri(), (string) $request->getBody(), $headers);
                break;
            case RequestMethodInterface::METHOD_PUT:
                $result = $client->put((string) $request->getUri(), (string) $request->getBody(), $headers);
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
        $this->logger?->info("{method} {path} {target} {code} {response}", [
            "method" => $request->getMethod(),
            "path" => (string) $request->getUri(),
            "target" => $request->getRequestTarget(),
            "code" => $message->getStatusCode(),
            "response" => $message->getReasonPhrase(),
        ]);
        return $message;
    }
}