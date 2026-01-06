<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Wrappers;

use JuanchoSL\CurlClient\BatchCurlRequests;
use JuanchoSL\CurlClient\Factories\CurlHandleFactory;
use JuanchoSL\CurlClient\Factories\CurlRequesterFactory;
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;
use JuanchoSL\HttpData\Bodies\Creators\UrlencodedCreator;
use JuanchoSL\HttpData\Factories\StreamFactory;
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
        $length = (($length = strpos($content_type, ';')) !== false) ? $length : null;
        switch (substr($content_type, 0, $length)) {
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

    public function sendBatch(RequestInterface ...$requests): iterable
    {
        $batch = new BatchCurlRequests();
        $factory = new CurlHandleFactory();
        foreach ($requests as $request) {
            $batch->addHandler($factory->createFromRequest($request));
        }
        return $results = $batch();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $message = (new CurlRequesterFactory())->createFromRequest($request);
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