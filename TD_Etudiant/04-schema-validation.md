> Retour au [README](README.md) — Étape 4 / 11

## Test 4 — Validation du schéma Doctrine (fonctionnel)

> **Complexité** : très faible pour un test fonctionnel. Une seule assertion, pas de HTTP,
> pas de fixtures à manipuler. Idéal pour découvrir `FunctionalTestCase` sans se compliquer.

### Fichier

`tests/Functional/SchemaValidationTest.php` *(fichier à créer)*

### Objectif

Vérifier que le schéma de la base de données est synchronisé avec les mappings Doctrine.
Ce test n'est pas un test de code métier mais un test de **cohérence infrastructure** :
si un étudiant modifie une Entity sans créer de migration, ce test le détecte.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testSchemaIsSynchronized` | Schéma BDD vs mappings Doctrine | Pas d'erreur |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;

class SchemaValidationTest extends FunctionalTestCase
{
    public function testSchemaIsSynchronized(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->entityManager();

        $validator = new SchemaValidator($em);

        $this->assertTrue(
            $validator->schemaInSyncWithMetadata(),
            'Le schéma Doctrine n\'est pas synchronisé avec les métadonnées.',
        );
    }
}
```

### Points clés

- `SchemaValidator::schemaInSyncWithMetadata()` retourne `true` si le schéma est OK,
  `false` s'il est désynchronisé.
- Ce test est **très rapide** (pas de BDD, juste une vérification de métadonnées).
- Dans le pipeline CI, cette vérification est aussi faite via
  `php bin/console doctrine:schema:validate` — mais l'avantage du test PHPUnit
  est qu'il apparaît dans le rapport de couverture et peut être exécuté partiellement.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/SchemaValidationTest.php
```

---



---

**Suite :** [05-fixtures-check.md](05-fixtures-check.md) | **Retour :** [README](README.md)
