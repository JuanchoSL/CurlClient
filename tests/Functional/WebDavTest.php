<?php

namespace JuanchoSL\CurlClient\Tests\Functional;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\CurlClient\Wrappers\PsrCurlClient;
use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpData\Factories\UriFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class WebDavTest extends TestCase
{

    public static function providerData(): array
    {
        return [
            'webdav' => [(new UriFactory())->createUri(getenv("WEBDAV_SERVER"))]
        ];
    }

    /**
     * @dataProvider providerData
     */
    public function testOpen($uri)
    {
        $request = (new RequestFactory())->createRequest(RequestMethodInterface::METHOD_HEAD, $uri);
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }
    /**
     * @dataProvider providerData
     */
    public function testNotExists($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_GET, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withRequestTarget('/not-exists/');
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @dataProvider providerData
     */
    public function testNotList($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_GET, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertNotEmpty((string) $response->getBody());
        $this->assertStringNotContainsString('test', (string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testMakeDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MKCOL', $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withHeader("Accept", 'application/json')
            ->withRequestTarget("/test/")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testList($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_GET, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertNotEmpty((string) $response->getBody());
        $this->assertStringContainsString('test/', (string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testFailMakeDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MKCOL', $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withRequestTarget("/test/")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }
    /**
     * @dataProvider providerData
     */
    public function testRenameDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MOVE', $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withHeader("Overwrite", 'T')
            ->withHeader("Destination", (string) $uri->withUserInfo('')->withPath('/renamed-test/'))
            ->withRequestTarget("/test/")
        ;

        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testWriteFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_PUT, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withRequestTarget("/test.txt")
            ->withBody((new StreamFactory())->createStream("Lorem ipsum dolor"))
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testRenameFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MOVE', $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withHeader("Overwrite", 'T')
            ->withHeader("Destination", (string) $uri->withUserInfo('')->withPath('/renamed-test.txt'))
            ->withRequestTarget("/test.txt")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testFailRenameFile(UriInterface $uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MOVE', $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withHeader("Overwrite", 'T')
            ->withHeader("Destination", (string) $uri->withUserInfo('')->withPath('/renamed-test.txt'))
            ->withRequestTarget("/test.txt")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertNotEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testDeleteFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_DELETE, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withRequestTarget("/renamed-test.txt")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testFailDeleteFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_DELETE, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withRequestTarget("/renamed-test.txt")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertNotEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testTruncateDir($uri)
    {
        $this->markTestSkipped();
        $uri->chdir('/');
        $this->assertTrue($uri->truncate('renamed-test/'), $uri->getStatus());
    }
    /**
     * @dataProvider providerData
     */
    public function testFailTruncateDir($uri)
    {
        $this->markTestSkipped();
        $uri->chdir('/');
        $this->assertFalse($uri->truncate('renamed-test/'), $uri->getStatus());
    }
    /**
     * @dataProvider providerData
     */
    public function testDeleteDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_DELETE, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withRequestTarget("/renamed-test/")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testFailDeleteDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_DELETE, $uri)
            ->withHeader("Authorization", 'Basic ' . base64_encode($uri->getUserInfo()))
            ->withRequestTarget("/renamed-test/")
        ;
        $response = (new PsrCurlClient())->sendRequest($request);
        $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertNotEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testClear($uri)
    {
        $this->markTestSkipped();
        $uri->chdir('/');
        $this->assertTrue($uri->truncate('.'));
    }
}