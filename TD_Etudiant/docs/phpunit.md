# PHPUnit

> Framework de tests pour PHP.

## Objectif

PHPUnit permet d'écrire et d'exécuter des **tests automatisés** qui vérifient que le code
fonctionne correctement. Chaque test exécute une partie du code et compare le résultat obtenu
au résultat attendu.

## Pourquoi ici

- **Fiabilité** : les tests détectent les régressions (bugs introduits par de nouvelles modifications).
- **Documentation vivante** : les tests décrivent ce que le code est censé faire.
- **Prérequis au CI** : un pipeline CI sans tests ne sert à rien — PHPUnit est la brique
  fondamentale de la qualité.
- **Phase 2** : dans la phase 2 du cours, les étudiants doivent diagnostiquer quels tests
  échouent pour identifier les bugs injectés.

## Types de tests

| Type | Répertoire | Ce qu'on teste | Exemple |
|------|-----------|----------------|---------|
| **Unitaire** | `tests/Unit/` | Un seul service, isolé | `PromotionService::isOnPromotion()` |
| **Intégration** | `tests/Integration/` | Interaction entre composants | `CategoryProductTest` (relation ManyToMany) |
| **Fonctionnel** | `tests/Functional/` | Parcours complet via HTTP | `LoginTest`, `CheckoutTest` |

### Exemple de test unitaire

```php
class PromotionServiceTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        $this->service = new PromotionService();
    }

    public function testIsOnPromotionDuringPeriod(): void
    {
        $product = new Product();
        $product->setPrice(5000);
        $product->setPromoPrice(3500);
        $product->setPromoStartsAt(new \DateTimeImmutable('2026-01-01'));
        $product->setPromoEndsAt(new \DateTimeImmutable('2026-12-31'));

        $now = new \DateTimeImmutable('2026-06-15');

        $this->assertTrue($this->service->isOnPromotion($product, $now));
    }
}
```

### Exemple de test fonctionnel

```php
class LoginTest extends FunctionalTestCase
{
    public function testLoginSuccess(): void
    {
        $this->browser()
            ->request('GET', '/login')
            ->submitForm('Se connecter', [
                '_username' => 'client@example.com',
                '_password' => 'Client123!',
            ]);

        $this->assertResponseRedirects('/catalog');
    }
}
```

## Comment lancer en local

```bash
# Lancer tous les tests
php vendor/bin/phpunit --no-coverage

# Lancer un fichier de test spécifique
php vendor/bin/phpunit tests/Unit/PromotionServiceTest.php

# Lancer les tests avec un filtre de nom
php vendor/bin/phpunit --filter=testIsOnPromotion

# Lancer avec un format de sortie détaillé
php vendor/bin/phpunit --testdox --no-coverage

# Lancer avec couverture de code (nécessite pcov ou xdebug)
php vendor/bin/phpunit --coverage-text
```

> **Windows** : utilisez `php vendor\bin\phpunit` ou les alias Composer.

## Dans le pipeline CI

```bash
php vendor/bin/phpunit --coverage-clover=var/coverage.xml
```

Le pipeline exécute **tous les tests** (180 tests, 673 assertions). Si un seul test échoue,
le pipeline est **rouge**. Le rapport de couverture (`coverage.xml`) mesure le pourcentage
de code exécuté par les tests.

## Couverture de code

La **couverture** mesure quelle proportion du code source est exécutée par les tests.

```
Tests : 180 tests, 673 assertions
Code couvert : >= 70 % (seuil configuré)
```

| Métrique | Seuil | Description |
|----------|-------|-------------|
| **Line coverage** | ≥ 70 % | Lignes de code exécutées |
| **MSI** (Mutation Score Indicator) | ≥ 80 % | Mutations détectées par les tests |

## Ressources

- [Documentation PHPUnit 12](https://phpunit.readthedocs.io/)
- [Attribute PHPUnit](https://phpunit.readthedocs.io/en/10.0/attributes.html)
- [WebTestCase Symfony](https://symfony.com/doc/current/testing.html)
