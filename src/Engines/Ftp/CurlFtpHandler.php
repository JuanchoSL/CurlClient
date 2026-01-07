<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Ftp;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\Engines\Common\CurlHandler;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote ftp servers
 */
class CurlFtpHandler extends CurlHandler
{

    protected bool $pasive = true;
    protected int $active_port = 20;

    public function setPasive(bool $pasive): static
    {
        $this->pasive = $pasive;
        return $this;
    }
    public function getPasive(): bool
    {
        return $this->pasive;
    }
    public function setActivePort(int $port): static
    {
        $this->active_port = $port;
        return $this;
    }
    public function getActivePort(): int
    {
        return $this->active_port;
    }

    /*
    //SFTP
    public function prepareChmod(UriInterface $url, int $perms): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_NEW_DIRECTORY_PERMS, $perms);
        curl_setopt($curl, CURLOPT_NEW_FILE_PERMS, $perms);
        return $curl;
    }
        */
    public function prepareStat(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        //curl_setopt($curl, CURLFTPMETHOD_SINGLECWD, true);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, false);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        return $curl;
    }

    public function prepareList(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        //curl_setopt($curl, CURLFTPMETHOD_SINGLECWD, true);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, true);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        return $curl;
    }

    public function prepareHead(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
            )
        );
        return $curl;
    }

    public function prepareGet(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, false);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        return $curl;
    }

    public function preparePatch(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        if (!empty($data)) {
            $this->prepareReaderResource($curl, $data);

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_APPEND, true);
            curl_setopt($curl, CURLOPT_FTPAPPEND, true);
            curl_setopt($curl, CURLOPT_UPLOAD, 1);
        }
        return $curl;
    }

    public function preparePut(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        if (!empty($data)) {
            $this->prepareReaderResource($curl, $data);

            curl_setopt($curl, CURLOPT_APPEND, false);
            curl_setopt($curl, CURLOPT_FTPAPPEND, false);
            curl_setopt($curl, CURLOPT_UPLOAD, true);
        }
        return $curl;
    }

    public function preparePost(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        if (empty($data)) {
            curl_setopt($curl, CURLOPT_QUOTE, array(sprintf("MKD %s", $url->getPath())));
        } else {
            $this->prepareReaderResource($curl, $data);

            curl_setopt($curl, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
            curl_setopt($curl, CURLOPT_APPEND, false);
            curl_setopt($curl, CURLOPT_UPLOAD, true);
        }
        return $curl;
    }

    public function prepareDelete(UriInterface $url): CurlHandle
    {
        $this->setReturnTransfer(false);
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, false);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if (substr($url->getPath(), -1) == '/') {
            curl_setopt($curl, CURLOPT_POSTQUOTE, array(sprintf("RMD %s", $url->getPath())));
        } else {
            curl_setopt($curl, CURLOPT_PREQUOTE, array(sprintf("DELE %s", $url->getPath())));
        }
        return $curl;
    }


    public function prepareConnect(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'CONNECT');
        return $curl;
    }

    protected function init(UriInterface $url, $header = []): CurlHandle
    {
        $curl = parent::init($url, $header);
        if ($this->getPasive()) {
            curl_setopt($curl, CURLOPT_FTP_USE_EPSV, true);
        } else {
            curl_setopt($curl, CURLOPT_FTPPORT, $this->getActivePort());
            curl_setopt($curl, CURLOPT_FTP_USE_EPRT, true);
        }

        list($username, $password) = explode(':', $url->getUserInfo());
        if ($this->getSsl()) {
            //curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);//*
            //curl_setopt($curl, CURLOPT_TLSAUTH_TYPE, 'SRP');
            //curl_setopt($curl, CURLOPT_SSL_FALSESTART, false);
            curl_setopt($curl, CURLOPT_FTP_SSL, true);//*
            curl_setopt($curl, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_SSL);//CURLFTPAUTH_DEFAULT | CURLFTPAUTH_TLS | CURLFTPAUTH_SSL
            curl_setopt($curl, CURLOPT_FTP_SSL_CCC, CURLFTPSSL_CCC_NONE);
            curl_setopt($curl, CURLOPT_TLSAUTH_USERNAME, $username);
            curl_setopt($curl, CURLOPT_TLSAUTH_PASSWORD, $password);
        } else {
            curl_setopt($curl, CURLOPT_LOGIN_OPTIONS, 'AUTH=*');//AUTH=NTLM o AUTH=*
            curl_setopt($curl, CURLOPT_USERPWD, $url->getUserInfo());
        }

        curl_setopt($curl, CURLOPT_IGNORE_CONTENT_LENGTH, true);
        curl_setopt($curl, CURLOPT_ACCEPTTIMEOUT_MS, $this->getConnectionTimeoutSeconds() * 1000);

        $opt_timeout = (version_compare(PHP_VERSION, '8.4.0', '<')) ? CURLOPT_FTP_RESPONSE_TIMEOUT : CURLOPT_SERVER_RESPONSE_TIMEOUT;
        curl_setopt($curl, $opt_timeout, $this->getConnectionTimeoutSeconds());
        return $this->setClientOptions($curl);
    }

    public static function execute(CurlHandle $curl): CurlResponseInterface
    {
        $result = curl_exec($curl);
        $response_info = curl_getinfo($curl);
        $headers = [];
        if (isset($response_info['filetime']) && $response_info['filetime'] > 0) {
            $headers[] = "Last-Modified: " . date(DATE_RFC1123, $response_info['filetime']);
        }
        if (isset($response_info['size_download']) && $response_info['size_download'] > 0) {
            $headers[] = "Content-Length: " . $response_info['size_download'];
        }
        $headers = (empty($headers)) ? '.' : implode(PHP_EOL, $headers);
        $response_info['header_size'] = mb_strlen($headers);
        if ($result === false) {
            $result = curl_error($curl);
        }
        return new CurlResponse($headers . PHP_EOL . PHP_EOL . $result, $response_info);
    }

}