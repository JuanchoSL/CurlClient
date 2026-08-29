<?php

namespace JuanchoSL\CurlClient\Tests\Functional;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\CurlClient\Wrappers\PsrCurlClient;
use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpData\Factories\UriFactory;
use PHPUnit\Framework\TestCase;

class SFtpTest extends TestCase
{

    public static function providerData(): array
    {
        return [
            'SFtp' => [(new UriFactory())->createUri(getenv('SFTP_SERVER'))],
        ];
    }

    /**
     * @dataProvider providerData
     */
    public function testOpen($uri)
    {
        $request = (new RequestFactory())->createRequest(RequestMethodInterface::METHOD_HEAD, $uri);
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        $this->assertEquals(0, $response->getStatusCode());
    }
    /**
     * @dataProvider providerData
     */
    public function testNotExists($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_GET, $uri)
            ->withRequestTarget('not-exists/');
        //echo print_r($request, true);exit;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        //echo print_r((string) $response->getBody(), true);exit;
        $this->assertStringContainsString('No such file', (string) $response->getBody());
    }

    /**
     * @dataProvider providerData
     */
    public function testNotList($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_GET, $uri)
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        //echo print_r((string)$response->getBody(), true);exit;
        //$this->assertEquals(Codes::CLOSING_DATA_CONNECTION, $response->getStatusCode());
        $res = explode(PHP_EOL, (string) (new StringsManipulators((string) $response->getBody()))->eol(PHP_EOL));
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
    }
    /**
     * @dataProvider providerData
     */
    public function testMakeDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_POST, $uri)
            ->withRequestTarget("test/")
        ;
        echo print_r($request, true).PHP_EOL;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        echo print_r($response, true).PHP_EOL;
        echo print_r($response, true).PHP_EOL;
        $this->assertStringNotContainsString('mkdir command failed', (string) $response->getBody());

        //$this->assertEquals(Codes::CLOSING_DATA_CONNECTION, $response->getStatusCode());
        //$this->assertNotEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testList($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_GET, $uri)
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        $res = explode(PHP_EOL, (string) (new StringsManipulators((string) $response->getBody()))->eol(PHP_EOL));
        $this->assertNotEmpty($res);
        $this->assertCount(3, $res);
    }
    /**
     * @dataProvider providerData
     */
    public function testFailMakeDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_POST, $uri)
            ->withRequestTarget("test/")
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        $this->assertStringContainsString('mkdir command failed', (string) $response->getBody());
        //$this->assertEquals(Codes::FILE_UNAVAILABLE, $response->getStatusCode());
        //$this->assertEquals("QUOT command failed with 550", (string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testRenameDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MOVE', $uri)
            ->withHeader('Destination', (string) $uri->withUserInfo('')->withPath(rtrim($uri->getPath(), '/') . '/renamed-test/')->getPath())
            ->withRequestTarget("test/")
        ;
        //echo print_r($request, true);exit;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        //echo print_r((string) $response->getBody(), true);exit;
        $this->assertEquals(0, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testWriteFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_POST, $uri)
            ->withRequestTarget("test.txt")
            ->withBody((new StreamFactory())->createStream("Lorem ipsum dolor"))
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        $this->assertEquals(0, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testRenameFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MOVE', $uri)
            ->withHeader('Destination', (string) $uri->withUserInfo('')->withPath(rtrim($uri->getPath(), '/') . '/renamed-test.txt')->getPath())
            ->withRequestTarget("test.txt")
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        $this->assertEmpty((string) $response->getBody());

    }
    /**
     * @dataProvider providerData
     */
    public function testFailRenameFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest('MOVE', $uri)
            ->withHeader('Destination', (string) $uri->withUserInfo('')->withPath(rtrim($uri->getPath(), '/') . '/renamed-test.txt')->getPath())
            ->withRequestTarget("test.txt")
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        $this->assertStringContainsString('No such file', (string) $response->getBody());

        //$this->assertEquals(Codes::FILE_UNAVAILABLE, $response->getStatusCode());
        //$this->assertNotEmpty((string) $response->getBody());

    }
    /**
     * @dataProvider providerData
     */
    public function testDeleteFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_DELETE, $uri)
            ->withRequestTarget("renamed-test.txt")
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        //echo print_r($response, true);exit;
        //$this->assertEquals(Codes::REQUESTED_FILE_ACTION_WAS_OKAY, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testFailDeleteFile($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_DELETE, $uri)
            ->withRequestTarget("renamed-test.txt")
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        $this->assertNotEmpty((string) $response->getBody());
        $this->assertStringContainsString('No such file', (string) $response->getBody());
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
            ->withRequestTarget("renamed-test/")
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        //echo print_r($response, true);exit;
        $this->assertEmpty((string) $response->getBody());
    }
    /**
     * @dataProvider providerData
     */
    public function testFailDeleteDir($uri)
    {
        $request = (new RequestFactory())
            ->createRequest(RequestMethodInterface::METHOD_DELETE, $uri)
            ->withRequestTarget("renamed-test/")
        ;
        $response = (new PsrCurlClient([
            CURLOPT_SSH_PUBLIC_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa.pub']),
            CURLOPT_SSH_PRIVATE_KEYFILE => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'etc', 'localhost-rsa'])
        ]))->sendRequest($request);
        //echo print_r($response, true);exit;
        $this->assertNotEmpty((string) $response->getBody());
        $this->assertStringContainsString('No such file', (string) $response->getBody());
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