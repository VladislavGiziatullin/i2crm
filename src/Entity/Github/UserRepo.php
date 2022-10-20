<?php

namespace App\Entity\Github;

use App\Repository\Github\UserRepoRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepoRepository::class)]
#[ORM\Table(name: '`github_user_repo`')]
class UserRepo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?int $githubRepoId = null;

    #[ORM\Column]
    private ?int $githubUserId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $repoUpdatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGithubRepoId(): ?int
    {
        return $this->githubRepoId;
    }

    public function setGithubRepoId(int $githubRepoId): self
    {
        $this->githubRepoId = $githubRepoId;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRepoUpdatedAt(): ?DateTimeInterface
    {
        return $this->repoUpdatedAt;
    }

    public function setRepoUpdatedAt(DateTimeInterface $repoUpdatedAt): self
    {
        $this->repoUpdatedAt = $repoUpdatedAt;

        return $this;
    }
}
