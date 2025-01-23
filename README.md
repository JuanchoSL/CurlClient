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
```json
"require": {
    "juanchosl/curlclient": "1.0.*"
},
```

### Install
```bash
$ composer install
```


## How use

```php
use JuanchoSL\CurlClient\CurlRequest;

$extra_headers = ['Content-type' => 'application/json'];

$curl = new CurlRequest();
$curl->setSsl(true);
$response = $curl->post($url, json_encode([$key => $value]), $extra_headers);

$http_code = $response->getResponseCode();
$body = $response->getBody();
```
