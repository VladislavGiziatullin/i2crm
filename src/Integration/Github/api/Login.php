<?php

namespace App\Integration\Github\api;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Login extends API
{
    const PATH = '/login/';

    public function __construct(
        protected readonly RequestFactoryInterface $requestFactory,
        protected readonly StreamFactoryInterface $streamFactory,
        protected readonly UriFactoryInterface $uriFactory,
        protected readonly string $scheme = 'https',
        protected readonly string $host = 'github.com'
    ) {}

    public function refreshToken(
        string $refreshToken,
        string $clientId,
        string $clientSecret
    ): RequestInterface {
        return $this->createRequest(
            'POST',
            $this->createUri(self::PATH . 'oauth/access_token')
        )->withBody($this->streamFactory->createStream(json_encode([
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret
            ], JSON_THROW_ON_ERROR))
        );
    }

    protected function getResources(): ResourcesEnum
    {
        return ResourcesEnum::core;
    }
}
