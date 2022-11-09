<?php

declare(strict_types=1);

namespace JuanchoSL\CurlClient;

class DataConverter
{

    /**
     * Create a new XML string with the array data on recursive mode
     * @param array $data Values to parse
     * @param \SimpleXMLElement $xml Child where we would append the result, null for root index
     * @return string XML string
     */
    public static function array2xml(array $data, \SimpleXMLElement $xml = null): string
    {
        if (is_null($xml)) {
            $xml = new \SimpleXMLElement('<root/>');
        }
        self::convertArray2xml($data, $xml);
        return $xml->asXML();
    }

    /**
     * PArse and read the array data values, if the value is a new array, call recursively
     * in order to convert it too
     * @param array $data Values to parse
     * @param \SimpleXMLElement $xml Child where we would append the result
     * @return void
     */
    private static function convertArray2xml(array $data, \SimpleXMLElement &$xml): void
    {
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
            }
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml->addChild("$key");
                    self::array2xml($value, $subnode);
                } else {
                    $subnode = $xml->addChild("item$key");
                    self::array2xml($value, $subnode);
                }
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * Read, parse and convert the array of elements for the desired type, can be:
     * - JSON
     * - XML
     * - URL or FORM (form url encoded body content)
     * @param array $elements Data to convert
     * @param string $type Type of conversion (JSON, XML, FORM)
     * @return string The conversion result
     */
    public static function bodyPrepare(array $elements, string $type): string
    {
        switch (strtolower($type)) {
            case 'json':
                $response = json_encode($elements);
                break;

            case 'xml':
                $response = self::array2xml($elements);
                break;

            case 'url':
            case 'form':
            default:
                $response = array();
                if (is_array($elements)) {
                    foreach ($elements as $name => $value) {
                        $response[] = $name . "=" . urlencode($value);
                    }
                    $response = join("&", $response);
                }
                break;
        }
        return $response;
    }

}
