<?php

namespace App\Integration\Github\api;

use Psr\Http\Message\RequestInterface;

class Users extends API
{
    private const PATH = '/users/';

    public function get(string $username): RequestInterface
    {
        return $this->createRequest('GET', $this->createUri(self::PATH . $username));
    }

    public function repos(
        string $username,
        int $perPage = 30,
        int $page = 1,
        UserRepoTypeEnum $type = UserRepoTypeEnum::owner,
        SortEnum $sort = SortEnum::full_name,
        SortDirectionEnum $direction = SortDirectionEnum::asc,
    ): RequestInterface {
        return clone $this->createRequest(
            'GET',
            $this->createUri(self::PATH . rawurlencode($username) . '/repos')
                ->withQuery(http_build_query([
                    'per_page' => $perPage,
                    'page' => $page,
                    'type' => $type->name,
                    'sort' => $sort->name,
                    'direction' => $direction->name,
                ])),
        );
    }

    protected function getResources(): ResourcesEnum
    {
        return ResourcesEnum::core;
    }
}
