<?php

namespace Tests\Unit;

use JuanchoSL\CurlClient\CurlClient;
use JuanchoSL\CurlClient\DataConverter;
use PHPUnit\Framework\TestCase;

class SindarinApiTest extends TestCase
{

    public function testGetSindarinApi()
    {
        $curl = new CurlClient();
        $curl->setSsl(true);
        $body = DataConverter::bodyPrepare(['text' => 'This is a sample text'], 'url');
        $response = $curl->get('https://api.funtranslations.com/translate/sindarin.json?' . $body);
//        print_r($response);exit;
        $code = $curl->getLastInfo('http_code');
        $this->assertEquals(200, $code);
        $response = json_decode($response, false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('success', $response);
        $this->assertObjectHasAttribute('contents', $response);
    }

}
