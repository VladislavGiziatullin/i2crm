<?php

namespace App\Integration\Github;

use App\Integration\Github\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Psr\Http\Client\ClientInterface;

class ClientFactory
{
    private $stack = [];

    public function __construct(
        private readonly Client $client,
        private readonly UserRepository $userRepository,
    ){}

    public function create(?int $userId = null): ClientInterface
    {
        $client = $this->stack[$userId] ?? null;
        if ($client !== null) {
            return $client;
        }

        $client = clone $this->client;

        $this->stack[$userId] = $client;

        if ($userId === null) {
            return $client;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new UserNotFoundException($userId);
        }

        $client->setUser($user);

        return $client;
    }
}
