<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Factories;

use CurlHandle;
use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\Engines\Email\CurlEmailHandler;
use JuanchoSL\CurlClient\Engines\Ftp\CurlFtpHandler;
use JuanchoSL\CurlClient\Engines\Http\CurlHttpHandler;
use JuanchoSL\CurlClient\Engines\Samba\CurlSmbHandler;
use JuanchoSL\CurlClient\Engines\Ssh\CurlSshHandler;
use JuanchoSL\CurlClient\Engines\WebDav\CurlWebDavHandler;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Exceptions\RequestException;
use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\Validators\Types\Strings\StringValidation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class CurlHandleFactory
{

    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
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

            case 'smb':
            case 'smbs':
                return $this->createFromRequestSmb($request);

            case 'http':
            case 'https':
            default:
                return (in_array(strtoupper($request->getMethod()), ['MKCOL', 'PROPFIND', 'MOVE'])) ? $this->createFromRequestWebDav($request) : $this->createFromRequestHttp($request);
        }
    }

    public function createFromRequestEmail(RequestInterface $request): CurlHandle
    {
        if (!$request->getBody()->isSeekable()) {
            $exception = new RequestException("The sended body is not seekable");
            $exception->setRequest($request);
            throw $exception;
        }
        $client = new CurlEmailHandler($this->options);
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
        $request = $this->prepareRequestTargetIntoUri($request);
        $class = (in_array(strtolower($request->getUri()->getScheme()), ['sftp', 'ssh'])) ? CurlSshHandler::class : CurlFtpHandler::class;
        $client = new $class($this->options + [
            CURLOPT_HTTP_VERSION => $this->prepareProtocolVersion($request)
        ]);
        if (in_array(strtolower($request->getUri()->getScheme()), ['sftp', 'ftps'])) {
            $client = $client->setSsl(true, !$this->detectLookup($request->getUri()));
            if (in_array(strtolower($request->getUri()->getScheme()), ['ftps'])) {
                $request = $request->withUri($request->getUri()->withScheme('ftp'));
            }
        }
        if (in_array(strtolower($request->getUri()->getScheme()), ['ftp', 'ftps'])) {
            $client = $client->setPasive(true);
        }
        $headers = $this->prepareHeaders($request);
        switch (strtoupper($request->getMethod())) {
            case RequestMethodInterface::METHOD_GET:
                if (substr($request->getRequestTarget(), -1) == '/') {
                    $result = $client->prepareList($request->getUri(), $headers);
                } else {
                    $result = $client->prepareGet($request->getUri(), $headers);
                }
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
            case 'MOVE':
                $result = $client->prepareMove($request->getUri(), $headers);
                break;
            case 'LIST':
                $result = $client->prepareList($request->getUri(), $headers);
                break;
            case 'PROPFIND':
                $result = $client->prepareStat($request->getUri(), $headers);
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
        $headers = $this->prepareHeaders($request);
        $client = (new CurlHttpHandler($this->options + [
            CURLOPT_REQUEST_TARGET => $request->getRequestTarget(),
            CURLOPT_HTTP_VERSION => $this->prepareProtocolVersion($request)
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
                $result = $client->prepare(strtoupper($request->getMethod()), $request->getUri(), $headers, (string) $request->getBody());
                /*$exception = new RequestException("The method '{$request->getMethod()}' is not supported");
                $exception->setRequest($request);
                throw $exception;*/
                break;
        }
        return $result;
    }

    public function createFromRequestWebDav(RequestInterface $request): CurlHandle
    {
        $request = $this->prepareRequestTargetIntoUri($request);
        $headers = $this->prepareHeaders($request);
        $client = (new CurlWebDavHandler($this->options + [
            //CURLOPT_REQUEST_TARGET => $request->getRequestTarget(),
            CURLOPT_HTTP_VERSION => $this->prepareProtocolVersion($request)
        ]));
        if (in_array(strtolower($request->getUri()->getScheme()), ['https'])) {
            $client = $client->setSsl(true, !$this->detectLookup($request->getUri()));
        }
        switch (strtoupper($request->getMethod())) {
            case 'MOVE':
                $result = $client->prepareMove($request->getUri(), $headers);
                break;
            case 'PROPFIND':
                $result = $client->preparePropfind($request->getUri(), $headers);
                break;
            case 'MKCOL':
                $result = $client->prepareMkcol($request->getUri(), $headers);
                break;
            default:
                $result = $this->createFromRequestHttp($request);
                break;
        }
        return $result;
    }

    /*
    public function createFromRequestSmb(RequestInterface $request): CurlHandle
    {
        $request = $this->prepareRequestTargetIntoUri($request);
        $headers = $this->prepareHeaders($request);
        $client = (new CurlSmbHandler($this->options + [
            //CURLOPT_REQUEST_TARGET => $request->getRequestTarget(),
            //CURLOPT_HTTP_VERSION => $this->prepareProtocolVersion($request)
        ]));
        if (in_array(strtolower($request->getUri()->getScheme()), ['smbs'])) {
            $client = $client->setSsl(true, !$this->detectLookup($request->getUri()));
        }
        switch (strtoupper($request->getMethod())) {
            case RequestMethodInterface::METHOD_GET:
                if (substr($request->getRequestTarget(), -1) == '/') {
                    $result = $client->prepareGet($this->detectIp($request->getUri()), $headers);
                } else {
                    $result = $client->prepareGet($this->detectIp($request->getUri()), $headers);
                }
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
                $result = $client->prepareDelete($this->detectIp($request->getUri()), $headers);
                break;
            case 'MOVE':
                $result = $client->prepareMove($this->detectIp($request->getUri()), $headers);
                break;
            default:
                $result = $client->prepareHead($request->getUri(), $headers);
                break;
        }
        return $result;
    }
    */
    protected function prepareRequestTargetIntoUri(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();
        $uri = (new UriFactory)->createUri((string) (new StringsManipulators($uri->getAuthority()))->rtrim('/')->concatenation($request->getRequestTarget(), '/')->replace('//', '/')->preppend($uri->getScheme(), '://'));
        //$uri = (new UriFactory)->createUri((string) (new StringsManipulators($uri->getScheme())->concatenation($uri->getAuthority(), '://')->rtrim('/')->concatenation($request->getRequestTarget(), '/')));
        return $request->withUri($uri);
    }

    protected function prepareHeaders(RequestInterface $request): iterable
    {
        $headers = [];
        foreach ($request->getHeaders() as $header => $values) {
            $headers[$header] = $request->getHeaderLine($header);
        }
        return $headers;
    }

    protected function prepareProtocolVersion(RequestInterface $request)
    {
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
        return $protocol;
    }

    protected function detectIp(UriInterface $url)
    {
        if (!StringValidation::isIpV4($url->getHost())) {
            return $url->withHost(gethostbyname($url->getHost()));
        }
        return $url;
    }
    protected function detectLookup(UriInterface $url)
    {
        $host = $this->detectIp($url)->getHost();
        foreach (net_get_interfaces() as $interface) {
            if ($interface['up'] == 1) {
                foreach ($interface['unicast'] as $lan) {
                    if (array_key_exists('address', $lan) && $lan['address'] == $host) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}