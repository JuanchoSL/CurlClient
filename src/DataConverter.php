<?php

declare(strict_types=1);

namespace JuanchoSL\CurlClient;

class DataConverter
{

    public static function array2xml(array $data, \SimpleXMLElement $xml = null): string
    {
        if (is_null($xml)) {
            $xml = new \SimpleXMLElement('<root/>');
        }
        self::convertArray2xml($data, $xml);
        return $xml->asXML();
    }

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

    public static function bodyPrepare(array $elements, string $type): string
    {
        switch ($type) {
            case 'json':
                $response = json_encode($elements);
                break;
            case 'xml':
                $response = self::array2xml($elements);
                break;
            default:
                $response = array();
                if (is_array($elements)) {
                    foreach ($elements as $name => $value) {
                        $response [] = $name . "=" . urlencode($value);
                    }
                    $response = join("&", $elements);
                }
                break;
        }
        return $response;
    }

}
