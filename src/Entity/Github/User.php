<?php

namespace App\Entity\Github;

use App\Repository\Github\UserRepository;
use App\Validator\Github as GitHubValidator;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`github_user`')]
#[ORM\Index(columns: ['github_user_id'], name: 'idx_github_user_github_user_id')]
#[UniqueEntity('username')]
#[GitHubValidator\UserNameIsExists]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?int $githubUserId = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?int $addedByUserId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $repoLastUpdatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGithubUserId(): ?int
    {
        return $this->githubUserId;
    }

    public function setGithubUserId(int $githubUserId): self
    {
        $this->githubUserId = $githubUserId;

        return $this;
    }

    public function getAddedByUserId(): ?int
    {
        return $this->addedByUserId;
    }

    public function setAddedByUserId(int $addedByUserId): self
    {
        $this->addedByUserId = $addedByUserId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRepoLastUpdatedAt(): ?DateTimeInterface
    {
        return $this->repoLastUpdatedAt;
    }

    public function setRepoLastUpdatedAt(?DateTimeInterface $repoLastUpdatedAt): self
    {
        $this->repoLastUpdatedAt = $repoLastUpdatedAt;

        return $this;
    }
}
