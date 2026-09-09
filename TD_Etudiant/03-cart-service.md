> Retour au [README](README.md) — Étape 3 / 11

## Test 3 — Panier : calcul du total (unitaire)

> **Complexité** : moyenne. Introduction aux **stubs** : on simule les dépendances
> Doctrine pour isoler le service. C'est le concept le plus difficile des tests unitaires.

### Fichier

`tests/Unit/CartServiceTest.php` *(fichier à créer)*

### Objectif

Tester le calcul du total du panier. `CartService::getTotal()` additionne
`(prix × quantité)` pour chaque item. C'est la base de tout e-commerce.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testEmptyCartReturnsZero` | Panier vide | `0` |
| (fourni) `testSingleItemReturnsCorrectTotal` | 1 produit × 1 | Prix du produit |
| à créer `testMultipleItems` | 2 produits différents | Somme des sous-totaux |
| à créer `testQuantityMultiplier` | 1 produit × 3 | Prix × 3 |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

> **Note** : `CartService` utilise Doctrine (`EntityManagerInterface`).
> En mode unitaire, il faut ** stubber cette dépendance :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $this->service = new CartService($em);
    }

    public function testEmptyCartReturnsZero(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $this->assertSame(0.0, $this->service->getTotal($cart));
    }

    public function testSingleItemReturnsCorrectTotal(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);
        $cart->addItem($item);

        $this->assertSame(25.00, $this->service->getTotal($cart));
    }

    // ... autres méthodes
}
```

### Points clés

- `Cart` exige un `User` en constructeur — il faut un stub (`createStub`) pour le fabriquer.
- `CartItem` exige un `Product` en constructeur.
- La méthode de calcul s'appelle `getTotal()` (pas `calculateSubtotal()`).
- Utilisez `createStub()` (et non `createMock()`) quand vous n'avez pas d'attente
  sur les méthodes de l'objet fictif — sinon PHPUnit émet des notices.
- `CartService` ne prend qu'un seul argument : `EntityManagerInterface $em`
  (pas de `ProductRepository`).

### Vérification

```bash
php vendor/bin/phpunit tests/Unit/CartServiceTest.php
```

---



---

**Suite :** [04-schema-validation.md](04-schema-validation.md) | **Retour :** [README](README.md)
