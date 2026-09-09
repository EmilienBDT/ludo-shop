# PHPStan

> Analyse statique pour PHP — détecte les erreurs sans exécuter le code.

## Objectif

PHPStan examine le code PHP **sans l'exécuter** et cherche les erreurs de typage, les
appels de méthodes inexistantes, les variables non définies, les types incorrects, et
d'autres problèmes potentiels.

## Pourquoi ici

- **Détection précoce** : PHPStan trouve des erreurs que PHP ne détecterait qu'à l'exécution.
- **Qualité du code** : un code bien typé réduit les bugs et améliore la maintenabilité.
- **Pilier du CI** : dans la phase 2, l'enseignant peut introduire des erreurs de type
  que PHPStan détecte instantanément.
- **Niveau 8** : le niveau le plus strict, qui vérifie les types retournés, les paramètres,
  les tableaux, et bien plus.

## Les niveaux de PHPStan

| Niveau | Vérification | Sévérité |
|--------|-------------|----------|
| 0 | Variables non définies, fonctions inconnues | Bas |
| 1-4 | Types de paramètres, de retour, propriétés | Moyen |
| 5-6 | Types de tableaux, génériques | Élevé |
| 7-8 | Tous les types, analyse approfondie | Très élevé |

**LudoShop utilise le niveau 8** — le plus strict.

## Configuration

Le fichier `phpstan.neon` définit les règles d'analyse :

```neon
parameters:
    level: 8
    paths:
        - src
    tmpDir: var/phpstan
```

## Comment lancer en local

```bash
# Analyse complète
php vendor/bin/phpstan analyse --no-progress

# Avec affichage des erreurs
php vendor/bin/phpstan analyse

# Analyser un fichier spécifique
php vendor/bin/phpstan analyse src/Service/CartService.php

# Analyser avec un niveau spécifique (pour tester)
php vendor/bin/phpstan analyse --level=5 src/
```

## Dans le pipeline CI

```bash
php vendor/bin/phpstan analyse --no-progress
```

Si PHPStan trouve **la moindre erreur**, le pipeline est **rouge**. Il n'y a pas de tolérance :
0 erreur au niveau 8.

## Erreurs courantes

| Identifiant | Description | Exemple |
|-------------|-------------|---------|
| `return.type` | Type de retour incorrect | Fonction déclarée `int` mais retourne `string` |
| `argument.type` | Argument de mauvais type | Passer un `User` là où un `UserInterface` est attendu |
| `missingType.iterableValue` | Type non précisé sur un tableau | `array` au lieu de `list<string>` |
| `property.onlyWritten` | Propriété jamais lue | Un attribut défini mais jamais utilisé |
| `arguments.count` | Nombre d'arguments incorrect | Trop/few arguments dans un appel de fonction |

## Ressources

- [Documentation PHPStan](https://phpstan.org/user-guide/getting-started)
- [Identifiants d'erreurs](https://phpstan.org/error-identifiers/)
- [PHPStan pour Symfony](https://github.com/phpstan/phpstan-symfony)
