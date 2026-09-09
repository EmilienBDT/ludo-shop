<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\WishlistItem;
use App\Repository\WishlistItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class WishlistService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WishlistItemRepository $wishlistItemRepository,
    ) {
    }

    public function add(User $user, Product $product): void
    {
        if ($product->isMature() && !$user->isAdult()) {
            throw new \DomainException('Vous devez être majeur pour ajouter un produit mature à votre liste de souhaits.');
        }

        if ($this->has($user, $product)) {
            throw new \DomainException('Ce produit est déjà dans votre liste de souhaits.');
        }

        $item = new WishlistItem($user, $product);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function remove(User $user, Product $product): void
    {
        $item = $this->findByUserAndProduct($user, $product);

        if (null === $item) {
            throw new \DomainException('Ce produit n\'est pas dans votre liste de souhaits.');
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    public function has(User $user, Product $product): bool
    {
        return null !== $this->findByUserAndProduct($user, $product);
    }

    /**
     * @return WishlistItem[]
     */
    public function findByUser(User $user): array
    {
        return $this->wishlistItemRepository->findByUser($user);
    }

    public function count(User $user): int
    {
        return $this->wishlistItemRepository->countByUser($user);
    }

    private function findByUserAndProduct(User $user, Product $product): ?WishlistItem
    {
        return $this->wishlistItemRepository->findOneBy(['user' => $user, 'product' => $product]);
    }
}
