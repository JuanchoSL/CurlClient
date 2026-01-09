<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Factories;

use CurlHandle;
use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\Engines\Email\CurlEmailHandler;
use JuanchoSL\CurlClient\Engines\Ftp\CurlFtpHandler;
use JuanchoSL\CurlClient\Engines\Http\CurlHttpHandler;
use JuanchoSL\CurlClient\Engines\Ssh\CurlSshHandler;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Exceptions\RequestException;
use JuanchoSL\Validators\Types\Strings\StringValidation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class CurlHandleFactory
{

    public function createFromRequest(RequestInterface $request): CurlHandle
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

            case 'http':
            case 'https':
            default:
                return $this->createFromRequestHttp($request);
        }
    }

    public function createFromRequestEmail(RequestInterface $request): CurlHandle
    {
        if (!$request->getBody()->isSeekable()) {
            $exception = new RequestException("The sended body is not seekable");
            $exception->setRequest($request);
            throw $exception;
        }
        $client = new CurlEmailHandler();
        if (in_array(strtolower($request->getUri()->getScheme()), ['smtps', 'pop3s', 'imaps'])) {
            $client = $client->setSsl(true, !$this->detectLookup($request->getUri()));
        }
        switch (strtoupper($request->getMethod())) {
            case RequestMethodInterface::METHOD_GET:
                $result = $client->prepareGet($request->getUri());
                break;

            case RequestMethodInterface::METHOD_DELETE:
                $result = $client->prepareDelete($request->getUri());
                break;

            case RequestMethodInterface::METHOD_POST:
                $result = $client->preparePost($request->getUri(), (string) $request->getBody());
                break;
        }
        return $result;
    }

    public function createFromRequestFtp(RequestInterface $request): CurlHandle
    {
        if (!$request->getBody()->isSeekable()) {
            $exception = new RequestException("The sended body is not seekable");
            $exception->setRequest($request);
            throw $exception;
        }
        $class = (in_array(strtolower($request->getUri()->getScheme()), ['sftp', 'ssh'])) ? CurlSshHandler::class : CurlFtpHandler::class;
        $client = (new $class([
            CURLOPT_REQUEST_TARGET => $request->getRequestTarget(),
        ]));

        if (in_array(strtolower($request->getUri()->getScheme()), ['sftp', 'ftps'])) {
            $client = $client->setSsl(true, !$this->detectLookup($request->getUri()));
            if (in_array(strtolower($request->getUri()->getScheme()), ['ftps'])) {
                $request = $request->withUri($request->getUri()->withScheme('ftp'));
            }
        }

        switch (strtoupper($request->getMethod())) {
            case RequestMethodInterface::METHOD_GET:
                if (substr($request->getRequestTarget(), -1) == '/') {
                    $result = $client->setPasive(true)->prepareList($request->getUri());
                } else {
                    $result = $client->setPasive(true)->prepareGet($request->getUri());
                }
                break;
            case RequestMethodInterface::METHOD_POST:
                $result = $client->setPasive(true)->preparePost($request->getUri(), (string) $request->getBody());
                break;
            case RequestMethodInterface::METHOD_PATCH:
                $result = $client->setPasive(true)->preparePatch($request->getUri(), (string) $request->getBody());
                break;
            case RequestMethodInterface::METHOD_PUT:
                $result = $client->setPasive(true)->preparePut($request->getUri(), (string) $request->getBody());
                break;
            case RequestMethodInterface::METHOD_DELETE:
                $result = $client->setPasive(false)->prepareDelete($request->getUri());
                break;
            case RequestMethodInterface::METHOD_HEAD:
                $result = $client->prepareHead($request->getUri());
                break;
            default:
                $exception = new RequestException("The method '{$request->getMethod()}' is not supported");
                $exception->setRequest($request);
                throw $exception;
        }
        return $result;
    }

    public function createFromRequestHttp(RequestInterface $request): CurlHandle
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
        switch ($request->getProtocolVersion()) {
            case "1.0":
                $protocol = CURL_HTTP_VERSION_1_0;
                break;
            default:
            case "1.1":
                $protocol = CURL_HTTP_VERSION_1_1;
                break;
            case "2":
                $protocol = CURL_HTTP_VERSION_2;
                break;
            case "2.0":
                $protocol = CURL_HTTP_VERSION_2_0;
                break;
            case "3":
                $protocol = CURL_HTTP_VERSION_3;
                break;
        }

        $client = (new CurlHttpHandler([
            CURLOPT_REQUEST_TARGET => $request->getRequestTarget(),
            CURLOPT_HTTP_VERSION => $protocol
        ]));
        if (in_array(strtolower($request->getUri()->getScheme()), ['https'])) {
            $client = $client->setSsl(true, !$this->detectLookup($request->getUri()));
        }
        switch (strtoupper($request->getMethod())) {
            case RequestMethodInterface::METHOD_GET:
                $result = $client->prepareGet($request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_POST:
                $result = $client->preparePost($request->getUri(), (string) $request->getBody(), $headers);
                break;
            case RequestMethodInterface::METHOD_PATCH:
                $result = $client->preparePatch($request->getUri(), (string) $request->getBody(), $headers);
                break;
            case RequestMethodInterface::METHOD_PUT:
                $result = $client->preparePut($request->getUri(), (string) $request->getBody(), $headers);
                break;
            case RequestMethodInterface::METHOD_DELETE:
                $result = $client->prepareDelete($request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_HEAD:
                $result = $client->prepareHead($request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_OPTIONS:
                $result = $client->prepareOptions($request->getUri(), $headers);
                break;
            case RequestMethodInterface::METHOD_TRACE:
                $result = $client->prepareTrace($request->getUri(), $headers);
                break;
            default:
                $exception = new RequestException("The method '{$request->getMethod()}' is not supported");
                $exception->setRequest($request);
                throw $exception;
        }
        return $result;
    }

    protected function detectLookup(UriInterface $url)
    {
        $host = $url->getHost();
        if (!StringValidation::isIpV4($url->getHost())) {
            $host = gethostbyname($url->getHost());
        }
        foreach (net_get_interfaces() as $interface) {
            if ($interface['up'] == 1) {
                foreach ($interface['unicast'] as $lan) {
                    if ($lan['address'] == $host) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}