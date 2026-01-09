<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Common;

use CurlHandle;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote services as APIs
 */
class CurlHandler
{
    protected CurlHandle $curl;

    private ?bool $ssl = null;
    private bool $cert_strict = false;
    private bool $return_transfer = true;
    protected int $connection_timeout = 60;


    /**
     *
     * @var array<int,string>
     */
    protected array $settings = [];

    /**
     * Class constructor
     * @param array<int,string> $settings Associative array including CURL_OPT_*** as key and his value
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Enable or disable SSL Strict mode
     * @param bool $ssl
     * @return static
     */
    public function setSsl(bool $ssl, ?bool $cert_strict = null): static
    {
        if (is_bool($ssl)) {
            $this->ssl = $ssl;
        }
        $cert_strict ??= $this->ssl;
        if (is_bool($cert_strict) && $ssl) {
            $this->cert_strict = $cert_strict;
        }
        return $this;
    }

    /**
     * Return if SSL Strict mode is enabled
     * @return bool
     */
    public function getSsl(): bool
    {
        return $this->ssl ?? false;
    }

    /**
     * Enable or disable the return transfer directive
     * @param bool $return_transfer
     * @return static
     */
    public function setReturnTransfer(bool $return_transfer): static
    {
        if (is_bool($return_transfer)) {
            $this->return_transfer = $return_transfer;
        }
        return $this;
    }

    /**
     * Return if return transfer directive is enabled or not
     * @return bool
     */
    public function getReturnTransfer(): bool
    {
        return $this->return_transfer;
    }

    public function setConnectionTimeoutSeconds(int $connection_timeout_seconds): static
    {
        $this->connection_timeout = $connection_timeout_seconds;
        return $this;
    }
    public function getConnectionTimeoutSeconds(): int
    {
        return $this->connection_timeout;
    }

    /**
     * Prepare the curl object with the default values and then the user settings
     * @param UriInterface $url URL of the request
     * @param array<string,string> $header Extra headers for send in this request.
     * Indexed or associative arrays are valid values
     * @return CurlHandle Request response
     */
    protected function init(UriInterface $url, array $header = []): CurlHandle
    {

        $curl = curl_init();
        /*
        curl_setopt($curl, CURLOPT_STDERR, STDOUT);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        */
        if (!empty($url->getPort())) {
            curl_setopt($curl, CURLOPT_PORT, $url->getPort());
        }
        if (is_null($this->ssl)) {
            if (in_array(strtolower($url->getScheme()), ['ftps', 'https', 'ssl', 'tls', 'pop3s', 'imaps', 'smtps'])) {
                $this->setSsl(true);
            }
        }
        $url = (string) $url;
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("The provided url is malformed");
        }
        curl_setopt($curl, CURLOPT_URL, $url);

        if (!empty($header)) {
            array_walk(
                $header,
                function (&$value, $key) {
                    $value = (is_numeric($key)) ? $value : implode(': ', [$key, $value]);
                }
            );
            $header = array_values($header);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, $this->return_transfer ? 1 : 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->getConnectionTimeoutSeconds());
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->getConnectionTimeoutSeconds());
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        if ($this->getSsl()) {
            $caCert = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . 'cacert.pem';
            $strictSSL = (file_exists($caCert) && $this->cert_strict);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, ($strictSSL) ? 2 : 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, ($strictSSL) ? 2 : 0);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_MAX_DEFAULT);
            curl_setopt($curl, CURLOPT_CAINFO, ($strictSSL) ? $caCert : true);
            curl_setopt($curl, CURLSSLOPT_AUTO_CLIENT_CERT, intval(!$this->cert_strict));
        }
        if (!empty($this->settings)) {
            foreach ($this->settings as $option => $setting) {
                curl_setopt($curl, $option, $setting);
            }
        }
        return $curl;
    }

    protected function setClientOptions(CurlHandle $curl)
    {
        if (!empty($this->settings)) {
            foreach ($this->settings as $option => $setting) {
                curl_setopt($curl, $option, $setting);
            }
        }
        return $curl;
    }

    protected function readerResource(CurlHandle $curl, $resource, int $buffer): string
    {
        return fread($resource, $buffer);
    }

    protected function prepareReaderResource(CurlHandle $curl, $data)
    {
        $path = tempnam(sys_get_temp_dir(), 'curl');
        file_put_contents($path, $data);
        $resource = fopen($path, 'rb');
        curl_setopt($curl, CURLOPT_READDATA, $resource);
        curl_setopt($curl, CURLOPT_READFUNCTION, [$this, 'readerResource']);
        return $curl;
    }
    protected function writerResource(CurlHandle $curl, string $data): int
    {
        $path = tempnam(sys_get_temp_dir(), 'save');
        $resource = fopen($path, 'a+');
        $but = fwrite($resource, $data);
        fclose($resource);
        return $but;
    }

    protected function prepareWriterResource(CurlHandle $curl)
    {
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, [$this, 'writerResource']);
        return $curl;
    }
}