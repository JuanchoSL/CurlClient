<?php

declare(strict_types=1);

namespace JuanchoSL\CurlClient;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\CurlResponse;

/**
 * Perform cURL request to remote services as APIs
 */
class CurlRequest
{

    private \CurlHandle $curl;
    private bool $ssl = false;
    private bool $follow_locations = false;
    private bool $return_transfer = true;
    private string $cookie;
    private string $cookie_path = "cookies";
    private string $user_agent;

    /**
     *
     * @var array<string,mixed>
     */
    private array $response_info = [];

    /**
     *
     * @var array<int,string>
     */
    private array $settings = [];

    /**
     * Class constructor
     * @param array<int,string> $settings Associative array including CURL_OPT_*** as key and his value
     */
    public function __construct(array $settings = [])
    {
        $this->setCookiePath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->cookie_path);
        $this->settings = $settings;
    }

    /**
     * Enable or disable SSL Strict mode
     * @param bool $ssl
     * @return CurlRequest
     */
    public function setSsl(bool $ssl): self
    {
        if (is_bool($ssl)) {
            $this->ssl = $ssl;
        }
        return $this;
    }

    /**
     * Return if SSL Strict mode is enabled
     * @return bool
     */
    public function getSsl(): bool
    {
        return $this->ssl;
    }

    /**
     * Enable or disable the follow location directive
     * @param bool $follow_locations
     * @return CurlRequest
     */
    public function setFollowLocations(bool $follow_locations): self
    {
        if (is_bool($follow_locations)) {
            $this->follow_locations = $follow_locations;
        }
        return $this;
    }

    /**
     * Return if follow location directive is enabled or not
     * @return bool
     */
    public function getFollowLocations(): bool
    {
        return $this->follow_locations;
    }

    /**
     * Enable or disable the return transfer directive
     * @param bool $return_transfer
     * @return CurlRequest
     */
    public function setReturnTransfer(bool $return_transfer): self
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

    /**
     * Set the user agent for send with the request,  a full string with the user agent
     * @param string $agent
     * @return CurlRequest
     */
    public function setUserAgent(string $agent): self
    {
        $this->user_agent = $agent;
        return $this;
    }

    /**
     * The user agent for use in the request
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->user_agent;
    }

    /**
     * Define the cookies saving place. The webserver user needs to have read and write privileges
     * @param string $path the cookies saving directory
     * @return CurlRequest
     */
    public function setCookiePath($path): self
    {
        $this->cookie_path = $path;
        if (!file_exists($this->cookie_path)) {
            mkdir($this->cookie_path, 0777, true);
        }
        return $this;
    }

    /**
     * Return the directory where cookies has
     * @return string The cookie path
     */
    public function getCookiePath(): string
    {
        return $this->cookie_path;
    }

    /**
     * Prepare the curl object with the default values and then the user settings
     * @param string $url URL of the request
     * @param array<string,string> $header Extra headers for send in this request.
     * Indexed or associative arrays are valid values
     * @return void
     */
    private function init(string $url, array $header = []): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("The provided url is malformed");
        }
        $host = (string) parse_url($url, PHP_URL_HOST);
        $this->cookie = $this->cookie_path . DIRECTORY_SEPARATOR . md5($host);
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->cookie = str_replace('\\', '/', $this->cookie);
        }
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $url);
        if (!empty($this->user_agent)) {
            curl_setopt($this->curl, CURLOPT_USERAGENT, $this->getUserAgent());
        }
        if (!empty($header)) {
            array_walk(
                $header,
                function (&$value, $key) {
                    $value = (is_numeric($key)) ? $value : implode(': ', [$key, $value]);
                }
            );
            $header = array_values($header);
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, $this->return_transfer ? 1 : 0);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        if (parse_url($url, PHP_URL_SCHEME) === 'https' || $this->ssl === true) {
            $caCert = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . 'cacert.pem';
            $strictSSL = (file_exists($caCert) && $this->ssl === true);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, ($strictSSL) ? 2 : 0);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($this->curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
            curl_setopt($this->curl, CURLOPT_CAINFO, ($strictSSL) ? $caCert : true);
        }
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, $this->follow_locations ? 1 : 0);

        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
        if (!empty($this->settings)) {
            foreach ($this->settings as $option => $setting) {
                curl_setopt($this->curl, $option, $setting);
            }
        }
    }

    /**
     * Send the prepared request
     * @return CurlResponseInterface Request response
     */
    protected function exec(): CurlResponseInterface
    {
        $result = curl_exec($this->curl);
        $this->response_info = curl_getinfo($this->curl);
        if ($result === false) {
            $result = curl_error($this->curl);
        }
        $this->close();
        return new CurlResponse($result, $this->response_info);
    }

    /**
     * Send a GET request to the URL
     * @param string $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function get(string $url, array $header = []): CurlResponseInterface
    {
        $this->init($url, $header);
        return $this->exec();
    }

    /**
     * Send a POST request to the URL
     * @param string $url URL
     * @param mixed $post_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function post(string $url, $post_elements, array $header = []): CurlResponseInterface
    {
        $this->init($url, $header);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_elements);
        return $this->exec();
    }

    /**
     * Send a PUT request to the URL
     * @param string $url URL
     * @param mixed $put_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function put(string $url, $put_elements, array $header = []): CurlResponseInterface
    {
        $this->init($url, $header);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $put_elements);
        return $this->exec();
    }

    /**
     * Send a PATCH request to the URL
     * @param string $url URL
     * @param mixed $patch_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function patch(string $url, $patch_elements, array $header = []): CurlResponseInterface
    {
        $this->init($url, $header);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $patch_elements);
        return $this->exec();
    }

    /**
     * Send a DELETE request to the URL
     * @param string $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function delete(string $url, array $header = []): CurlResponseInterface
    {
        $this->init($url, $header);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        return $this->exec();
    }

    /**
     * Send a HEAD request to the URL
     * @param string $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlResponseInterface Request response
     */
    public function head(string $url, array $header = []): CurlResponseInterface
    {
        $this->init($url, $header);
        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
            )
        );
        $result_execution = curl_exec($this->curl);
        $result = ($this->return_transfer) ? explode("\n", (string) $result_execution) : $result_execution;
        $this->response_info = curl_getinfo($this->curl);
        $this->close();
        return new CurlResponse($result, $this->response_info);
    }

    /**
     * Retrieve a remote file on binary format
     * @param string $url Path of the remote file
     * @return CurlResponseInterface Request response
     */
    public function getBinary($url): CurlResponseInterface
    {
        $this->init($url);
        curl_setopt($this->curl, CURLOPT_BINARYTRANSFER, 1);
        return $this->exec();
    }

    public function __destruct()
    {
        if (!empty($this->cookie) && file_exists($this->cookie)) {
            //unlink($this->cookie);
        }
        $this->close();
    }

    /**
     * Close the conection
     * @return void
     */
    public function close(): void
    {
        if (isset($this->curl)) {
            curl_close($this->curl);
        }
    }

}