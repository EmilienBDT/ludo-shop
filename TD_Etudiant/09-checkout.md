> Retour au [README](README.md) — Étape 9 / 11

## Test 9 — Checkout : parcours complet de commande (fonctionnel)

> **Complexité** : élevée. Parcours en 4 étapes : panier → checkout (adresse) → paiement
> → confirmation. Chaque étape dépend de la précédente. C'est le test business-critical.

### Fichier

`tests/Functional/CheckoutTest.php` *(fichier à créer)*

### Objectif

Tester le parcours complet : panier → checkout (adresse) → paiement → confirmation.
C'est le test le plus business-critical de l'application.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| à créer `testCheckoutPageRequiresCart` | GET /checkout sans panier | Redirection ou message d'erreur |
| (fourni) `testCheckoutCreatesOrder` | POST /checkout avec adresse | Order créée avec statut `pending` |
| (fourni) `testPaymentCreatesPaidOrder` | POST /orders/{id}/pay | Statut passe à `paid` |
| à créer `testConfirmationPageIsDisplayed` | Après paiement | Page de confirmation avec numéro |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

> Ce test est le plus complexe. Il enchaîne plusieurs étapes.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

class CheckoutTest extends FunctionalTestCase
{
    public function testCheckoutCreatesOrder(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        // 1. Ajouter un produit au panier
        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        // 2. Remplir le formulaire de checkout
        $crawler = $this->client->request('GET', '/checkout');
        $token = $crawler->filter('input[name="checkout_form[_token]"]')->attr('value');

        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        // 3. Vérifier que l'order est créée
        $this->assertResponseRedirects();
        $order = $this->repository(Order::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->getStatus()->value);
    }

    public function testPaymentCreatesPaidOrder(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        // Setup panier + checkout (comme ci-dessus)
        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        $crawler = $this->client->request('GET', '/checkout');
        $token = $crawler->filter('input[name="checkout_form[_token]"]')->attr('value');

        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        $order = $this->repository(Order::class)->findOneBy(['user' => $user]);

        // 4. Payer la commande
        $this->client->request('POST', '/orders/'.$order->getId().'/pay');

        $this->assertResponseRedirects();
        $this->entityManager()->clear();

        $paidOrder = $this->repository(Order::class)->find($order->getId());
        $this->assertSame('paid', $paidOrder->getStatus()->value);
    }
}
```

### Points clés

- Ce test reprend le **même patron** que `OrderConfirmationEmailTest::createPaidOrder()`.
- `$this->client->getContainer()->get(CartService::class)` récupère le service depuis le container.
- `$this->entityManager()->clear()` vide le cache Doctrine avant de re-lire l'entité.
- Le CSRF token est **obligatoire** pour le formulaire de checkout.
- Si un test est trop long, extrayez la logique de setup dans une méthode privée.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/CheckoutTest.php
```

---



---

**Suite :** [10-order-status.md](10-order-status.md) | **Retour :** [README](README.md)
