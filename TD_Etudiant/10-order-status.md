> Retour au [README](README.md) — Étape 10 / 11

## Test 10 — Changement de statut de commande par l'admin (fonctionnel)

> **Complexité** : très élevée. Combine **tout** : le parcours checkout (test 9), la
> sécurité (403), l'historique en BDD, et la vérification d'emails via le profiler.
> C'est le test le plus complet du projet.

### Fichier

`tests/Functional/OrderStatusTest.php` *(fichier à créer)*

### Objectif

Tester le workflow de modération : seul un admin peut changer le statut d'une commande,
l'historique est créé, et un email de notification est envoyé.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testAdminCanChangeStatus` | Login admin + POST changement de statut | Statut mis à jour |
| (fourni) `testClientCannotChangeStatus` | Login client + POST changement de statut | 403 Forbidden |
| (fourni) `testStatusChangeCreatesHistoryEntry` | Après changement | Entrée dans `OrderStatusHistory` |
| (fourni) `testStatusChangeSendsEmail` | Après changement | Email envoyé (profiler) |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use App\Entity\Product;
use App\Service\CartService;

class OrderStatusTest extends FunctionalTestCase
{
    private function createPaidOrder(): Order
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

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
        $this->client->request('POST', '/orders/'.$order->getId().'/pay');
        $this->entityManager()->clear();

        return $this->repository(Order::class)->find($order->getId());
    }

    private function changeStatus(Order $order, string $status): void
    {
        $crawler = $this->client->request('GET', '/admin/orders/'.$order->getId());
        $form = $crawler->filter('form[action*="status"]');
        $this->assertGreaterThan(0, $form->count(), 'Le formulaire de changement de statut est introuvable.');

        $this->client->submit($form->form(), [
            'order_status_form[status]' => $status,
        ]);

        $this->assertResponseRedirects();
    }

    public function testAdminCanChangeStatus(): void
    {
        $order = $this->createPaidOrder();

        $this->login('admin@example.com');
        $this->changeStatus($order, 'shipped');

        $this->entityManager()->clear();

        $updated = $this->repository(Order::class)->find($order->getId());
        $this->assertSame('shipped', $updated->getStatus()->value);
    }

    public function testClientCannotChangeStatus(): void
    {
        $order = $this->createPaidOrder();

        $this->login('client@example.com');

        $this->client->request('POST', '/admin/orders/'.$order->getId().'/status', [
            'order_status_form[status]' => 'shipped',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testStatusChangeCreatesHistoryEntry(): void
    {
        $order = $this->createPaidOrder();

        $this->login('admin@example.com');
        $this->changeStatus($order, 'shipped');

        $this->entityManager()->clear();

        $history = $this->repository(OrderStatusHistory::class)->findBy(
            ['order' => $this->repository(Order::class)->find($order->getId())],
            ['changedAt' => 'ASC'],
        );

        $this->assertGreaterThanOrEqual(1, count($history));
    }

    public function testStatusChangeSendsEmail(): void
    {
        $order = $this->createPaidOrder();

        $this->login('admin@example.com');
        $this->changeStatus($order, 'shipped');

        $profile = $this->client->getProfile();
        $this->assertNotNull($profile);
        $collector = $profile->getCollector('mailer');
        $messages = $collector->getEvents()->getMessages();

        $this->assertGreaterThanOrEqual(1, count($messages));
    }
}
```

### Points clés

- La méthode privée `createPaidOrder()` est **réutilisée** dans plusieurs tests (D.R.Y.).
- La méthode privée `changeStatus()` utilise `$this->client->submit()` pour soumettre
  le formulaire correctement — le champ s'appelle `order_status_form[status]` (pas `status`).
- `$this->assertResponseStatusCodeSame(403)` vérifie que l'accès est interdit.
- Les tests d'email utilisent le **profiler Symfony** (`getProfile()->getCollector('mailer')`).
- `$this->entityManager()->clear()` est **obligatoire** après un flush pour re-lire depuis la BDD.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/OrderStatusTest.php
```

---



---

**Suite :** [11-promo-panier.md](11-promo-panier.md) | **Retour :** [README](README.md)
