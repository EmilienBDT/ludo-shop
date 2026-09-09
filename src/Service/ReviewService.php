<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReviewService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReviewRepository $reviewRepository,
    ) {
    }

    public function canReview(User $user, Product $product): bool
    {
        $count = $this->entityManager->getRepository(OrderItem::class)
            ->createQueryBuilder('oi')
            ->select('COUNT(oi.id)')
            ->innerJoin('oi.order', 'o')
            ->where('o.user = :user')
            ->andWhere('o.status = :status')
            ->andWhere('oi.product = :product')
            ->setParameter('user', $user)
            ->setParameter('status', OrderStatus::Paid)
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function create(User $user, Product $product, int $rating, ?string $comment): Review
    {
        if (!$this->canReview($user, $product)) {
            throw new \DomainException('Vous devez avoir commandé et payé ce produit pour laisser un avis.');
        }

        if ($this->reviewRepository->hasUserReviewedProduct($user, $product)) {
            throw new \DomainException('Vous avez déjà laissé un avis pour ce produit.');
        }

        $review = new Review();
        $review->setUser($user);
        $review->setProduct($product);
        $review->setRating($rating);
        $review->setComment($comment);

        $this->entityManager->persist($review);
        $this->entityManager->flush();

        return $review;
    }

    public function averageRating(Product $product): ?float
    {
        return $this->reviewRepository->averageRating($product);
    }

    public function toggleVisibility(Review $review): void
    {
        $review->setIsHidden(!$review->isHidden());
        $this->entityManager->flush();
    }
}
