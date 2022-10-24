<?php

namespace App\Message\Github;

final class UpdateUserRepoMessage
{
    public function __construct(
        private readonly int $githubUserId,
    ){}

    public function getGithubUserId(): int
    {
        return $this->githubUserId;
    }
}
