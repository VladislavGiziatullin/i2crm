<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(unique: true)]
    private ?int $githubUserId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $githubAccessToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $githubAccessTokenExpiresAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $githubRefreshToken = null;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getGithubAccessToken(): ?string
    {
        return $this->githubAccessToken;
    }

    public function setGithubAccessToken(?string $githubAccessToken): self
    {
        $this->githubAccessToken = $githubAccessToken;

        return $this;
    }

    public function getGithubAccessTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->githubAccessTokenExpiresAt;
    }

    public function setGithubAccessTokenExpiresAt(?\DateTimeInterface $githubAccessTokenExpiresAt): self
    {
        $this->githubAccessTokenExpiresAt = $githubAccessTokenExpiresAt;

        return $this;
    }

    public function getGithubRefreshToken(): ?string
    {
        return $this->githubRefreshToken;
    }

    public function setGithubRefreshToken(?string $githubRefreshToken): self
    {
        $this->githubRefreshToken = $githubRefreshToken;

        return $this;
    }
}
