<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\WishlistItem;
use App\Repository\WishlistItemRepository;
use App\Service\WishlistService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class WishlistServiceTest extends TestCase
{
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        $this->user = (new User())->setEmail('test@example.com');
        $this->product = (new Product())->setName('Catan')->setPrice(50.00);
    }

    public function testAddPersistsWishlistItem(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $repo = $this->createMock(WishlistItemRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $service = new WishlistService($em, $repo);
        $service->add($this->user, $this->product);
    }

    public function testAddThrowsOnDuplicate(): void
    {
        $existingItem = new WishlistItem($this->user, $this->product);
        $repo = $this->createMock(WishlistItemRepository::class);
        $repo->method('findOneBy')->willReturn($existingItem);

        $service = new WishlistService($this->createMock(EntityManagerInterface::class), $repo);

        $this->expectException(\DomainException::class);
        $service->add($this->user, $this->product);
    }

    public function testAddThrowsForMinorWithMatureProduct(): void
    {
        $minor = (new User())->setEmail('minor@example.com');
        $minor->setBirthDate(new \DateTimeImmutable('-16 years'));

        $matureProduct = (new Product())->setName('Exploding Kittens')->setPrice(30.00)->setIsMature(true);

        $repo = $this->createMock(WishlistItemRepository::class);
        $service = new WishlistService($this->createMock(EntityManagerInterface::class), $repo);

        $this->expectException(\DomainException::class);
        $service->add($minor, $matureProduct);
    }

    public function testAddAllowsAdultWithMatureProduct(): void
    {
        $adult = (new User())->setEmail('adult@example.com');
        $adult->setBirthDate(new \DateTimeImmutable('-25 years'));

        $matureProduct = (new Product())->setName('Mature Game')->setPrice(30.00)->setIsMature(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $repo = $this->createMock(WishlistItemRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $service = new WishlistService($em, $repo);
        $service->add($adult, $matureProduct);
    }

    public function testRemoveDeletesWishlistItem(): void
    {
        $item = new WishlistItem($this->user, $this->product);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove');
        $em->expects($this->once())->method('flush');
        $repo = $this->createMock(WishlistItemRepository::class);
        $repo->method('findOneBy')->willReturn($item);

        $service = new WishlistService($em, $repo);
        $service->remove($this->user, $this->product);
    }

    public function testRemoveThrowsWhenNotInWishlist(): void
    {
        $repo = $this->createMock(WishlistItemRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $service = new WishlistService($this->createMock(EntityManagerInterface::class), $repo);

        $this->expectException(\DomainException::class);
        $service->remove($this->user, $this->product);
    }

    public function testHasReturnsTrueWhenPresent(): void
    {
        $item = new WishlistItem($this->user, $this->product);
        $repo = $this->createMock(WishlistItemRepository::class);
        $repo->method('findOneBy')->willReturn($item);

        $service = new WishlistService($this->createMock(EntityManagerInterface::class), $repo);

        $this->assertTrue($service->has($this->user, $this->product));
    }

    public function testHasReturnsFalseWhenAbsent(): void
    {
        $repo = $this->createMock(WishlistItemRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $service = new WishlistService($this->createMock(EntityManagerInterface::class), $repo);

        $this->assertFalse($service->has($this->user, $this->product));
    }
}
