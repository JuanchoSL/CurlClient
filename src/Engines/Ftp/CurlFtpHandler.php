<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Ftp;

use CurlHandle;
use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\Contracts\Preparations\BasicCurlMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Preparations\ListMethodsInterface;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\Engines\Common\CurlHandler;
use JuanchoSL\DataManipulation\Manipulators\Arrays\ArrayManipulators;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\Validators\Types\Strings\StringValidations;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote ftp servers
 */
class CurlFtpHandler extends CurlHandler implements BasicCurlMethodsInterface, ListMethodsInterface
{

    protected bool $pasive = true;
    protected string $active_port = '-';

    public function setPasive(bool $pasive): static
    {
        $this->pasive = $pasive;
        return $this;
    }
    public function getPasive(): bool
    {
        return $this->pasive;
    }
    public function setActivePort(string $port): static
    {
        $this->active_port = $port;
        return $this;
    }
    public function getActivePort(): string
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
    public function prepareStat(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        //curl_setopt($curl, CURLFTPMETHOD_SINGLECWD, true);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, false);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        return $curl;
    }

    public function prepareMove(UriInterface $url, array $header = []): CurlHandle
    {
        $man = (new ArrayManipulators())->keyToCase(CASE_LOWER);
        $header = $man($header);
        if (!array_key_exists('destination', $header)) {
            throw new PreconditionFailedException("The new pathname needs to be indicated into a 'Destination' header");
        }
        $curl = $this->init($url, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'MOVE');
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_NOBODY, true);

        curl_setopt($curl, CURLOPT_PREQUOTE, [
            sprintf("RNFR %s", $url->getPath()),
            sprintf("RNTO %s", $header['destination'])
        ]);
        curl_setopt($curl, CURLOPT_REQUEST_TARGET, $header['destination']);
        return $curl;
    }

    public function prepareList(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        //curl_setopt($curl, CURLFTPMETHOD_SINGLECWD, true);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, true);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        return $curl;
    }

    public function prepareHead(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, RequestMethodInterface::METHOD_HEAD);
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
            )
        );
        return $curl;
    }

    public function prepareGet(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        //$this->prepareWriterResource($curl);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, false);
        curl_setopt($curl, CURLOPT_UPLOAD, false);
        return $curl;
    }

    public function preparePatch(UriInterface $url, string $data, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        if (!empty($data)) {
            $this->prepareReaderResource($curl, $data);

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, RequestMethodInterface::METHOD_PATCH);
            curl_setopt($curl, CURLOPT_APPEND, true);
            curl_setopt($curl, CURLOPT_FTPAPPEND, true);
            curl_setopt($curl, CURLOPT_UPLOAD, 1);
        }
        return $curl;
    }

    public function preparePut(UriInterface $url, string $data, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        if (!empty($data)) {
            $this->prepareReaderResource($curl, $data);

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, RequestMethodInterface::METHOD_PUT);
            curl_setopt($curl, CURLOPT_APPEND, false);
            curl_setopt($curl, CURLOPT_FTPAPPEND, false);
            curl_setopt($curl, CURLOPT_UPLOAD, true);
        }
        return $curl;
    }

    public function preparePost(UriInterface $url, string $data, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
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

    public function prepareDelete(UriInterface $url, array $header = []): CurlHandle
    {
        $this->setReturnTransfer(false);
        $curl = $this->init($url, $header);
        curl_setopt($curl, CURLOPT_DIRLISTONLY, false);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, RequestMethodInterface::METHOD_DELETE);
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
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, RequestMethodInterface::METHOD_CONNECT);
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
        curl_setopt($curl, CURLOPT_FTP_SKIP_PASV_IP, true);
        if ($this->getSsl()) {
            //curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);//*
            //curl_setopt($curl, CURLOPT_TLSAUTH_TYPE, 'SRP');
            //curl_setopt($curl, CURLOPT_SSL_FALSESTART, false);
            curl_setopt($curl, CURLOPT_FTP_SSL, true);//*
            curl_setopt($curl, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_SSL);//CURLFTPAUTH_DEFAULT | CURLFTPAUTH_TLS | CURLFTPAUTH_SSL
            curl_setopt($curl, CURLOPT_FTP_SSL_CCC, CURLFTPSSL_CCC_NONE);
            if ((new StringValidations())->isValueContaining(':')->getResult($url->getUserInfo())) {
                list($username, $password) = (new StringsManipulators($url->getUserInfo()))->explode(':');
                curl_setopt($curl, CURLOPT_TLSAUTH_USERNAME, (string) $username->urlDecode());
                curl_setopt($curl, CURLOPT_TLSAUTH_PASSWORD, (string) $password->urlDecode());
            }
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

        if (false && isset($response_info['header_size']) && $response_info['header_size'] == 0) {
            if (isset($response_info['filetime']) && $response_info['filetime'] > 0) {
                $headers[] = "Last-Modified: " . date(DATE_RFC1123, $response_info['filetime']);
            }
            if (isset($response_info['size_download']) && $response_info['size_download'] > 0) {
                $headers[] = "Content-Length: " . $response_info['size_download'];
            }
            if (!empty($headers)) {
                $headers = implode(PHP_EOL, $headers);
            }
            $response_info['header_size'] = mb_strlen($headers);
        }
        if ($result === false) {
            $result = curl_error($curl);
            $response_info['size_download'] = mb_strlen($result);
        }
        return new CurlResponse($result, $response_info);
    }

}