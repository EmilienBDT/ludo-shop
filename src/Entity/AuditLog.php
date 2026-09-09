<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Append-only security journal (RG-47): entries are never modified nor
 * deleted once created.
 */
#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 100)]
    private string $action;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $details = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @param array<string, mixed> $details */
    public function __construct(User $user, string $action, array $details = [])
    {
        $this->user = $user;
        $this->action = $action;
        $this->details = $details;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    /** @return array<string, mixed> */
    public function getDetails(): array
    {
        return $this->details;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
