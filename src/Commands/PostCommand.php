<?php declare(strict_types=1);

namespace JuanchoSL\CurlClient\Commands;

use JuanchoSL\CurlClient\CurlRequest;
use JuanchoSL\DataTransfer\DataConverters\ArrayConverter;
use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\HttpData\Bodies\Creators\UrlencodedCreator;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\RequestListener\Enums\InputArgument;
use JuanchoSL\RequestListener\Enums\InputOption;
use JuanchoSL\RequestListener\UseCases;
use JuanchoSL\Validators\Types\Strings\StringValidation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostCommand extends UseCases
{
    protected function configure(): void
    {
        $this->addArgument('url', InputArgument::REQUIRED, InputOption::SINGLE);
        $this->addArgument('headers', InputArgument::OPTIONAL, InputOption::MULTI);
        $this->addArgument('body', InputArgument::OPTIONAL, InputOption::SINGLE);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $url = $request->getAttribute('url');
        if (!StringValidation::isUrl($url)) {
            throw new PreconditionFailedException("The url {$url} is not valid");
        }
        $curl = new CurlRequest();
        $content = $curl->post($url, (string) (new UrlencodedCreator)
            ->appendData(ArrayConverter::convert($request->getParsedBody())), ['content-type' => 'application/x-www-form-urlencoded; charset=UTF-8']);
        $body = $content->getBody();
        return $response->withBody((new StreamFactory)->createStream($body));
    }
}