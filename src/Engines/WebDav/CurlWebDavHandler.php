<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Engines\WebDav;

use CurlHandle;
use JuanchoSL\CurlClient\Contracts\Preparations\BasicCurlMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Preparations\ExtendedHttpMethodsInterface;
use JuanchoSL\CurlClient\Contracts\Preparations\MoveMethodsInterface;
use Psr\Http\Message\UriInterface;
use JuanchoSL\CurlClient\Engines\Http\CurlHttpHandler;

class CurlWebDavHandler extends CurlHttpHandler implements BasicCurlMethodsInterface, ExtendedHttpMethodsInterface, MoveMethodsInterface
{

    public function preparePropfind(UriInterface $url, array $headers = []): CurlHandle
    {

        $curl = $this->init($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
        return $curl;
    }

    public function prepareMkcol(UriInterface $url, array $headers = []): CurlHandle
    {
        $curl = $this->init($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'MKCOL');
        return $curl;
    }

    public function prepareMove(UriInterface $url, array $headers = []): CurlHandle
    {
        $curl = $this->init($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'MOVE');
        return $curl;
    }

}