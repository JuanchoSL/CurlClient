<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Email;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\Engines\Common\CurlHandler;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote ftp servers
 */
class CurlEmailHandler extends CurlHandler
{

    public function prepareList(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, true);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        return $curl;
    }

    public function prepareGet(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, false);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        return $curl;
    }
    public function preparePatch(UriInterface $url, string $data): CurlHandle
    {

        $curl = $this->init($url);
        curl_setopt($curl, \CURLOPT_CUSTOMREQUEST, 'PATCH');
        //curl_setopt($curl, CURLOPT_INFILE, $resource);
        //curl_setopt($curl, CURLOPT_INFILESIZE, filesize($path));
        //curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'resource']);
        //curl_setopt($curl, CURLOPT_WRITEFUNCTION, [$this, 'resource']);
        curl_setopt($curl, CURLOPT_FTPAPPEND, true);
        curl_setopt($curl, CURLOPT_UPLOAD, 1);
        $this->prepareReaderResource($curl, $data);
        return $curl;

        $path = tempnam(sys_get_temp_dir(), 'ftpup');
        file_put_contents($path, $data);
        $resource = fopen($path, 'rb');
        curl_setopt($curl, CURLOPT_READDATA, $resource);
        curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'readerResource']);
        return $curl;
    }
    public function preparePut(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_APPEND, false);
        curl_setopt($curl, CURLOPT_UPLOAD, true);
        $this->prepareReaderResource($curl, $data);
        return $curl;

        $path = tempnam(sys_get_temp_dir(), 'ftpup');
        file_put_contents($path, $data);
        $resource = fopen($path, 'rb');
        curl_setopt($curl, CURLOPT_READDATA, $resource);
        curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'readerResource']);
    }
    public function preparePost(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);

        curl_setopt($curl, CURLOPT_UPLOAD, true);
        curl_setopt($curl, CURLOPT_MAIL_AUTH, 'juan.sanchez@tecnicosweb.com');
        curl_setopt($curl, CURLOPT_MAIL_FROM, '<juan.sanchez@tecnicosweb.com>');
        curl_setopt($curl, CURLOPT_MAIL_RCPT, ['<webmaster@tecnicosweb.com>']);
        curl_setopt($curl, CURLOPT_MAIL_RCPT_ALLLOWFAILS, true);
        $this->prepareReaderResource($curl, $data);
        return $curl;

        $path = tempnam(sys_get_temp_dir(), 'sendmail');
        file_put_contents($path, $data);
        $resource = fopen($path, 'rb');
        curl_setopt($curl, CURLOPT_READDATA, $resource);
        curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'readerResource']);
    }
    public function prepareDelete(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url->withPath($url->getPath()));
        $this->setReturnTransfer(false);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELE');
        return $curl;
    }

    protected function init(UriInterface $url, $header = []): CurlHandle
    {
        $curl = parent::init($url, $header);
        if (true) {
            curl_setopt($curl, CURLOPT_FTP_USE_EPSV, true);
        }
        list($username, $password) = explode(':', $url->getUserInfo());
        if ($this->getSsl()) {
            curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);
            //curl_setopt($curl, CURLOPT_TLSAUTH_TYPE, 'SRP');
            curl_setopt($curl, CURLOPT_TLSAUTH_USERNAME, $username);
            curl_setopt($curl, CURLOPT_TLSAUTH_PASSWORD, $password);
            //curl_setopt($curl, CURLSSLOPT_AUTO_CLIENT_CERT, 1);
        } else {
            curl_setopt($curl, CURLOPT_LOGIN_OPTIONS, 'AUTH=*');//AUTH=NTLM o AUTH=*
            if (true) {
                curl_setopt($curl, CURLOPT_USERPWD, $url->getUserInfo());
            } else {
                curl_setopt($curl, CURLOPT_USERNAME, $username);
                curl_setopt($curl, CURLOPT_PASSWORD, $password);
            }
        }
        /*
                curl_setopt($curl, CURLOPT_IGNORE_CONTENT_LENGTH, true);
                curl_setopt($curl, CURLOPT_ACCEPTTIMEOUT_MS, $this->getConnectionTimeoutSeconds() * 1000);
                curl_setopt($curl, CURLOPT_SERVER_RESPONSE_TIMEOUT, $this->getConnectionTimeoutSeconds());
        */
        return $this->setClientOptions($curl);
    }

    public static function execute(CurlHandle $curl): CurlResponseInterface
    {
        $result = curl_exec($curl);
        $response_info = curl_getinfo($curl);
        $response_info['header_size'] = 1;
        if ($result === false) {
            $result = curl_error($curl);
        }
        return new CurlResponse(".\r\n\r\n" . $result, $response_info);
    }
}