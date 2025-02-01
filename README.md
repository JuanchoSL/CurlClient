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

```php
use JuanchoSL\CurlClient\CurlRequest;

$extra_headers = ['Content-type' => 'application/json'];

$curl = new CurlRequest();
$curl->setSsl(true);
$response = $curl->post($url, json_encode([$key => $value]), $extra_headers);

$http_code = $response->getResponseCode();
$body = $response->getBody();
```

### PSR-18 Client interface implementation

#### Create a PSR-7 Request using PSR-17 Factory and send using the PSR-18 Client implementation

```php
$request = (new RequestFactory)
    ->createRequest('GET', 'https://www.tecnicosweb.com')
    ->withProtocolVersion('1.1')
    ->withHeader('User-agent',(new UserAgent())->getDesktopWindows(1))
    ->withAddedHeader('Accept','text/html')
    ;

$response = (new Psr7CurlClient)->sendRequest($request);

print_r($response);
```

#### Returns a PSR-7 Response

```bash
JuanchoSL\HttpData\Containers\Response Object
(
    [protocol_version:protected] => 1.1
    [headers:protected] => Array
        (
            [url] => Array
                (
                    [0] => https://www.tecnicosweb.com/
                )

            [content-type] => Array
                (
                    [0] => text/html; charset=UTF-8
                )

            [code] => Array
                (
                    [0] => 200
                )

            [header-size] => Array
                (
                    [0] => 473
                )

            [request-size] => Array
                (
                    [0] => 237
                )

            [filetime] => Array
                (
                    [0] => -1
                )

            [ssl-verify-result] => Array
                (
                    [0] => 20
                )

            [redirect-count] => Array
                (
                    [0] => 0
                )

            [total-time] => Array
                (
                    [0] => 0.258074
                )

            [namelookup-time] => Array
                (
                    [0] => 0.064663
                )

            [connect-time] => Array
                (
                    [0] => 0.111044
                )

            [pretransfer-time] => Array
                (
                    [0] => 0.169494
                )

            [size-upload] => Array
                (
                    [0] => 0
                )

            [size-download] => Array
                (
                    [0] => 31524
                )

            [speed-download] => Array
                (
                    [0] => 122151
                )

            [speed-upload] => Array
                (
                    [0] => 0
                )

            [download-content-length] => Array
                (
                    [0] => -1
                )

            [upload-content-length] => Array
                (
                    [0] => 0
                )

            [starttransfer-time] => Array
                (
                    [0] => 0.217347
                )

            [redirect-time] => Array
                (
                    [0] => 0
                )

            [redirect-url] => Array
                (
                    [0] =>
                )

            [primary-ip] => Array
                (
                    [0] => 87.98.231.3
                )

            [certinfo] => Array
                (
                    [0] => Array
                        (
                        )

                )

            [primary-port] => Array
                (
                    [0] => 443
                )

            [local-ip] => Array
                (
                    [0] => 192.168.0.5
                )

            [local-port] => Array
                (
                    [0] => 60058
                )

            [version] => Array
                (
                    [0] => 2
                )

            [protocol] => Array
                (
                    [0] => 2
                )

            [ssl-verifyresult] => Array
                (
                    [0] => 0
                )

            [scheme] => Array
                (
                    [0] => https
                )

            [appconnect-time-us] => Array
                (
                    [0] => 169416
                )

            [connect-time-us] => Array
                (
                    [0] => 111044
                )

            [namelookup-time-us] => Array
                (
                    [0] => 64663
                )

            [pretransfer-time-us] => Array
                (
                    [0] => 169494
                )

            [redirect-time-us] => Array
                (
                    [0] => 0
                )

            [starttransfer-time-us] => Array
                (
                    [0] => 217347
                )

            [posttransfer-time-us] => Array
                (
                    [0] => 169510
                )

            [total-time-us] => Array
                (
                    [0] => 258074
                )

            [effective-method] => Array
                (
                    [0] => GET
                )

            [capath] => Array
                (
                    [0] =>
                )

            [cainfo] => Array
                (
                    [0] =>
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

#### Extract the response stream body contents from the previus PSR-7 Response

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
