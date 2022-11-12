<?php

namespace Tests\Unit;

use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\DataConverter;
use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{

    public function testGetApiLyrics()
    {
        $curl = new CurlRequest();
        $curl->setSsl(false);
        $body = DataConverter::bodyPrepare(['artist' => 'rihanna', 'song' => 'umbrella'], 'url');
        $response = $curl->get('http://api.chartlyrics.com/apiv1.asmx/SearchLyric?' . $body);

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith('text/xml', $response->getContentType());

        $xml = simplexml_load_string($response->getBody(), "SimpleXMLElement", LIBXML_NOCDATA);
        $body = json_decode(json_encode($xml), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($body);
        $this->assertObjectHasAttribute('SearchLyricResult', $body);
        $this->assertIsArray($body->SearchLyricResult);
        $this->assertNotEmpty($body->SearchLyricResult);
        $body = current($body->SearchLyricResult);
        $this->assertObjectHasAttribute('Artist', $body);
        $this->assertStringContainsStringIgnoringCase('rihanna', $body->Artist);
        $this->assertObjectHasAttribute('Song', $body);
        $this->assertStringContainsStringIgnoringCase('umbrella', $body->Song);
    }

    public function testGetApiBitcoinPrice()
    {
        $curl = new CurlRequest();
        $curl->setSsl(true);
        $response = $curl->get('https://api.coindesk.com/v1/bpi/currentprice.json');

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith('application/javascript', $response->getContentType());

        $body = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($body);
        $this->assertObjectHasAttribute('chartName', $body);
        $this->assertEqualsIgnoringCase('bitcoin', $body->chartName);
    }

    public function testGetExchangeRatesApi()
    {
        $curl = new CurlRequest();
        $curl->setSsl(true);
        $response = $curl->get('https://api.coingecko.com/api/v3/exchange_rates');

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith('application/json', $response->getContentType());

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

}
