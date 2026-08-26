<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Factories;

use JuanchoSL\CurlClient\Engines\Email\CurlEmailRequest;
use JuanchoSL\CurlClient\Engines\Ftp\CurlFtpRequest;
use JuanchoSL\CurlClient\Engines\Http\CurlHttpRequest;
use JuanchoSL\CurlClient\Engines\Samba\CurlSmbRequest;
use JuanchoSL\CurlClient\Engines\Ssh\CurlSshRequest;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Exceptions\NetworkException;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\Headers;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CurlRequesterFactory
{

    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
    public function createFromRequest(RequestInterface $request): ResponseInterface
    {
        switch ((new StringsManipulators($request->getUri()->getScheme()))->toLower()->__tostring()) {
            case 'smtp':
            case 'smtps':
            case 'imap':
            case 'imaps':
            case 'pop3':
            case 'pop3s':
                return $this->createFromRequestEmail($request);

            case 'ftp':
            case 'ftps':
            case 'sftp':
                return $this->createFromRequestFtp($request);

            case 'smb':
            case 'smbs':
                return $this->createFromRequestSmb($request);

            case 'http':
            case 'https':
            default:
                return $this->createFromRequestHttp($request);
        }
    }

    /*
    public function createFromRequestSmb(RequestInterface $request): ResponseInterface
    {
        $result = (new CurlHandleFactory($this->options))->createFromRequest($request);
        $result = CurlSmbRequest::execute($result);
        $response = new ResponseFactory();
        $message = $response
            ->createResponse($result->getResponseCode(), Headers::getMessage((int) $result->getResponseCode()) ?? '')
            ->withBody((new StreamFactory())->createStream($result->getBody()));

        return $message;
    }
    */
    public function createFromRequestEmail(RequestInterface $request): ResponseInterface
    {
        $result = (new CurlHandleFactory($this->options))->createFromRequest($request);
        $result = CurlEmailRequest::execute($result);
        $response = new ResponseFactory();
        $message = $response
            ->createResponse($result->getResponseCode(), Headers::getMessage((int) $result->getResponseCode()) ?? '')
            ->withBody((new StreamFactory())->createStream($result->getBody()));

        return $message;
    }

    public function createFromRequestFtp(RequestInterface $request): ResponseInterface
    {
        $result = (new CurlHandleFactory($this->options))->createFromRequest($request);
        $result = (in_array(strtolower($request->getUri()->getScheme()), ['sftp', 'ssh'])) ? CurlSshRequest::execute($result) : CurlFtpRequest::execute($result);
        $response = new ResponseFactory();
        $message = $response
            ->createResponse($result->getResponseCode(), Headers::getMessage((int) $result->getResponseCode()) ?? '')
            ->withBody((new StreamFactory())->createStream($result->getBody()));
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

    public function createFromRequestHttp(RequestInterface $request): ResponseInterface
    {
        $result = (new CurlHandleFactory($this->options))->createFromRequest($request);
        $result = CurlHttpRequest::execute($result);
        if (empty($result) || $result->getResponseCode() == 0) {
            $exception = new NetworkException("The request to '{$request->getUri()->__tostring()}' failure");
            $exception->setRequest($request);
            throw $exception;
        }
        $response = new ResponseFactory();
        $message = $response
            ->createResponse($result->getResponseCode(), Headers::getMessage((int) $result->getResponseCode()) ?? '')
            ->withBody((new StreamFactory())->createStream($result->getBody()));
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