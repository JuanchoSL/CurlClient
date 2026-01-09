<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Email;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\Engines\Common\CurlHandler;
use JuanchoSL\DataManipulation\Manipulators\Arrays\ArrayManipulators;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Bodies\Parsers\MessageReader;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\Validators\Types\Strings\StringValidations;
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
        curl_setopt($curl, CURLOPT_FTPAPPEND, true);
        curl_setopt($curl, CURLOPT_UPLOAD, 1);
        $this->prepareReaderResource($curl, $data);
        return $curl;
    }
    public function preparePut(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        curl_setopt($curl, CURLOPT_APPEND, false);
        curl_setopt($curl, CURLOPT_UPLOAD, true);
        $this->prepareReaderResource($curl, $data);
        return $curl;
    }
    public function preparePost(UriInterface $url, string $data): CurlHandle
    {
        $curl = $this->init($url);
        $msg = new MessageReader((new StreamFactory())->createStream($data));
        $headers = (new ArrayManipulators())->keyToCase()->__invoke($msg->getHeadersParams());
        $from = (new StringsManipulators($headers['from']))->trim()->substring(strpos($headers['from'], '<'))->trim('<>');
        curl_setopt($curl, CURLOPT_UPLOAD, true);
        curl_setopt($curl, CURLOPT_MAIL_AUTH, (string) $from);
        curl_setopt($curl, CURLOPT_MAIL_FROM, (string) $from->preppend('<', '')->concatenation('>', ''));
        if (!is_array($headers['to'])) {
            $headers['to'] = explode(',', $headers['to']);
        }
        foreach ($headers['to'] as $key => $to) {
            if (!(new StringValidations())->isValueStartingWith('<')->isValueEndingWith('>')->getResult($to)) {
                $headers['to'][$key] = "<{$to}>";
            }
        }
        curl_setopt($curl, CURLOPT_MAIL_RCPT, $headers['to']);
        curl_setopt($curl, CURLOPT_MAIL_RCPT_ALLLOWFAILS, true);
        $this->prepareReaderResource($curl, $data);
        return $curl;
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
        if ($this->getSsl()) {
            curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);
            //curl_setopt($curl, CURLOPT_TLSAUTH_TYPE, 'SRP');
            if ((new StringValidations())->isValueContaining(':')->getResult($url->getUserInfo())) {
                list($username, $password) = (new StringsManipulators($url->getUserInfo()))->explode(':');
                curl_setopt($curl, CURLOPT_TLSAUTH_USERNAME, (string) $username->urlDecode());
                curl_setopt($curl, CURLOPT_TLSAUTH_PASSWORD, (string) $password->urlDecode());
            }
            //curl_setopt($curl, CURLSSLOPT_AUTO_CLIENT_CERT, 1);
        } else {
            curl_setopt($curl, CURLOPT_LOGIN_OPTIONS, 'AUTH=*');//AUTH=NTLM o AUTH=*
            if (true) {
                curl_setopt($curl, CURLOPT_USERPWD, $url->getUserInfo());
            } else {
                list($username, $password) = explode(':', $url->getUserInfo());
                curl_setopt($curl, CURLOPT_USERNAME, $username);
                curl_setopt($curl, CURLOPT_PASSWORD, $password);
            }
        }
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