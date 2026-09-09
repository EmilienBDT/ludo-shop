> Retour au [README](README.md) — Étape 5 / 11

## Test 5 — Commande console : app:fixtures:check (fonctionnel)

> **Complexité** : faible+. Introduction à `CommandTester` : un outil PHPUnit dédié aux
> commandes Symfony Console. Plus onéreux qu'un GET simple, mais conceptuellement isolé.

### Fichier

`tests/Functional/FixturesCheckCommandTest.php` *(fichier à créer)*

### Objectif

Tester la commande console `app:fixtures:check` qui vérifie la cohérence des données
de démo. Un test de commande = un test atypique qui montre qu'on peut tester autre chose
qu'une page web.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testCommandPassesOnValidFixtures` | Fixtures valides | Exit code 0, message de succès |
| (fourni) `testCommandFailsOnInvalidData` | Produit mature avec âge min incorrect | Exit code 1, message d'erreur |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Command\FixturesCheckCommand;
use App\Entity\Product;
use Symfony\Component\Console\Tester\CommandTester;

class FixturesCheckCommandTest extends FunctionalTestCase
{
    private function getTester(): CommandTester
    {
        return new CommandTester(new FixturesCheckCommand(
            $this->entityManager(),
        ));
    }

    public function testCommandPassesOnValidFixtures(): void
    {
        $tester = $this->getTester();

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('vérifications', $tester->getDisplay());
    }

    public function testCommandFailsOnInvalidData(): void
    {
        // Corrompre les fixtures : produit mature avec âge min trop bas
        $product = $this->repository(Product::class)->findOneBy([]);
        $this->assertNotNull($product);

        $product->setIsMature(true);
        $product->setMinAge(14);
        $this->entityManager()->flush();
        $this->entityManager()->clear();

        $tester = $this->getTester();
        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('mature', $tester->getDisplay());
    }
}
```

### Points clés

- `CommandTester` est un outil PHPUnit dédié aux commandes Symfony Console.
- On construit la commande **manuellement** en lui injectant ses dépendances.
- `tester->execute([])` exécute la commande avec les arguments donnés.
- `tester->getDisplay()` retourne la sortie console (comme un `echo`).
- `$exitCode === 0` = succès, `$exitCode === 1` = échec.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/FixturesCheckCommandTest.php
```

---



---

**Suite :** [06-login.md](06-login.md) | **Retour :** [README](README.md)
