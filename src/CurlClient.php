<?php

declare(strict_types=1);

namespace JuanchoSL\CurlClient;

/**
 * Perform cURL request to remote services as APIs
 */
class CurlClient
{

    private \CurlHandle $curl;
    private bool $ssl = false;
    private bool $follow_locations = false;
    private bool $return_transfer = true;
    private int $id;
    private string $cookie;
    private string $cookie_path = "/cookies";
    private array $response_info = [];
    private array $settings = [];
    private string $user_agent;
    private array $navigator = [
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36',
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0',
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:13.0)Gecko/20100101 Firefox/13.0',
        'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16',
        'Mozilla/5.0 (Linux; Android 4.4; Nexus 5 Build/_BuildID_) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 5.0; Nexus 5 Build/LPX13D) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.102 Mobile Safari/537.36',
        'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Win64; x64; Trident/6.0)'
    ];

    const HTTPCONNECTION_AGENT_LINUX = 0;
    const HTTPCONNECTION_AGENT_UBUNTU = 1;
    const HTTPCONNECTION_AGENT_IPHONE = 3;
    const HTTPCONNECTION_AGENT_ANDROID_4_4 = 4;
    const HTTPCONNECTION_AGENT_ANDROID_5 = 5;
    const HTTPCONNECTION_AGENT_IE10_WIN8 = 6;

    /**
     * Class constructor
     * @param array $settings Associative array including CURL_OPT_*** as key and his value
     */
    public function __construct(array $settings = [])
    {
        $this->setUserAgent(0);
        $this->settings = $settings;
        $this->id = time();
    }

    /**
     * Enable or disable SSL Strict mode
     * @param bool $ssl
     * @return void
     */
    public function setSsl(bool $ssl): void
    {
        if (is_bool($ssl)) {
            $this->ssl = $ssl;
        }
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
     * @return void
     */
    public function setFollowLocations(bool $follow_locations): void
    {
        if (is_bool($follow_locations)) {
            $this->follow_locations = $follow_locations;
        }
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
     * @return void
     */
    public function setReturnTransfer(bool $return_transfer): void
    {
        if (is_bool($return_transfer)) {
            $this->return_transfer = $return_transfer;
        }
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
     * Set the user agent for send with the request, can send a full string with the user agent
     * or use the constants for set a predeterminated user agent
     * @param string|int $agent
     * @return void
     */
    public function setUserAgent(string|int $agent): void
    {
        if (is_numeric($agent) && array_key_exists($agent, $this->navigator)) {
            $this->user_agent = $this->navigator[$agent];
        } elseif (is_string($agent) && strlen($agent) > 20) {
            $this->user_agent = $agent;
        }
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
     * Prepare the curl object with the default values and then the user settings
     * @param String $url URL of the reuqest
     * @param Array $header Extra headers for send in this request.
     * Indexed or associative arrays are valid values
     * @param String|Null $cookie Cookie path
     * @return void
     */
    private function init(string $url, array $header = [], $cookie = null): void
    {
        $this->cookie = ($cookie) ? $cookie : $this->cookie_path . $this->id;
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->getUserAgent());
        if (!empty($header)) {
            array_walk(
                    $header, function (&$value, $key) {
                        $value = (is_numeric($key)) ? $value : implode(': ', [$key, $value]);
                    }
            );
            $header = array_values($header);
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, $this->return_transfer);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        $urlArray = parse_url($url);
        if ($urlArray['scheme'] === 'https' || $this->ssl === true) {
            $caCert = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . 'cacert.pem';
            $strictSSL = (file_exists($caCert) && $this->ssl === true);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, ($strictSSL) ? 2 : 0);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($this->curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
            curl_setopt($this->curl, CURLOPT_CAINFO, ($strictSSL) ? $caCert : true);
        }
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, $this->follow_locations);

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
     * @return mixed The result of the request or the error message
     */
    protected function exec(): mixed
    {
        $result = curl_exec($this->curl);
        $this->response_info = curl_getinfo($this->curl);
        if ($result === false) {
            $result = curl_error($this->curl);
        }
        $this->close();
        return $result;
    }

    /**
     * Define the cookies saving place. The webserver user needs to have read and write privileges
     * @param string $path the cookies saving directory
     * @return void
     */
    public function setCookiePath($path): void
    {
        $this->cookie_path = $path;
    }

    /**
     * Send a GET request to the URL
     * @param string $url URL
     * @param Array $header Extra headers for send in this request
     * @return mixed Request response or error message
     */
    public function get(string $url, array $header = []): mixed
    {
        $this->init($url, $header);
        return $this->exec();
    }

    /**
     * Send a POST request to the URL
     * @param string $url URL
     * @param mixed $post_elements Fullformatted values to send into request
     * @param Array $header Extra headers for send in this request
     * @return mixed Request response or error message
     */
    public function post(string $url, $post_elements, array $header = []): mixed
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
     * @param Array $header Extra headers for send in this request
     * @return mixed Request response or error message
     */
    public function put(string $url, $put_elements, array $header = []): mixed
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
     * @param Array $header Extra headers for send in this request
     * @return mixed Request response or error message
     */
    public function patch(string $url, $patch_elements, array $header = []): mixed
    {
        $this->init($url, $header);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $patch_elements);
        return $this->exec();
    }

    /**
     * Send a DELETE request to the URL
     * @param string $url URL
     * @param Array $header Extra headers for send in this request
     * @return mixed Request response or error message
     */
    public function delete(string $url, array $header = []): mixed
    {
        $this->init($url, $header);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        return $this->exec();
    }

    /**
     * Send a HEAD request to the URL
     * @param string $url URL
     * @param Array $header Extra headers for send in this request
     * @return array Request response or error message
     */
    public function head(string $url, array $header = []): array
    {
        $this->init($url, $header);
        curl_setopt_array(
                $this->curl,
                array(
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true,
                )
        );
        $result = explode("\n", curl_exec($this->curl));
        $this->close();
        return $result;
    }

    /**
     * Retrieve a remote file on binary format
     * @param string $url Path of the remote file
     * @return string
     */
    public function getBinary($url)
    {
        $this->init($url);
        curl_setopt($this->curl, CURLOPT_BINARYTRANSFER, 1);
        return $this->exec();
    }

    private function _close(): void
    {
        curl_close($this->curl);
    }

    /**
     * Close the conection
     * @return void
     */
    public function close(): void
    {
        if (file_exists($this->cookie)) {
            unlink($this->cookie);
        }
        $this->_close();
    }

    /**
     * Retrieve the info from the last request performed
     * @param int $id The desired info name or null for all responses array
     * @return mixed The value of the selected index or the full array of data
     */
    public function getLastInfo($id = null): mixed
    {
        $info = $this->response_info;
        return (!empty($id)) ? $info[$id] : $info;
    }

}
