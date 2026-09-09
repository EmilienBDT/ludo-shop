<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Address;
use App\Entity\Product;
use App\Service\CartService;

/**
 * B-02 — RG-34 / UC-23: address book, default address, snapshot in order.
 */
class AddressBookTest extends FunctionalTestCase
{
    public function testAddressListPageLoads(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/account/addresses');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mes adresses');
    }

    public function testAddressListRedirectsAnonymous(): void
    {
        $this->client->request('GET', '/account/addresses');

        $this->assertResponseRedirects();
    }

    public function testNewAddressFormVisible(): void
    {
        $this->login('client@example.com');

        $crawler = $this->client->request('GET', '/account/addresses/new');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form[name="address_form"]'));
    }

    public function testAddressCanBeCreated(): void
    {
        $this->login('client@example.com');
        $crawler = $this->client->request('GET', '/account/addresses/new');

        $token = $crawler->filter('input[name="address_form[_token]"]')->attr('value');

        $this->client->request('POST', '/account/addresses/new', [
            'address_form' => [
                'label' => 'Domicile',
                'addressLine' => '123 Rue de la Paix',
                'postalCode' => '75002',
                'city' => 'Paris',
                'country' => 'FR',
                'isDefault' => true,
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects('/account/addresses');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', '123 Rue de la Paix');

        $user = $this->findUser('client@example.com');
        $addresses = $this->entityManager()->getRepository(Address::class)->findBy(['user' => $user]);
        $this->assertCount(1, $addresses);
        $this->assertTrue($addresses[0]->isDefault());
    }

    public function testOnlyOneAddressCanBeDefault(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $address1 = new Address(
            user: $user,
            label: 'Domicile',
            addressLine: '123 Rue de la Paix',
            city: 'Paris',
            postalCode: '75002',
            country: 'FR',
        );
        $address1->setDefault(true);

        $address2 = new Address(
            user: $user,
            label: 'Bureau',
            addressLine: '456 Avenue des Champs',
            city: 'Lyon',
            postalCode: '69001',
            country: 'FR',
        );

        $this->entityManager()->persist($address1);
        $this->entityManager()->persist($address2);
        $this->entityManager()->flush();

        $crawler = $this->client->request('GET', '/account/addresses');
        $setDefaultUrl = $crawler->filter('form[action*="default"]')->first()->attr('action');
        $this->client->request('POST', $setDefaultUrl);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->entityManager()->clear();
        $refreshed1 = $this->entityManager()->getRepository(Address::class)->find($address1->getId());
        $refreshed2 = $this->entityManager()->getRepository(Address::class)->find($address2->getId());
        $this->assertFalse($refreshed1->isDefault());
        $this->assertTrue($refreshed2->isDefault());
    }

    public function testAddressCanBeDeleted(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $address = new Address(
            user: $user,
            label: 'Domicile',
            addressLine: '123 Rue de la Paix',
            city: 'Paris',
            postalCode: '75002',
            country: 'FR',
        );
        $this->entityManager()->persist($address);
        $this->entityManager()->flush();

        $crawler = $this->client->request('GET', '/account/addresses');
        $deleteUrl = $crawler->filter('form[action*="delete"]')->first()->attr('action');

        $this->client->request('POST', $deleteUrl);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $count = $this->entityManager()->getRepository(Address::class)->count(['user' => $user]);
        $this->assertSame(0, $count);
    }

    public function testCannotAccessOtherUserAddress(): void
    {
        $this->login('client@example.com');
        $otherUser = $this->findUser('admin@example.com');

        $address = new Address(
            user: $otherUser,
            label: 'Admin',
            addressLine: '999 Admin St',
            city: 'Paris',
            postalCode: '75000',
            country: 'FR',
        );
        $this->entityManager()->persist($address);
        $this->entityManager()->flush();

        $this->client->request('GET', '/account/addresses/'.$address->getId().'/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddressCanBeEdited(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $address = new Address(
            user: $user,
            label: 'Domicile',
            addressLine: 'Ancienne adresse',
            city: 'Paris',
            postalCode: '75002',
            country: 'FR',
        );
        $this->entityManager()->persist($address);
        $this->entityManager()->flush();

        $crawler = $this->client->request('GET', '/account/addresses/'.$address->getId().'/edit');
        $token = $crawler->filter('input[name="address_form[_token]"]')->attr('value');

        $this->client->request('POST', '/account/addresses/'.$address->getId().'/edit', [
            'address_form' => [
                'label' => 'Domicile',
                'addressLine' => 'Nouvelle adresse',
                'postalCode' => '69001',
                'city' => 'Lyon',
                'country' => 'FR',
                'isDefault' => false,
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects('/account/addresses');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Nouvelle adresse');
    }

    public function testAddressSnapshotInOrderIsolation(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $address = new Address(
            user: $user,
            label: 'Domicile',
            addressLine: '123 Rue de la Paix',
            city: 'Paris',
            postalCode: '75002',
            country: 'FR',
        );
        $address->setDefault(true);
        $this->entityManager()->persist($address);
        $this->entityManager()->flush();

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        $crawler = $this->client->request('GET', '/checkout');
        $token = $crawler->filter('input[name="checkout_form[_token]"]')->attr('value');

        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue de la Paix',
                'postalCode' => '75002',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects();
        $order = $this->entityManager()->getRepository(\App\Entity\Order::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($order);
        $this->assertSame('123 Rue de la Paix', $order->getAddressLine());
        $this->assertSame('75002', $order->getPostalCode());

        $address->setAddressLine('456 Nouvelle Rue');
        $this->entityManager()->flush();

        $this->entityManager()->clear();
        $orderRefreshed = $this->entityManager()->getRepository(\App\Entity\Order::class)->find($order->getId());
        $this->assertSame('123 Rue de la Paix', $orderRefreshed->getAddressLine());
    }
}
