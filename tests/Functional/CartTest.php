<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Service\CartService;

class CartTest extends FunctionalTestCase
{
    public function testAddProductToCart(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testCartQuantityIsUpdated(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Catan');

        $cartItem = $this->repository(CartItem::class)->findOneBy(['product' => $product]);
        $this->assertNotNull($cartItem);
        $this->assertSame(1, $cartItem->getQuantity());

        $this->client->request('POST', '/cart/items/'.$cartItem->getId().'/update', [
            'quantity' => 3,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $cartItem = $this->repository(CartItem::class)->findOneBy(['product' => $product]);
        $this->assertNotNull($cartItem);
        $this->assertSame(3, $cartItem->getQuantity());
    }

    public function testRemoveProductFromCart(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Catan');

        $cartItem = $this->repository(CartItem::class)->findOneBy(['product' => $product]);
        $this->assertNotNull($cartItem);
        $this->assertSame(1, $cartItem->getQuantity());

        $this->client->request('POST', '/cart/items/'.$cartItem->getId().'/remove');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $cartItem = $this->repository(CartItem::class)->findOneBy(['product' => $product]);
        $this->assertNull($cartItem);
    }

    public function testCartShowsCorrectTotal(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 2);

        $this->client->request('GET', '/cart');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#cart-total');
    }
}
