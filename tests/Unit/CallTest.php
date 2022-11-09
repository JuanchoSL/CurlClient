<?php

namespace Tests\Unit;

use JuanchoSL\CurlClient\CurlClient;
use JuanchoSL\CurlClient\DataConverter;
use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{
    public function testGetApiLyrics()
    {
        $curl = new CurlClient();
        $curl->setSsl(false);
        $body = DataConverter::bodyPrepare(['artist' => 'rihanna', 'song' => 'umbrella'], 'url');
        $response = $curl->get('http://api.chartlyrics.com/apiv1.asmx/SearchLyric?' . $body);
        $code = $curl->getLastInfo('http_code');
        $xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
        $this->assertEquals(200, $code);
        $response = json_decode(json_encode($xml), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('SearchLyricResult', $response);
        $this->assertIsArray($response->SearchLyricResult);
        $this->assertNotEmpty($response->SearchLyricResult);
        $response = current($response->SearchLyricResult);
        $this->assertObjectHasAttribute('Artist', $response);
        $this->assertStringContainsStringIgnoringCase('rihanna', $response->Artist);
        $this->assertObjectHasAttribute('Song', $response);
        $this->assertStringContainsStringIgnoringCase('umbrella', $response->Song);
    }
    public function testGetApiBitcoinPrice()
    {
        $curl = new CurlClient();
        $curl->setSsl(true);
        $response = $curl->get('https://api.coindesk.com/v1/bpi/currentprice.json');
        $code = $curl->getLastInfo('http_code');
        $this->assertEquals(200, $code);
        $response = json_decode($response, false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('chartName', $response);
        $this->assertEqualsIgnoringCase('bitcoin', $response->chartName);
    }
    public function testGetExchangeRatesApi()
    {
        $curl = new CurlClient();
        $curl->setSsl(true);
        $response = $curl->get('https://api.coingecko.com/api/v3/exchange_rates');
        $code = $curl->getLastInfo('http_code');
        $this->assertEquals(200, $code);
        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('rates', $response);
        $this->assertIsArray($response['rates']);
        $this->assertArrayHasKey('eur', $response['rates']);
        $this->assertArrayHasKey('name', $response['rates']['eur']);
        $this->assertArrayHasKey('unit', $response['rates']['eur']);
        $this->assertArrayHasKey('value', $response['rates']['eur']);
        $this->assertArrayHasKey('type', $response['rates']['eur']);
        $this->assertEqualsIgnoringCase('fiat', $response['rates']['eur']['type']);
        $this->assertEqualsIgnoringCase('euro', $response['rates']['eur']['name']);
    }
}