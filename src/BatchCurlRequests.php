<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient;

use CurlHandle;
use JuanchoSL\HttpData\Bodies\Parsers\ResponseReader;
use JuanchoSL\HttpData\Factories\StreamFactory;

class BatchCurlRequests
{

    protected $batch;
    protected $handlers;
    public function __construct()
    {
        $this->batch = curl_multi_init();
    }

    public function addHandler(CurlHandle $handler, mixed $index = null): self
    {
        curl_multi_add_handle($this->batch, $handler);
        if (is_null($index)) {
            $this->handlers[] = $handler;
        } else {
            $this->handlers[$index] = $handler;
        }

        return $this;
    }

    public function __destruct()
    {
        curl_multi_close($this->batch);
    }

    public function __invoke()
    {
        $running = null;
        do {
            $status = curl_multi_exec($this->batch, $running);
            if ($status !== CURLM_OK) {
                throw new \Exception(curl_multi_strerror($code = curl_multi_errno($this->batch)), $code);
            }
            /*
            while (($info = curl_multi_info_read($this->batch)) !== false) {
                if ($info['msg'] === CURLMSG_DONE) {
                    $handle = $info['handle'];
                    curl_multi_remove_handle($this->batch, $handle);
                    $url = $map[$handle];

                    if ($info['result'] === CURLE_OK) {
                        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                        echo "La petición a {$url} ha terminado con el estado HTTP {$statusCode} :", PHP_EOL;
                        echo curl_multi_getcontent($handle);
                        echo PHP_EOL, PHP_EOL;
                    } else {
                        echo "La petición a {$url} ha fallado con el error : ", PHP_EOL;
                        echo curl_strerror($info['result']);
                        echo PHP_EOL, PHP_EOL;
                    }
                }
            }
            */
        } while ($running);

        $results = [];
        foreach ($this->handlers as $i => $handle) {
            $content = curl_multi_getcontent($handle); // Obtiene el contenido

            $result = new ResponseReader((new StreamFactory())->createStream($content));
            $results[$i] = $result();

            curl_multi_remove_handle($this->batch, $handle); // 5. Remueve el manejador
            curl_close($handle); // Cierra el manejador individual
        }

        return $results;
    }
}