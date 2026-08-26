<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient;

use JuanchoSL\CurlClient\Contracts\CurlResponseInterface;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
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

        if (is_string($body) && isset($this->last_info['header_size']) && $this->last_info['header_size'] > 0) {
            if (mb_substr_count($body, PHP_EOL . PHP_EOL) > 0) {
                //$body = (string)(new StringsManipulators(strval($body)))->eol(PHP_EOL);
                list($headers, $this->body) = explode(PHP_EOL . PHP_EOL, $body, 2);
            } else {
                $headers = trim(mb_substr($body, 0, $this->last_info['header_size']));
            }
        }
        if (empty($this->body) && isset($this->last_info['size_download']) && $this->last_info['size_download'] > 0) {
            $this->body = trim(mb_substr($body, (intval($this->last_info['size_download'])) * -1));
        }
        /*
        if (isset($this->last_info['header_size']) && $this->last_info['header_size'] > 0 && mb_substr_count($body, PHP_EOL . PHP_EOL) > 0) {
            $body = (new StringsManipulators($body))->eol(PHP_EOL)->__tostring();
            list($headers, $this->body) = explode(PHP_EOL . PHP_EOL, $body, 2);
        } else {
            if (isset($this->last_info['header_size']) && $this->last_info['header_size'] > 0) {
                $headers = trim(mb_substr($body, 0, $this->last_info['header_size']));
            }
            if (isset($this->last_info['size_download']) && $this->last_info['size_download'] > 0) {
                $this->body = trim(mb_substr($body, intval($this->last_info['size_download']) * -1));
            }
        }
        */
        //$headers = explode(PHP_EOL, $headers);
        $headers = (new StringsManipulators($headers))->eol(PHP_EOL)->explode(PHP_EOL);
        foreach ($headers as $value) {
            if (StringValidation::isValueContaining((string) $value, ':')) {
                $this->headers[(string) $value->substringBeforeChar(':')->trim()] = (string) $value->substringAfterChar(':')->trim();
            }
            /*
            if (($pos = strpos($value, ':')) !== false) {
                $name = substr($value, 0, $pos);
                $value = substr($value, $pos + 1);
                if (!empty($value)) {
                    $this->headers[trim($name)] = trim($value);
                }
            }
            */
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
