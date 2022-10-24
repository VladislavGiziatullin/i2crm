<?php

namespace App\Infrastructure\Integration\Github\api;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

abstract class API
{
    public const HEADER_API_RESOURCE = 'resource';

    public function __construct(
        protected readonly RequestFactoryInterface $requestFactory,
        protected readonly StreamFactoryInterface $streamFactory,
        protected readonly UriFactoryInterface $uriFactory,
        protected readonly string $scheme = 'https',
        protected readonly string $host = 'api.github.com',
    ) {}

    abstract protected function getResources(): ResourcesEnum;

    protected function createRequest(string $method, UriInterface $uri): RequestInterface
    {
        return $this->requestFactory
            ->createRequest($method, $uri)
            ->withHeader(self::HEADER_API_RESOURCE, $this->getResources()->name);
    }

    protected function createUri(string $path): UriInterface
    {
        return clone $this->uriFactory->createUri($path)
            ->withScheme($this->scheme)
            ->withHost($this->host);
    }
}
