> Retour au [README](README.md) — Étape 7 / 11

## Test 7 — Catalogue : affichage + filtres (fonctionnel)

> **Complexité** : moyenne+. Introduction aux fixtures Doctrine (`persist`/`flush`), au
> login multi-rôles, et aux assertions de contenu HTML avec filtres query params.

### Fichier

`tests/Functional/CatalogTest.php` *(fichier à créer)*

### Objectif

Tester que la page catalogue affiche les produits, que les filtres fonctionnent,
et que les produits inactifs/matures sont correctement masqués.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testCatalogPageDisplaysProducts` | GET /products | 200, Catan visible |
| à créer `testFilterByCategory` | GET /categories/{slug} | Seuls les produits de la catégorie |
| (fourni) `testInactiveProductIsHidden` | Produit inactif en BDD | Non visible dans le catalogue |
| (fourni) `testMatureProductHiddenForMinor` | Login minor + GET /products/{id} | 404 (produit masqué) |
| (fourni) `testMatureProductVisibleForAdult` | Login client + GET /products/{id} | 200, produit visible |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;

class CatalogTest extends FunctionalTestCase
{
    public function testCatalogPageDisplaysProducts(): void
    {
        $this->client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testInactiveProductIsHidden(): void
    {
        $inactive = new Product();
        $inactive->setName('Produit Test');
        $inactive->setReference('TEST-999');
        $inactive->setPrice(10.00);
        $inactive->setStock(5);
        $inactive->setIsActive(false);
        $this->entityManager()->persist($inactive);
        $this->entityManager()->flush();

        $this->client->request('GET', '/products');

        $this->assertSelectorTextNotContains('body', 'Produit Test');
    }

    public function testMatureProductHiddenForMinor(): void
    {
        $this->login('minor@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'LIM-001']);
        $this->assertNotNull($product);

        $this->client->request('GET', '/products/'.$product->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMatureProductVisibleForAdult(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'LIM-001']);
        $this->assertNotNull($product);

        $this->client->request('GET', '/products/'.$product->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Limite Limite');
    }
}
```

### Points clés

- `assertSelectorTextContains('body', 'Catan')` vérifie que le texte est dans le HTML rendu.
- `$this->entityManager()->persist()` + `flush()` insère directement en BDD pour le test.
- `$this->entityManager()->clear()` vide le cache Doctrine pour re-lire depuis la BDD.
- Les produits matures sont paginés (9 par page). Pour les tester, ciblez directement
  `/products/{id}` (retourne 404 si le produit est masqué pour le rôle connecté).
- La route des catégories est `/categories/{slug}` — vérifiez `src/Controller/CategoryController.php`.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/CatalogTest.php
```

---



---

**Suite :** [08-cart.md](08-cart.md) | **Retour :** [README](README.md)
