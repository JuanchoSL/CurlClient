<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Contracts;

interface CurlResponseInterface
{

    public function getResponseCode(): int;

    public function getContentType(): string;

    public function getBody(): mixed;

    public function getHeaders(): array;

}