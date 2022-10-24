<?php

namespace App\Integration\Github;

use App\Entity\User;
use App\Integration\Github\api\API;
use App\Integration\Github\api\Login;
use App\Integration\Github\api\RateLimit;
use App\Integration\Github\Exception\ApiLimitExceedException;
use App\Integration\Github\Exception\BadRefreshTokenException;
use App\Integration\Github\Exception\ClientErrorException;
use App\Integration\Github\Exception\InternalServerErrorException;
use App\Integration\Github\Exception\NotFoundException;
use App\Repository\UserRepository;
use DateTime;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class Client implements ClientInterface
{
    private const X_RATELIMIT_LIMIT = 'limit';
    private const HEADER_X_RATELIMIT_LIMIT = 'x-ratelimit-' . self::X_RATELIMIT_LIMIT;
    private const X_RATELIMIT_REMAINING = 'remaining';
    private const HEADER_X_RATELIMIT_REMAINING = 'x-ratelimit-' . self::X_RATELIMIT_REMAINING;
    private const X_RATELIMIT_RESET = 'reset';
    private const HEADER_X_RATELIMIT_RESET = 'x-ratelimit-' . self::X_RATELIMIT_RESET;
    private const X_RATELIMIT_USED = 'used';
    private const HEADER_X_RATELIMIT_USED = 'x-ratelimit-' . self::X_RATELIMIT_USED;
    private const X_RATELIMIT_RESOURCE = 'resource';
    private const HEADER_X_RATELIMIT_RESOURCE = 'x-ratelimit-' . self::X_RATELIMIT_RESOURCE;

    // It field value may be cached
    private ?array $rateLimits = null;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly UserRepository $userRepository,
        private readonly ClientInterface $client,
        private readonly Login $login,
        private readonly RateLimit $rateLimit,
        private ?User $user = null,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if ($this->user !== null) {
            $this->authenticate();
            $githubAccessToken = $this->user->getGithubAccessToken();
            $request = $request->withHeader('Authorization', sprintf('Bearer %s', $githubAccessToken));
        }

        if ($this->rateLimits === null) {
            $rateLimitsContent = json_decode(
                $this->client->sendRequest($this->setContentTypeHeaders($this->rateLimit->get()))->getBody()->getContents(),
                true,
                flags: JSON_THROW_ON_ERROR
            );

            $this->rateLimits = $rateLimitsContent['resources'];
        }

        $this->checkRateLimits($request->getHeader(API::HEADER_API_RESOURCE)[0]);
        $request = $request->withoutHeader(API::HEADER_API_RESOURCE);

        $response = $this->client->sendRequest($this->setContentTypeHeaders($request));

        $this->handleResponseErrors($response);

        $this->rateLimits[self::HEADER_X_RATELIMIT_RESOURCE] = [
            self::X_RATELIMIT_LIMIT => (int)$response->getHeader(self::HEADER_X_RATELIMIT_LIMIT)[0],
            self::X_RATELIMIT_REMAINING => (int)$response->getHeader(self::HEADER_X_RATELIMIT_REMAINING)[0],
            self::X_RATELIMIT_RESET => (int)$response->getHeader(self::HEADER_X_RATELIMIT_RESET)[0],
            self::X_RATELIMIT_USED => (int)$response->getHeader(self::HEADER_X_RATELIMIT_USED)[0],
        ];

        return $response;
    }

    public function setUser(?User $user = null)
    {
        $this->user = $user;
    }

    protected function authenticate(): void
    {
        if ($this->user === null || !$this->isExpiredToken($this->user)) {
            return;
        }

        $request = $this->login->refreshToken($this->user->getGithubRefreshToken(), $this->clientId, $this->clientSecret);

        $responseContent = json_decode(
            $this->client->sendRequest($this->setContentTypeHeaders($request))->getBody()->getContents(),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        if (isset($responseContent['error'])) {
            throw new BadRefreshTokenException($responseContent['error_description']);
        }

        $this->user
            ->setGithubAccessToken($responseContent['access_token'])
            ->setGithubAccessTokenExpiresAt(DateTime::createFromFormat('U', time() + $responseContent['expires_in']))
            ->setGithubRefreshToken($responseContent['refresh_token']);

        $this->userRepository->save($this->user);
    }

    private function isExpiredToken(User $user): bool
    {
        return $user->getGithubAccessTokenExpiresAt() <= (new DateTime())->modify('-30 seconds');
    }

    private function setContentTypeHeaders(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json');
    }

    private function checkRateLimits(string $resource): void
    {
        $resourceLimits = $this->rateLimits[$resource];
        if ($resourceLimits[self::X_RATELIMIT_REMAINING] < 1) {
            throw new ApiLimitExceedException(
                $resource,
                $resourceLimits[self::X_RATELIMIT_LIMIT],
                $resourceLimits[self::X_RATELIMIT_RESET] - time(),
            );
        }
    }

    private function handleResponseErrors(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            throw new InternalServerErrorException($response->getReasonPhrase(), $statusCode);
        }

        if ($statusCode >= Response::HTTP_BAD_REQUEST) {
            if ($statusCode === Response::HTTP_NOT_FOUND) {
                throw new NotFoundException($response->getReasonPhrase(), $statusCode);
            }
            throw new ClientErrorException($response->getReasonPhrase(), $statusCode);
        }
    }
}
