<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\Http;

use CurlHandle;
use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\CurlClient\Contracts\Preparations\BasicCurlMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Preparations\SpecialServersMethodsInterface;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\Engines\Common\CurlHandler;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use Psr\Http\Message\UriInterface;

/**
 * Perform cURL request to remote services as APIs
 */
class CurlHttpHandler extends CurlHandler implements BasicCurlMethodsInterface, SpecialServersMethodsInterface
{

    private string $cookie;
    private string $cookie_path = "cookies";
    private string $user_agent;
    private bool $follow_locations = false;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->setCookiePath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->getCookiePath());
    }
    /**
     * Set the user agent for send with the request,  a full string with the user agent
     * @param string $agent
     * @return static
     */
    public function setUserAgent(string $agent): static
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
     * @return static
     */
    public function setCookiePath($path): static
    {
        $this->cookie_path = $path;
        if (!file_exists($this->cookie_path)) {
            mkdir($this->cookie_path, 0755, true);
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
     * Enable or disable the follow location directive
     * @param bool $follow_locations
     * @return static
     */
    public function setFollowLocations(bool $follow_locations): static
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
     * Prepare the curl object with the default values and then the user settings
     * @param UriInterface $url URL of the request
     * @param array<string,string> $header Extra headers for send in this request.
     * Indexed or associative arrays are valid values
     * @return CurlHandle Request response
     */
    protected function init(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = parent::init($url, $header);
        $this->cookie = $this->cookie_path . DIRECTORY_SEPARATOR . md5($url->getHost());
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->cookie = str_replace('\\', '/', $this->cookie);
        }

        if (!empty($url->getUserInfo())) {
            list($username, $password) = explode(':', $url->getUserInfo());
            curl_setopt($curl, CURLOPT_HTTPAUTH, \CURLAUTH_ANY | \CURLAUTH_ANYSAFE | \CURLAUTH_BASIC | \CURLAUTH_BEARER | \CURLAUTH_DIGEST | \CURLAUTH_GSSAPI | \CURLAUTH_GSSNEGOTIATE | \CURLAUTH_NEGOTIATE | \CURLAUTH_NTLM | \CURLAUTH_NTLM_WB | \CURLSSH_AUTH_AGENT | \CURLSSH_AUTH_GSSAPI);
            curl_setopt($curl, CURLOPT_USERNAME, $username);
            curl_setopt($curl, CURLOPT_PASSWORD, $password);
        }
        if (!empty($this->user_agent)) {
            curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->follow_locations ? 1 : 0);

        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie);
        return $this->setClientOptions($curl);
    }

    /**
     * Send a GET request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function prepareGet(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->init($url, $header);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        return $curl;
    }

    /**
     * Send a POST request to the URL
     * @param UriInterface $url URL
     * @param mixed $post_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function preparePost(UriInterface $url, string $post_elements, array $header = []): CurlHandle
    {
        return $curl = $this->prepare(RequestMethodInterface::METHOD_POST, $url, $header, $post_elements);
    }

    /**
     * Send a PUT request to the URL
     * @param UriInterface $url URL
     * @param mixed $put_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function preparePut(UriInterface $url, $put_elements, array $header = []): CurlHandle
    {
        return $curl = $this->prepare(RequestMethodInterface::METHOD_PUT, $url, $header, $put_elements);
    }

    /**
     * Send a PATCH request to the URL
     * @param UriInterface $url URL
     * @param mixed $patch_elements Fullformatted values to send into request
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function preparePatch(UriInterface $url, string $patch_elements, array $header = []): CurlHandle
    {
        return $curl = $this->prepare(RequestMethodInterface::METHOD_PATCH, $url, $header, $patch_elements);
    }

    /**
     * Send a DELETE request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function prepareDelete(UriInterface $url, array $header = []): CurlHandle
    {
        return $curl = $this->prepare(RequestMethodInterface::METHOD_DELETE, $url, $header);
    }

    /**
     * Send an OPTIONS request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function prepareOptions(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->prepare(RequestMethodInterface::METHOD_OPTIONS, $url, $header);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        return $curl;
    }

    /**
     * Send an TRACE request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function prepareTrace(UriInterface $url, array $header = []): CurlHandle
    {
        return $curl = $this->prepare(RequestMethodInterface::METHOD_TRACE, $url, $header);
    }

    /**
     * Send a HEAD request to the URL
     * @param UriInterface $url URL
     * @param array<string,string> $header Extra headers for send in this request
     * @return CurlHandle Request response
     */
    public function prepareHead(UriInterface $url, array $header = []): CurlHandle
    {
        $curl = $this->prepare(RequestMethodInterface::METHOD_HEAD, $url, $header);
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
            )
        );
        return $curl;
    }

    public function prepareConnect(UriInterface $url, array $header = []): CurlHandle
    {
        return $curl = $this->prepare(RequestMethodInterface::METHOD_CONNECT, $url, $header);
    }

    public function prepare(string $request, UriInterface $url, array $header = [], ?string $body = null): CurlHandle
    {
        $curl = $this->init($url, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($request));
        if (!empty($body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        return $curl;
    }

    public static function execute(CurlHandle $curl): CurlResponseInterface
    {
        $result = curl_exec($curl);
        $response_info = curl_getinfo($curl);
        if ($result === false) {
            $result = curl_error($curl);
        }
        $result = (string) (new StringsManipulators($result))->eol(PHP_EOL);
        return new CurlResponse($result, $response_info);
    }
}