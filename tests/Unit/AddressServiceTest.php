<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Address;
use App\Entity\User;
use App\Repository\AddressRepository;
use App\Service\AddressService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AddressServiceTest extends TestCase
{
    private AddressService $service;
    private \PHPUnit\Framework\MockObject\MockObject&EntityManagerInterface $entityManager;
    private \PHPUnit\Framework\MockObject\MockObject&AddressRepository $addressRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->addressRepository = $this->createMock(AddressRepository::class);
        $this->entityManager->method('getRepository')->with(Address::class)->willReturn($this->addressRepository);
        $this->service = new AddressService($this->entityManager);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');

        return $user;
    }

    private function createAddress(User $user, bool $isDefault = false): Address
    {
        $address = new Address(
            user: $user,
            label: 'Domicile',
            addressLine: '123 Rue Test',
            city: 'Paris',
            postalCode: '75001',
            country: 'FR',
        );
        if ($isDefault) {
            $address->setDefault(true);
        }

        return $address;
    }

    public function testFindByUserReturnsUserAddresses(): void
    {
        $user = $this->createUser();
        $addresses = [$this->createAddress($user)];

        $this->addressRepository->method('findBy')
            ->with(['user' => $user], ['isDefault' => 'DESC', 'label' => 'ASC'])
            ->willReturn($addresses);

        $result = $this->service->findByUser($user);

        $this->assertSame($addresses, $result);
    }

    public function testSetDefaultClearsPreviousDefault(): void
    {
        $user = $this->createUser();
        $oldDefault = $this->createAddress($user, true);
        $newDefault = $this->createAddress($user, false);

        $this->addressRepository->method('findBy')
            ->with(['user' => $user, 'isDefault' => true])
            ->willReturn([$oldDefault]);

        $this->entityManager->expects($this->once())->method('flush');

        $this->service->setDefault($newDefault);

        $this->assertFalse($oldDefault->isDefault());
        $this->assertTrue($newDefault->isDefault());
    }

    public function testSetDefaultDoesNothingIfAlreadyDefault(): void
    {
        $user = $this->createUser();
        $address = $this->createAddress($user, true);

        $this->addressRepository->method('findBy')
            ->with(['user' => $user, 'isDefault' => true])
            ->willReturn([$address]);

        $this->entityManager->expects($this->never())->method('flush');

        $this->service->setDefault($address);

        $this->assertTrue($address->isDefault());
    }

    public function testGetDefaultReturnsDefaultAddress(): void
    {
        $user = $this->createUser();
        $default = $this->createAddress($user, true);

        $this->addressRepository->method('findOneBy')
            ->with(['user' => $user, 'isDefault' => true])
            ->willReturn($default);

        $this->assertSame($default, $this->service->getDefault($user));
    }

    public function testGetDefaultReturnsNullWhenNone(): void
    {
        $user = $this->createUser();

        $this->addressRepository->method('findOneBy')
            ->with(['user' => $user, 'isDefault' => true])
            ->willReturn(null);

        $this->assertNull($this->service->getDefault($user));
    }
}
