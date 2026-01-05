<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Ftp;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\Engines\Common\CurlHandler;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote ftp servers
 */
class CurlFtpHandler extends CurlHandler
{

    protected bool $pasive = false;
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
    public function prepareList(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, true);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        return $curl;
    }

    public function prepareModifiedTime(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_FILETIME, true);
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
        $path = tempnam(sys_get_temp_dir(), 'ftpup');
        file_put_contents($path, $data);
        $resource = fopen($path, 'rb');

        $curl = $this->init($url);
        curl_setopt($curl, \CURLOPT_CUSTOMREQUEST, 'PATCH');
        //curl_setopt($curl, CURLOPT_INFILE, $resource);
        //curl_setopt($curl, CURLOPT_INFILESIZE, filesize($path));
        curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'resource']);
        curl_setopt($curl, CURLOPT_READDATA, $resource);
        //curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'resource']);
        //curl_setopt($curl, CURLOPT_WRITEFUNCTION, [$this, 'resource']);
        curl_setopt($curl, CURLOPT_FTPAPPEND, true);
        curl_setopt($curl, CURLOPT_UPLOAD, 1);
        return $curl;
    }

    public function preparePut(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        $path = tempnam(sys_get_temp_dir(), 'ftpup');
        file_put_contents($path, $data);
        $resource = fopen($path, 'rb');
        curl_setopt($curl, CURLOPT_READDATA, $resource);
        curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'resource']);
        curl_setopt($curl, CURLOPT_APPEND, false);
        curl_setopt($curl, CURLOPT_UPLOAD, true);
        return $curl;
    }

    public function preparePost(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        $path = tempnam(sys_get_temp_dir(), 'ftpup');
        file_put_contents($path, $data);
        $resource = fopen($path, 'rb');
        curl_setopt($curl, CURLOPT_READDATA, $resource);
        curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'resource']);
        curl_setopt($curl, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
        curl_setopt($curl, CURLOPT_APPEND, false);
        curl_setopt($curl, CURLOPT_UPLOAD, true);
        return $curl;
    }

    public function prepareDelete(UriInterface $url): CurlHandle
    {
        $curl = $this->init($url->withPath(dirname($url->getPath())));
        $this->setReturnTransfer(false);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl, CURLOPT_QUOTE, array(sprintf("DELE %s", $url->getPath())));
        return $curl;
    }

    protected function init(UriInterface $url, $header = []): CurlHandle
    {
        $curl = parent::init($url, $header);

        //curl_setopt($curl, CURLOPT_PROTOCOLS_STR, 'ftp,ftps');
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
            /*
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLSSLOPT_AUTO_CLIENT_CERT, 1);
            */
        } else {
            curl_setopt($curl, CURLOPT_LOGIN_OPTIONS, 'AUTH=*');//AUTH=NTLM o AUTH=*
            if (true) {
                curl_setopt($curl, CURLOPT_USERPWD, $url->getUserInfo());
            } else {
                curl_setopt($curl, CURLOPT_USERNAME, $username);
                curl_setopt($curl, CURLOPT_PASSWORD, $password);
            }
        }

        curl_setopt($curl, CURLOPT_IGNORE_CONTENT_LENGTH, true);
        curl_setopt($curl, CURLOPT_ACCEPTTIMEOUT_MS, $this->getConnectionTimeoutSeconds() * 1000);

        $opt_timeout = (version_compare(PHP_VERSION, '8.4.0', '<')) ? CURLOPT_FTP_RESPONSE_TIMEOUT : CURLOPT_SERVER_RESPONSE_TIMEOUT;
        curl_setopt($curl, $opt_timeout, $this->getConnectionTimeoutSeconds());

        return $curl;
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

    protected function resource(CurlHandle $curl, $resource, int $buffer): string
    {
        return fread($resource, $buffer);
    }
}