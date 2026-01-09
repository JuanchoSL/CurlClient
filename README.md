# CurlClient

## Info

This is a class based on native php curl functions in order to perform requests and retrieve responses from remote servers

## Install

Use composer in order to install the module

### Add the dependency

```bash
composer require juanchosl/curlclient
```

or

### Install

```json
"require": {
    "juanchosl/curlclient": "1.0.*"
},
```

and

```bash
$ composer install
```

## How to use

### Native cURL lib implementation

> CurlRequest has been marked as __DEPRECATED__, use __Factories__ or specyfic handler type
```php
use JuanchoSL\CurlClient\CurlRequest;

$extra_headers = ['Content-type' => 'application/json'];

$curl = new CurlRequest();
$curl->setSsl(true);
$response = $curl->post($url, json_encode([$key => $value]), $extra_headers);

$http_code = $response->getResponseCode();
$body = $response->getBody();
```
#### HTTP Request
For use with servers using http comunications, with apis or webservers
```php
use JuanchoSL\CurlClient\Engines\Http\CurlHttpRequest;

$extra_headers = ['Content-type' => 'application/json'];

$curl = new CurlHttpRequest();
$curl->setSsl(true);
$response = $curl->post($url, json_encode([$key => $value]), $extra_headers);

$http_code = $response->getResponseCode();
$body = $response->getBody();
```

#### FTP Request
For use with servers using ftp protocols, ftp and ftps
```php
use JuanchoSL\CurlClient\Engines\Ftp\CurlFtpRequest;

$curl = new CurlFtpRequest();
$curl->setSsl(true);
$curl->setPasive(true);
$response = $curl->post("ftps://username:password@host:port/directory/filename.ext", file_get_contents("/path/local/file.ext"));

$body = $response->getBody();
```

#### SMTP Request
For use with smtp servers using single authentication, for send emails

```php
use JuanchoSL\CurlClient\Engines\Email\CurlEmailRequest;

$curl = new CurlEmailRequest();
$curl->setSsl(true);
$response = $curl->post("smtps://username:password@host:port", file_get_contents("/path/to/full/formed/message.eml"));
```
#### Handlers

In order to construct a CurlHandle and prepare the request for future use (as a Batch), use the equivalent CurlXXXHandler and call the prepare{METHOD}, retrieving a standard php CurlHandle
```php
use JuanchoSL\CurlClient\Engines\Http\CurlHttpHandler;

$extra_headers = ['Content-type' => 'application/json'];

$curl = new CurlHttpHandler();
$curl->setSsl(true);
$curl_handle = $curl->preparePost($url, json_encode([$key => $value]), $extra_headers);
```

### PSR-18 Client interface implementation

#### Create a PSR-7 Request using PSR-17 Factory and send using the PSR-18 Client implementation

```php
$request = (new RequestFactory)
    ->createRequest('GET', 'https://www.tecnicosweb.com')
    ->withProtocolVersion('1.1')
    ->withHeader('User-agent',(new UserAgent())->getDesktopWindows(1))
    ->withAddedHeader('Accept','text/html');

$response = (new PsrCurlClient)->sendRequest($request);

print_r($response);
```

#### Returns a PSR-7 Response

```bash
JuanchoSL\HttpData\Containers\Response Object
(
    [protocol_version:protected] => 1.1
    [headers:protected] => Array
        (
            [Date] => Array
                (
                    [0] => Sat, 15 Mar 2025 22:45:48 GMT
                )

            [Content-Type] => Array
                (
                    [0] => text/html; charset=UTF-8
                )

            [Transfer-Encoding] => Array
                (
                    [0] => chunked
                )

            [Connection] => Array
                (
                    [0] => keep-alive
                )

            [Server] => Array
                (
                    [0] => OVHcloud
                )

            [X-Powered-By] => Array
                (
                    [0] => PHP/8.3
                )

            [Set-Cookie] => Array
                (
                    [0] => PHPSESSID=82ba4ec20aa3c3b024c49f3db3d56255; path=/
                )

            [X-Content-Type-Options] => Array
                (
                    [0] => nosniff
                )

            [ETag] => Array
                (
                    [0] => "5ded4b575dbc971a6f22e31632c3b9ff"
                )

            [Expires] => Array
                (
                    [0] => Sun, 16 Mar 2025 22:33:11 GMT
                )

            [Cache-control] => Array
                (
                    [0] => max-age=85643
                    [1] => private
                )

            [Pragma] => Array
                (
                    [0] => cache
                )

            [User-Cache-Control] => Array
                (
                    [0] => max-age=85643
                )

            [Last-Modified] => Array
                (
                    [0] => Sat, 15 Mar 2025 22:33:11 GMT
                )

            [Vary] => Array
                (
                    [0] => Accept-Encoding
                    [1] => User-Agent
                )

        )

    [body:protected] => JuanchoSL\HttpData\Containers\Stream Object
        (
            [resource:protected] => Resource id #51
            [meta:protected] => Array
                (
                )

        )

    [status_code:protected] => 200
    [reasonPhrase:protected] => OK
)
```

