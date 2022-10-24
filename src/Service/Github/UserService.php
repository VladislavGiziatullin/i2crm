<?php

namespace App\Service\Github;

use App\Entity\Github\User as GithubUser;
use App\Entity\User;
use App\Repository\Github\APIRepository;
use App\Repository\Github\UserRepository;

class UserService
{
    public function __construct(
        private readonly APIRepository $APIRepository, // TODO: need hidden by interface
        private readonly UserRepository $repository, // TODO: need hidden by interface
    ){}

    public function create(GithubUser $user, User $creator): void
    {
        $username = $user->getUsername();
        $githubUserResponse = $this->APIRepository->getUser($creator->getId(), $username);

        $user->setGithubUserId($githubUserResponse->getGithubUserId())
            ->setAddedByUserId($creator->getId());

        $this->save($user); // Тут есть обработчик который слушает событие и создает в очередь задачу на выгрузку репозиториев
    }

    public function save(GithubUser $user): void
    {
        $this->repository->save($user, true);
    }

    public function remove(GithubUser $user): void
    {
        $this->repository->remove($user, true);
    }

    /**
     * @return iterable|User[]
     */
    public function getAll(): iterable
    {
        return $this->repository->findAll();
    }

    public function getByGithubUserId(int $githubUserId): ?GithubUser
    {
        return $this->repository->findOneBy(['githubUserId' => $githubUserId]);
    }
}
