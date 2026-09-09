<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;
use App\Service\CartService;

/**
 * Covers UC-20 / RG-31: wishlist add/remove, no duplicates,
 * mature product blocked for minors, toggle JSON, add-all-to-cart,
 * wishlist page 3-section layout, and button visibility.
 */
class WishlistTest extends FunctionalTestCase
{
    private Product $catan;
    private Product $limite;
    private Product $dixit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catan = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($this->catan);

        $this->limite = $this->repository(Product::class)->findOneBy(['reference' => 'LIM-001']);
        $this->assertNotNull($this->limite);

        $this->dixit = $this->repository(Product::class)->findOneBy(['reference' => 'DIX-001']);
        $this->assertNotNull($this->dixit);
    }

    public function testAddToWishlist(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'ajouté à votre liste de souhaits');

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testRemoveFromWishlist(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/wishlist/remove/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Catan');
    }

    public function testDuplicateAddShowsFlash(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'déjà dans votre liste de souhaits');
    }

    public function testMatureProductBlockedForMinor(): void
    {
        $this->login('minor@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->limite->getId());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'majeur');

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Limite Limite');
    }

    public function testWishlistRequiresLogin(): void
    {
        $this->client->request('GET', '/wishlist');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location'));
    }

    public function testToggleRequiresLogin(): void
    {
        $this->client->request('POST', '/wishlist/toggle/'.$this->catan->getId());

        $this->assertResponseRedirects();
    }

    public function testToggleAddsProduct(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/toggle/'.$this->catan->getId());

        $this->assertResponseIsSuccessful();
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($json['added']);
        $this->assertSame('Catan', $json['productName']);
    }

    public function testToggleRemovesProduct(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/wishlist/toggle/'.$this->catan->getId());

        $this->assertResponseIsSuccessful();
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($json['added']);
        $this->assertSame('Catan', $json['productName']);
    }

    public function testToggleMatureProductReturns400ForMinor(): void
    {
        $this->login('minor@example.com');

        $this->client->request('POST', '/wishlist/toggle/'.$this->limite->getId());

        $this->assertResponseStatusCodeSame(400);
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $json);
    }

    public function testAddAllToCartAddsAvailableProducts(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();
        $this->client->request('POST', '/wishlist/add/'.$this->dixit->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/wishlist/add-all-to-cart');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', '2 produit(s) ajouté(s) au panier');
    }

    public function testAddAllToCartFlashWhenNoAvailable(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/wishlist/add-all-to-cart');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'Aucun produit disponible');
    }

    public function testWishlistPageInStockSectionVisible(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
        $this->assertSelectorTextContains('body', 'Ajouter la wishlist au panier');
    }

    public function testWishlistPageOutOfStockSectionVisible(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'En attente de re-stock');
    }

    public function testWishlistPageInCartSectionVisible(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $this->catan);

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Déjà dans le panier');
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testAddToCartButtonVisibleForInStockWishlistProduct(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();

        $button = $this->client->getCrawler()->filter('form[action*="/cart/add/"] button');
        $this->assertGreaterThan(0, $button->count());
    }

    public function testStockAlertButtonVisibleForOOSWishlistProduct(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Plus de stock, me prévenir');
    }

    public function testEmptyWishlistShowsMessage(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Votre liste de souhaits est vide');
    }

    private function setStock(Product $product, int $stock): void
    {
        $product->setStock($stock);
        $this->entityManager()->flush();
    }
}
