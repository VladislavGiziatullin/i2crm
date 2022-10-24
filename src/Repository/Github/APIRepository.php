<?php

namespace App\Repository\Github;

use App\Entity\Github\User;
use App\Entity\Github\UserRepo;
use App\Integration\Github\api\SortDirectionEnum;
use App\Integration\Github\api\SortEnum;
use App\Integration\Github\api\UserRepoTypeEnum;
use App\Integration\Github\api\Users;
use App\Integration\Github\ClientFactory;
use App\Integration\Github\Exception\NotFoundException;
use App\Integration\Github\Exception\UserNotFoundException;
use DateTime;
use DateTimeInterface;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class APIRepository
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Users $users,
    ) {}

    /**
     * @return iterable|UserRepo[]
     * @throws UserNotFoundException
     * @throws JsonException
     * @throws ClientExceptionInterface
     */
    public function getAllUserRepos(
        string $username,
        int $requestUserId,
        UserRepoTypeEnum $type = UserRepoTypeEnum::owner,
        SortEnum $sort = SortEnum::full_name,
        SortDirectionEnum $direction = SortDirectionEnum::asc,
    ): iterable {
        $perPage = 5; // для наглядности пагинации
        $page = 1;

        $client = $this->clientFactory->create($requestUserId);

        $pageCount = $perPage;
        while ($pageCount === $perPage) {
            $request = $this->users->repos($username, $perPage, $page++, $type, $sort, $direction);

            $repos = $this->decodeJsonResponse($client->sendRequest($request));

            $pageCount = count($repos);

            foreach ($repos as $repo) {
                yield $this->createUserRepoFromResponse($repo); // Чтобы память не забивать
            }
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws UserNotFoundException
     */
    public function getUser(int $requestUserId, string $username): ?User
    {
        try {
            return $this->createUserFromResponse(
                $this->decodeJsonResponse(
                    $this->clientFactory->create($requestUserId)->sendRequest($this->users->get($username))
                )
            );
        } catch (NotFoundException) {
            return null;
        }
    }

    private function decodeJsonResponse(ResponseInterface $response): array
    {
        return json_decode(
            $response->getBody()->getContents(),
            true,
            flags: JSON_THROW_ON_ERROR
        );
    }

    private function createUserRepoFromResponse(mixed $repo): UserRepo
    {
        return (new UserRepo())
            ->setGithubUserId($repo['owner']['id'])
            ->setGithubRepoId($repo['id'])
            ->setName($repo['name'])
            ->setRepoUpdatedAt(DateTime::createFromFormat(DateTimeInterface::RFC3339, $repo['updated_at']));
    }

    private function createUserFromResponse(array $user): User
    {
        return (new User())
            ->setGithubUserId($user['id'])
            ->setUsername($user['login']);
    }
}
