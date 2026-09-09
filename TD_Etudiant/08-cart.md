> Retour au [README](README.md) — Étape 8 / 11

## Test 8 — Panier : ajout, modification, suppression (fonctionnel)

> **Complexité** : moyenne+. Introduction à la **session utilisateur** : le panier persiste
> entre les requêtes. Plusieurs étapes POST avec des dépendances entre elles.

### Fichier

`tests/Functional/CartTest.php` *(fichier à créer)*

### Objectif

Tester le parcours utilisateur du panier : ajouter un produit, modifier la quantité,
supprimer un article. C'est un parcours multi-étapes qui implique la session.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testAddProductToCart` | POST /cart/add/{id} avec quantité | Redirection, produit dans le panier |
| à créer `testCartQuantityIsUpdated` | POST /cart/items/{id}/update | Nouvelle quantité affichée |
| à créer `testRemoveProductFromCart` | POST /cart/items/{id}/remove | Produit plus présent |
| (fourni) `testCartShowsCorrectTotal` | Après ajout de produits | Total = somme des prix × quantités |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

> Le patron dépend de la route de votre `CartController`. Adaptez les URLs.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

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
```

### Points clés

- Le panier est en **session** : l'utilisateur doit être connecté.
- La route d'ajout est `POST /cart/add/{id}` où `{id}` est l'identifiant du produit (pas un champ de formulaire).
- La route de mise à jour est `POST /cart/items/{id}/update` où `{id}` est l'identifiant de l'item.
- La route de suppression est `POST /cart/items/{id}/remove`.
- `$this->repository(Product::class)->findOneBy(...)` cherche directement en BDD.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/CartTest.php
```

---



---

**Suite :** [09-checkout.md](09-checkout.md) | **Retour :** [README](README.md)
