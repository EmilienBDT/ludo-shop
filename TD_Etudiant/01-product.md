> Retour au [README](README.md) — Étape 1 / 11

## Test 1 — Produit : vérification d'état (unitaire)

> **Complexité** : très faible. Aucune dépendance, aucune BDD, aucune injection.
> C'est le test le plus simple du projet — idéal pour comprendre la mécanique.

### Fichier

`tests/Unit/ProductTest.php` *(fichier à créer)*

### Objectif

Tester les méthodes simples d'une Entity `Product` : `isMature()`, `isAvailable()`,
etc. Ces méthodes contiennent de la logique conditionnelle qui peut casser silencieusement.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testIsMatureReturnsTrueWhenFlagged` | `isMature = true` | `true` |
| à créer `testIsMatureReturnsFalseByDefault` | `isMature = false` | `false` |
| (fourni) `testIsAvailableWhenActiveAndInStock` | `isActive = true, stock > 0` | `true` |
| à créer `testIsNotAvailableWhenInactive` | `isActive = false` | `false` |
| à créer `testIsNotAvailableWhenOutOfStock` | `stock = 0` | `false` |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testIsMatureReturnsTrueWhenFlagged(): void
    {
        $product = new Product();
        $product->setIsMature(true);

        $this->assertTrue($product->isMature());
    }

    public function testIsAvailableWhenActiveAndInStock(): void
    {
        $product = new Product();
        $product->setIsActive(true);
        $product->setStock(10);

        $this->assertTrue($product->isAvailable());
    }

    // ... autres méthodes
}
```

### Points clés

- C'est le test le plus simple : aucune injection de dépendance, aucune BDD.
- Idéal pour commencer et comprendre la mécanique de PHPUnit.
- Si `isAvailable()` n'existe pas sur l'Entity, vérifiez les méthodes existantes
  dans `src/Entity/Product.php`.

### Vérification

```bash
php vendor/bin/phpunit tests/Unit/ProductTest.php
```

---



---

**Suite :** [02-promotion.md](02-promotion.md) | **Retour :** [README](README.md)
