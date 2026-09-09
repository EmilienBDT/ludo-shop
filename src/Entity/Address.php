<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Table(name: 'addresses')]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(length: 100)]
    private string $label = '';

    #[ORM\Column(length: 255)]
    private string $addressLine = '';

    #[ORM\Column(length: 100)]
    private string $city = '';

    #[ORM\Column(length: 20)]
    private string $postalCode = '';

    #[ORM\Column(length: 100)]
    private string $country = '';

    #[ORM\Column(options: ['default' => false])]
    private bool $isDefault = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        User $user,
        string $label,
        string $addressLine,
        string $city,
        string $postalCode,
        string $country,
    ) {
        $this->user = $user;
        $this->label = $label;
        $this->addressLine = $addressLine;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getAddressLine(): string
    {
        return $this->addressLine;
    }

    public function setAddressLine(string $addressLine): static
    {
        $this->addressLine = $addressLine;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function setDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
