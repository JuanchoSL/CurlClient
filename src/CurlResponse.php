<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Bodies\Parsers\ResponseReader;
use JuanchoSL\HttpData\Exceptions\NetworkException;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\Validators\Types\Strings\StringValidation;

/**
 * Group the cURL response data in order to use from other services
 */
class CurlResponse implements CurlResponseInterface
{

    /**
     *
     * @var array<string,mixed>
     */
    private array $last_info;
    private mixed $body = '';
    private array $headers = [];

    /**
     * Default constructor, set the responsed body and the info from the request
     * @param mixed $body The returned body from the request
     * @param array<string,mixed> $info The xtra info returned from the request
     */
    public function __construct(mixed $body, array $info)
    {
        $this->last_info = $info;
        $headers = '';
        if (array_key_exists('scheme', $info) && in_array(strtoupper($info['scheme']), ['HTTP', 'HTTPS'])) {
            if (array_key_exists('http_code', $info) && $info['http_code'] < 100) {
                throw new NetworkException($body, $info['http_code']);
            }
            $parsed = new ResponseReader((new StreamFactory())->createStream($body));
            $this->body = (string) $parsed->getBodyStream();
            $this->headers = $parsed->getHeadersParams();
        } else {
            if (is_string($body)) {
                if (isset($this->last_info['header_size']) && $this->last_info['header_size'] > 0) {
                    if (mb_strlen($body) > $this->last_info['header_size']) {
                        $headers = mb_substr($body, 0, $this->last_info['header_size']);
                        $body = mb_substr($body, $this->last_info['header_size']);
                    }
                } else {
                    if (mb_substr_count($body, PHP_EOL . PHP_EOL) > 0) {
                        list($headers, $this->body) = explode(PHP_EOL . PHP_EOL, $body, 2);
                    } else {
                        $headers = trim(mb_substr($body, 0, $this->last_info['header_size']));
                    }
                }
            }
            if (empty($this->body) && !empty($body) && is_string($body)) {
                if (isset($this->last_info['size_download']) && $this->last_info['size_download'] > 0) {
                    $this->body = trim(mb_substr($body, (intval($this->last_info['size_download'])) * -1));
                } elseif (isset($this->last_info['download_content_length']) && $this->last_info['download_content_length'] > 0) {
                    $this->body = trim(mb_substr($body, (intval($this->last_info['download_content_length'])) * -1));
                } else {
                    $this->body = $body;
                }
            }
            $headers = (new StringsManipulators($headers))->eol(PHP_EOL)->explode(PHP_EOL);
            foreach ($headers as $value) {
                if (StringValidation::isValueContaining((string) $value, ':')) {
                    $this->headers[(string) $value->substringBeforeChar(':')->trim()] = (string) $value->substringAfterChar(':')->trim();
                }
            }
        }
    }

    /**
     * Return the HTTP RESPONSE CODE from the request result
     * @return int the http code response
     */
    public function getResponseCode(): int
    {
        return $this->last_info['http_code'];
    }

    /**
     * Return the CONTENT-TYPE HEADER from the request result
     * @return string The content-type header value
     */
    public function getContentType(): string
    {
        return $this->last_info['content_type'];
    }

    /**
     * Return the body content from the request result
     * @return mixed The request response body
     */
    public function getBody(): mixed
    {
        return (empty($this->body)) ? $this->body : (string) (new StringsManipulators(strval($this->body)))->eol(PHP_EOL)->trim(PHP_EOL);
        return $this->body;
    }

    /**
     * Retrieve ALL available info
     * @return array<string,string>
     */
    public function getAllInfo(): array
    {
        return $this->last_info;
    }

    /**
     * Returns the response headers as array
     * @return array The response headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

}
