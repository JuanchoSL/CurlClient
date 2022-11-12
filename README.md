# CurlClient

## Info

This is a class based on native php curl functions in order to perform requests and retrieve responses from remote servers

## Install

Use composer in order to install the module

### Add the repository
```
"repositories": [
    {
        "type": "vcs",
        "url": "http://github.com/juanchosl/curlclient"
    }
],
```

### Add the dependency
```
"require": {
    "juanchosl/curlclient": "1.0.*"
},
```

### Install
```
$ composer install
```


## How use

```
use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\CurlClient\DataConverter;

$array_body_data = DataConverter::bodyPrepare([$key => $value], 'JSON');

$extra_headers = ['Content-type' => 'application/json'];

$curl = new CurlRequest();
$curl->setSsl(true);
$response = $curl->post($url, $array_body_data, $extra_headers);

$http_code = $response->getResponseCode();
$body = $response->getbody();
```
