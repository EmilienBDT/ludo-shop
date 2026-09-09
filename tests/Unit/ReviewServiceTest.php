<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Service\ReviewService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ReviewService: eligibility, uniqueness, average rating (RG-29).
 */
class ReviewServiceTest extends TestCase
{
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        $this->user = (new User())->setEmail('buyer@example.com');
        $this->product = (new Product())->setName('Catan');
    }

    public function testCanReviewReturnsTrueWhenPaidOrderExists(): void
    {
        $service = $this->createServiceWithOrderCount(1);

        $this->assertTrue($service->canReview($this->user, $this->product));
    }

    public function testCanReviewReturnsFalseWhenNoPaidOrder(): void
    {
        $service = $this->createServiceWithOrderCount(0);

        $this->assertFalse($service->canReview($this->user, $this->product));
    }

    public function testCreateThrowsWhenNotEligible(): void
    {
        $service = $this->createServiceWithOrderCount(0);

        $this->expectException(\DomainException::class);
        $service->create($this->user, $this->product, 5, 'Great game!');
    }

    public function testCreateThrowsOnDuplicateReview(): void
    {
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->method('hasUserReviewedProduct')->willReturn(true);

        $service = new ReviewService($this->createEligibleEntityManager(), $reviewRepo);

        $this->expectException(\DomainException::class);
        $service->create($this->user, $this->product, 4, null);
    }

    public function testCreatePersistsReview(): void
    {
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->method('hasUserReviewedProduct')->willReturn(false);

        $persisted = false;
        $entityManager = $this->createEligibleEntityManager();
        $entityManager->expects($this->once())->method('persist')->willReturnCallback(function () use (&$persisted) {
            $persisted = true;
        });
        $entityManager->expects($this->once())->method('flush');

        $service = new ReviewService($entityManager, $reviewRepo);
        $review = $service->create($this->user, $this->product, 4, 'Nice!');

        $this->assertTrue($persisted);
        $this->assertSame(4, $review->getRating());
        $this->assertSame('Nice!', $review->getComment());
        $this->assertSame($this->user, $review->getUser());
        $this->assertSame($this->product, $review->getProduct());
    }

    public function testAverageRatingReturnsNullWhenNoReviews(): void
    {
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->method('averageRating')->willReturn(null);

        $service = new ReviewService($this->createMock(EntityManagerInterface::class), $reviewRepo);

        $this->assertNull($service->averageRating($this->product));
    }

    public function testAverageRatingReturnsRoundedValue(): void
    {
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->method('averageRating')->willReturn(3.7);

        $service = new ReviewService($this->createMock(EntityManagerInterface::class), $reviewRepo);

        $this->assertSame(3.7, $service->averageRating($this->product));
    }

    public function testHiddenReviewsAreExcludedFromAverage(): void
    {
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->expects($this->once())->method('averageRating')->with($this->product)->willReturn(2.0);

        $service = new ReviewService($this->createMock(EntityManagerInterface::class), $reviewRepo);
        $service->averageRating($this->product);
    }

    private function createServiceWithOrderCount(int $count): ReviewService
    {
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $query->method('getSingleScalarResult')->willReturn($count);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('innerJoin')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $orderItemRepo = $this->createMock(EntityRepository::class);
        $orderItemRepo->method('createQueryBuilder')->willReturn($queryBuilder);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($orderItemRepo);

        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->method('hasUserReviewedProduct')->willReturn(false);

        return new ReviewService($entityManager, $reviewRepo);
    }

    private function createEligibleEntityManager(): EntityManagerInterface
    {
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $query->method('getSingleScalarResult')->willReturn(1);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('innerJoin')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $orderItemRepo = $this->createMock(EntityRepository::class);
        $orderItemRepo->method('createQueryBuilder')->willReturn($queryBuilder);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($orderItemRepo);

        return $entityManager;
    }
}
