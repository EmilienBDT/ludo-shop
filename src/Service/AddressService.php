<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Address;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AddressService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /** @return list<Address> */
    public function findByUser(User $user): array
    {
        /* @var list<Address> */
        return $this->entityManager->getRepository(Address::class)->findBy(
            ['user' => $user],
            ['isDefault' => 'DESC', 'label' => 'ASC'],
        );
    }

    public function getDefault(User $user): ?Address
    {
        return $this->entityManager->getRepository(Address::class)->findOneBy([
            'user' => $user,
            'isDefault' => true,
        ]);
    }

    public function setDefault(Address $address): void
    {
        if ($address->isDefault()) {
            return;
        }

        $this->unsetCurrentDefaults($address);
        $address->setDefault(true);
        $this->entityManager->flush();
    }

    public function setDefaultIfNeeded(Address $address): void
    {
        if (!$address->isDefault()) {
            return;
        }

        $this->unsetCurrentDefaults($address);
    }

    private function unsetCurrentDefaults(Address $address): void
    {
        $currentDefaults = $this->entityManager->getRepository(Address::class)->findBy([
            'user' => $address->getUser(),
            'isDefault' => true,
        ]);

        foreach ($currentDefaults as $old) {
            $old->setDefault(false);
        }
    }
}
