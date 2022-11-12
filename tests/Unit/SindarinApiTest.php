<?php

namespace Tests\Unit;

use JuanchoSL\CurlClient\CurlResponse;
use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\CurlClient\DataConverter;
use PHPUnit\Framework\TestCase;

class SindarinApiTest extends TestCase
{

    public function testGetSindarinApi()
    {
        $curl = new CurlRequest();
        $curl->setSsl(true);
        $body = DataConverter::bodyPrepare(['text' => 'This is a sample text'], 'url');
        $response = $curl->get('https://api.funtranslations.com/translate/sindarin.json?' . $body);
        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getResponseCode());
        $this->assertStringStartsWith('application/json', $response->getContentType());
        $response = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('success', $response);
        $this->assertObjectHasAttribute('contents', $response);
    }

}
