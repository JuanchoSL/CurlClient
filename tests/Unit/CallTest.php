<?php

namespace JuanchoSL\CurlClient\Tests\Unit;

use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\HttpHeaders\Constants\Types\Extensions;
use JuanchoSL\HttpHeaders\Constants\Types\MimeTypes;
use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{

    public function setUp(): void
    {
        defined('TMPDIR') or define('TMPDIR', sys_get_temp_dir());
    }

    /*
    public function testGetApiLyrics()
    {
        $curl = new CurlRequest();
        $response = $curl->setSsl(false)->setCookiePath(TMPDIR)->get('http://api.chartlyrics.com/apiv1.asmx/SearchLyric?artist=rihanna&song=umbrella');

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringContainsStringIgnoringCase("/" . Extensions::XML, $response->getContentType());

        $xml = simplexml_load_string($response->getBody(), "SimpleXMLElement", LIBXML_NOCDATA);
        $body = json_decode(json_encode($xml), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($body);
        $this->assertObjectHasProperty('SearchLyricResult', $body);
        $this->assertIsArray($body->SearchLyricResult);
        $this->assertNotEmpty($body->SearchLyricResult);
        $body = current($body->SearchLyricResult);
        $this->assertObjectHasProperty('Artist', $body);
        $this->assertStringContainsStringIgnoringCase('rihanna', $body->Artist);
        $this->assertObjectHasProperty('Song', $body);
        $this->assertStringContainsStringIgnoringCase('umbrella', $body->Song);
    }
    public function testGetApiBitcoinPrice()
    {
        $curl = new CurlRequest();
        $response = $curl->setSsl(true)->setCookiePath(TMPDIR)->get('https://api.coindesk.com/v1/bpi/currentprice.json');

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith(MimeTypes::JSON, $response->getContentType());

        $body = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($body);
        $this->assertObjectHasProperty('chartName', $body);
        $this->assertEqualsIgnoringCase('bitcoin', $body->chartName);
    }
    */
    public function testGetApiChickNorris()
    {
        $curl = new CurlRequest();
        $response = $curl->setSsl(true)->setCookiePath(TMPDIR)->get('https://api.chucknorris.io/jokes/random');

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith(MimeTypes::JSON, $response->getContentType());

        $body = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($body);
        $this->assertObjectHasProperty('value', $body);
        $this->assertNotEmpty($body->value);
    }

    public function testGetExchangeRatesApi()
    {
        $curl = new CurlRequest();
        $response = $curl->setSsl(true)->setCookiePath(TMPDIR)->get('https://api.coingecko.com/api/v3/exchange_rates');

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith(MimeTypes::JSON, $response->getContentType());

        $body = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('rates', $body);
        $this->assertIsArray($body['rates']);
        $this->assertArrayHasKey('eur', $body['rates']);
        $this->assertArrayHasKey('name', $body['rates']['eur']);
        $this->assertArrayHasKey('unit', $body['rates']['eur']);
        $this->assertArrayHasKey('value', $body['rates']['eur']);
        $this->assertArrayHasKey('type', $body['rates']['eur']);
        $this->assertEqualsIgnoringCase('fiat', $body['rates']['eur']['type']);
        $this->assertEqualsIgnoringCase('euro', $body['rates']['eur']['name']);
    }

    public function testGetRickAndMortyListApi()
    {
        $curl = new CurlRequest();
        $response = $curl->setSsl(true)->setCookiePath(TMPDIR)->get('https://rickandmortyapi.com/api/character');

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith(MimeTypes::JSON, $response->getContentType());

        $body = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('info', $body);
        $this->assertIsArray($body['info']);
        $this->assertArrayHasKey('results', $body);
        $this->assertIsArray($body['results']);
        $this->assertArrayHasKey('name', $body['results'][0]);
        $this->assertArrayHasKey('status', $body['results'][0]);
        $this->assertArrayHasKey('species', $body['results'][0]);
        $this->assertArrayHasKey('type', $body['results'][0]);
        $this->assertArrayHasKey('gender', $body['results'][0]);
    }
}