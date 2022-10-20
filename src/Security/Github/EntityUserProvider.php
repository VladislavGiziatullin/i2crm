<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Github;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class EntityUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    public function __construct(private readonly UserRepository $repository) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findUser(['username' => $identifier]);

        if ($user === null) {
            $exception = new UserNotFoundException(sprintf("User '%s' not found.", $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): ?UserInterface
    {
        $githubUserId = $response->getUsername();
        $user = $this->findUser(['githubUserId' => $githubUserId]);
        if ($user === null) {
            $user = (new User())
                ->setUsername($response->getNickname())
                ->setGithubUserId($githubUserId);
        }

        $githubAccessToken = $response->getAccessToken();
        if ($user->getGithubAccessToken() !== $githubAccessToken) {
            $user->setGithubAccessToken($githubAccessToken)
                ->setGithubAccessTokenExpiresAt(DateTime::createFromFormat('U', time() + $response->getExpiresIn()))
                ->setGithubRefreshToken($response->getRefreshToken());
        }

        $this->repository->save($user, true);

        return $user;
    }

    public function refreshUser(UserInterface $user): ?UserInterface
    {
        $class = \get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        $username = $user->getUserIdentifier();

        $user = $this->findUser(['username' => $username]);
        if ($user === null) {
            throw $this->createUserNotFoundException($username, sprintf('User with ID "%s" could not be reloaded.', $username));
        }

        return $user;
    }

    public function supportsClass($class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    private function findUser(array $criteria): ?UserInterface
    {
        return $this->repository->findOneBy($criteria);
    }

    private function createUserNotFoundException(string $username, string $message): UserNotFoundException
    {
        $exception = new UserNotFoundException($message);
        $exception->setUserIdentifier($username);

        return $exception;
    }
}
