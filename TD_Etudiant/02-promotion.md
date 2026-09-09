> Retour au [README](README.md) — Étape 2 / 11

## Test 2 — PromotionService::isOnPromotion() (unitaire)

> **Complexité** : faible. Un service avec une logique métier, pas de mock nécessaire.
> Plus de scénarios que le test 1, mais même principe : `new Service()` + assertions.

### Fichier

`tests/Unit/PromotionServiceTest.php` *(fichier à créer)*

### Objectif

Tester la logique métier de calcul de promotion **sans base de données**. Le service
`PromotionService` détermine si un produit est en promotion selon la date courante,
le prix promo, et les dates de début/fin.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testReturnsNormalPriceWhenNoPromotion` | Pas de dates promo définies | Prix normal, `false` |
| à créer `testReturnsPromoPriceDuringPeriod` | Date courante dans la période | Prix promo, `true` |
| à créer `testReturnsNormalPriceBeforePromotionPeriod` | Date courante avant le début | Prix normal, `false` |
| à créer `testReturnsNormalPriceAfterPromotionPeriod` | Date courante après la fin | Prix normal, `false` |
| à créer `testPromoPriceEqualToNormalIsNotActive` | Prix promo = prix normal | Prix normal, `false` |
| à créer `testPromoPriceGreaterThanNormalIsNotActive` | Prix promo > prix normal | Prix normal, `false` |
| à créer `testInvertedDatesAreNotActive` | Date de début > date de fin | `false` |
| à créer `testBoundaryStartIsIncluded` | Date = exactement le début de la promo | `true` |
| à créer `testBoundaryEndIsIncluded` | Date = exactement la fin de la promo | `true` |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Service\PromotionService;
use PHPUnit\Framework\TestCase;

class PromotionServiceTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        $this->service = new PromotionService();
    }

    public function testReturnsNormalPriceWhenNoPromotion(): void
    {
        $product = $this->createProduct(50.00);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    // ... autres méthodes

    private function createProduct(float $price, ?float $promoPrice = null): Product
    {
        $product = new Product();
        $product->setName('Test Product')->setPrice($price);

        if (null !== $promoPrice) {
            $product->setPromoPrice($promoPrice);
            $product->setPromoStartsAt(new \DateTimeImmutable('2026-08-01 00:00:00'));
            $product->setPromoEndsAt(new \DateTimeImmutable('2026-08-31 23:59:59'));
        }

        return $product;
    }
}
```

### Points clés

- Pas de `FunctionalTestCase`, pas de BDD, pas de kernel Symfony.
- La méthode `createProduct()` est un helper privé qui fabrique un `Product` en mémoire.
- On teste `getCurrentPrice()` **et** `isOnPromotion()` dans le même test quand c'est logique.
- Les bornes (début et fin inclus) sont critiques : un bug de `>=` vs `>` casse tout.

### Vérification

```bash
php vendor/bin/phpunit tests/Unit/PromotionServiceTest.php
```

---



---

**Suite :** [03-cart-service.md](03-cart-service.md) | **Retour :** [README](README.md)
