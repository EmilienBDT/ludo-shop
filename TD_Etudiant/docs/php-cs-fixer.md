# PHP-CS-Fixer

> Outil de formatage automatique du code PHP — applique le style PSR-12.

## Objectif

PHP-CS-Fixer **reformatte automatiquement** le code PHP pour qu'il respecte les conventions
de style (PSR-12 : indentations, espaces, sauts de ligne, ordre des imports, etc.).

## Pourquoi ici

- **Cohérence** : tout le projet utilise le même style, même si plusieurs personnes (ou agents)
  contribuent.
- **Lisibilité** : un code uniforme est plus facile à lire et à maintenir.
- **CI gate** : le pipeline vérifie que le code est correctement formaté avant de continuer.
- **Gain de temps** : au lieu de corriger le style manuellement, PHP-CS-Fixer le fait
  automatiquement.

## Configuration

Le fichier `.php-cs-fixer.dist.php` définit les règles :

```php
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@Symfony' => true,
        'ordered_imports' => true,
        'no_unused_imports' => true,
        // ... autres règles
    ])
    ->setFinder($finder);
```

## Comment lancer en local

```bash
# Vérifier le style (sans modifier)
php vendor/bin/php-cs-fixer fix --dry-run --diff

# Corriger automatiquement le style
php vendor/bin/php-cs-fixer fix

# Corriger un fichier spécifique
php vendor/bin/php-cs-fixer fix src/Service/CartService.php

# Voir le cache (améliore les performances)
php vendor/bin/php-cs-fixer fix --dry-run -vvv
```

> **Conseil** : exécutez `php vendor/bin/php-cs-fixer fix` avant chaque commit pour
> éviter les erreurs de style dans le pipeline.

## Dans le pipeline CI

```bash
php vendor/bin/php-cs-fixer fix --dry-run --diff
```

Le flag `--dry-run` empêche toute modification. Le flag `--diff` affiche les différences.
Si le code n'est pas conforme, le pipeline est **rouge**.

## Règles principales PSR-12

| Règle | Description |
|-------|-------------|
| Indentation | 4 espaces (pas de tabulations) |
| Largeur de ligne | 120 caractères maximum |
| Espaces | Après les mots-clés (`if`, `for`), pas avant les parenthèses |
| Imports | `use` triés, un par ligne |
| Braces | `{` sur la même ligne que la déclaration |
| Strings | Guillemets simples pour les strings simples |

## Ressources

- [Documentation PHP-CS-Fixer](https://cs.symfony.com/)
- [Liste des règles](https://cs.symfony.com/doc/rules/index.html)
- [PSR-12](https://www.php-fig.org/psr/psr-12/)