#### Extract the response stream body contents from the previous PSR-7 Response

```html
<!DOCTYPE html>
<html lang="es">
  <head>
    <title>Diseño páginas Web Terrassa</title>
    <base href="https://www.tecnicosweb.com" />
    <meta charset="UTF-8" />
    <link
      rel="apple-touch-icon"
      href="https://www.tecnicosweb.com/x-images/apple-touch-icon.png"
    />
    <link rel="canonical" href="https://www.tecnicosweb.com" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=yes, initial-scale=1, maximum-scale=5"
    />
    <meta name="robots" content="INDEX,FOLLOW" />
    <meta
      name="description"
      content="Desarrollo de aplicaciones y páginas web personalizadas a medida. Alta y optimización para buscadores. Integración de social media. Calidad al mejor precio."
    />
    <meta
      name="image_src"
      content="https://www.tecnicosweb.com/x-images/montaje-medium.png"
    />
    <meta
      name="keywords"
      content="aplicaciones, programación, diseño, web, reparación ordenadores, registro dominios, informática, terrassa, redes locales, equipos informáticos, telecomunicaciones, técnicos web, páginas web"
    />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Técnicos web" />
    <meta property="og:title" content="Diseño páginas Web Terrassa" />
    <meta property="og:url" content="https://www.tecnicosweb.com" />
    <meta
      property="og:description"
      content="Desarrollo de aplicaciones y páginas web personalizadas a medida. Alta y optimización para buscadores. Integración de social media. Calidad al mejor precio."
    />
    <meta
      property="og:image"
      content="https://www.tecnicosweb.com/x-images/montaje-medium.png"
    />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@Tecnicosweb" />
    <meta name="twitter:title" content="Diseño páginas Web Terrassa" />
    <meta name="twitter:url" content="https://www.tecnicosweb.com" />
    <meta
      name="twitter:description"
      content="Desarrollo de aplicaciones y páginas web personalizadas a medida. Alta y optimización para buscadores. Integración de social media. Calidad al mejor precio."
    />
    <meta
      name="twitter:image"
      content="https://www.tecnicosweb.com/x-images/montaje-medium.png"
    />
    <meta name="twitter:domain" content="https://www.tecnicosweb.com" />
    <meta name="Designer" content="Juan Sánchez Lecegui" />
    <meta name="Author" content="Juan Sánchez Lecegui" />
    <link rel="publisher" href="https://plus.google.com/+Tecnicosweb" />
    <link
      rel="icon"
      href="https://www.tecnicosweb.com/favicon.ico"
      type="image/x-icon"
    />
    <link
      rel="stylesheet"
      href="https://www.tecnicosweb.com/resources/v1/L2hvbWUvdGVjbmljb3Mvd3d3L3gtY3NzL2dlbmVyaWMuY3NzOy9ob21lL3RlY25pY29zL3d3dy94LWNzcy9zcHJpdGUuY3NzOy9ob21lL3RlY25pY29zL3d3dy94LWNzcy9qY2Fyb3VzZWwucmVzcG9uc2l2ZS5jc3M7L2hvbWUvdGVjbmljb3Mvd3d3L3gtY3NzL21hZ25pZmljLXBvcHVwLmNzczsvaG9tZS90ZWNuaWNvcy93d3cvcmVzb3VyY2VzL2Rlc2Fycm9sbG8td2ViLmNzcw==.css"
      type="text/css"
      media="all"
    />
    <script src="https://www.tecnicosweb.com/resources/v5/aHR0cHM6Ly9mcmFtZXdvcmsudGVjbmljb3N3ZWIuY29tL2NvcmUvY2xpZW50L3NyYy9qcXVlcnktMS4xMS4wLm1pbi5qcztodHRwczovL2ZyYW1ld29yay50ZWNuaWNvc3dlYi5jb20vY29yZS9jbGllbnQvc3JjL2pxdWVyeS1mb3JtUmVzb3VyY2VzLTEuMS5qczsvaG9tZS90ZWNuaWNvcy93d3cveC1jc3MvanF1ZXJ5LmpjYXJvdXNlbC5taW4uanM7L2hvbWUvdGVjbmljb3Mvd3d3L3gtY3NzL2pxdWVyeS1tYWduaWZpYy5wb3B1cC5qczsvaG9tZS90ZWNuaWNvcy93d3cveC1jc3MvanF1ZXJ5LnVudmVpbC5qczsvaG9tZS90ZWNuaWNvcy93d3cvcmVzb3VyY2VzL2Rlc2Fycm9sbG8td2ViLmpz.js"></script>
  </head>
  <body>
    <div id="content" class="minisites ">
      <!-- Contents removed -->
    </div>
  </body>
</html>
```
