# 04 — Pièges courants et comment les éviter

> Les erreurs les plus fréquentes des étudiants qui débutent en CI/CD.

## Erreurs de configuration

### ❌ Oublier le fichier de configuration CI

**Problème** : le pipeline ne se lance jamais.

**Solution** : vérifiez que le fichier existe selon votre plateforme :

| Plateforme | Fichier requis | Emplacement exact |
|-----------|---------------|-------------------|
| GitHub | `.github/workflows/ci.yml` | Dans un dossier `.github/workflows/` |
| GitLab | `.gitlab-ci.yml` | À la racine du projet |

> Pas `github/`, pas `.github/ci.yml`, pas `.gitlab-ci.yaml`.

### ❌ Mauvais nom de fichier

**Problème** : le fichier existe mais la plateforme ne le détecte pas.

**Solution** :
- **GitHub** : le fichier doit s'appeler `ci.yml` (ou `ci.yaml`) dans `.github/workflows/`
- **GitLab** : le fichier doit s'appeler `.gitlab-ci.yml` à la racine

```bash
# Vérifier
ls -la .gitlab-ci.yml          # GitLab
ls -la .github/workflows/      # GitHub
```

### ❌ Syntaxe YAML incorrecte

**Problème** : le pipeline ne se lance pas ou échoue immédiatement.

**Cause courante** : utiliser des **tabulations** au lieu d'**espaces**.

**Solution** :
```yaml
# ✅ Correct (espaces)
jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

# ❌ Incorrect (tabulations)
jobs:
	quality:
		runs-on: ubuntu-latest
```

> **Astuce** : configurez votre éditeur pour afficher les espaces et les tabulations.

### ❌ Indentation incorrecte

**Problème** : le YAML est mal parsé, les étapes ne s'exécutent pas.

**Solution** : chaque niveau de nested utilise **2 espaces**. Comptez soigneusement :

```yaml
jobs:           # niveau 0
  quality:      # niveau 1 (2 espaces)
    steps:      # niveau 2 (4 espaces)
      - name: X # niveau 3 (6 espaces)
        uses: Y # niveau 3 (6 espaces, aligné avec "name")
```

## Erreurs de dépendances

### ❌ `composer install` échoue dans le CI

**Problème** :
```
Error: Could not find a version of package "infection/infection" compatible with
your minimum-stability (stable) setting.
```

**Solution** : vérifiez `composer.json` :
```json
{
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

### ❌ `composer install` est lent dans le CI

**Problème** : le pipeline met 5+ minutes à cause de `composer install`.

**Solution** : activez le cache :

**GitHub Actions** :
```yaml
- name: Cache Composer
  uses: actions/cache@v4
  with:
    path: ~/.cache/composer
    key: composer-${{ hashFiles('composer.lock') }}
```

**GitLab CI** :
```yaml
cache:
  key: "${CI_JOB_REF_SLUG}"
  paths:
    - .composer-cache/
    - vendor/
```

### ❌ Extension PHP manquante dans le CI

**Problème** :
```
Error: Class "SQLite3" not found
```

**Solution** :

**GitHub Actions** :
```yaml
- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.3'
    extensions: sqlite3, intl, mbstring, zip
```

**GitLab CI** :
```yaml
before_script:
  - apt-get update && apt-get install -y libicu-dev libzip-dev
  - docker-php-ext-install intl zip opcache
```

## Erreurs de commande

### ❌ Utiliser des aliases PHP qui n'existent pas

**Problème** : dans le CI, les aliases Composer ne fonctionnent pas toujours.

**Solution** : utilisez le chemin complet :
```bash
# ✅ Fonctionne toujours
php vendor/bin/phpstan analyse

# ⚠️ Peut ne pas fonctionner dans le CI
vendor/bin/phpstan analyse
```

### ❌ Oublier `--no-coverage` pour PHPUnit

**Problème** :
```
Error: No code coverage driver is available
```

**Solution** : ajoutez `--no-coverage` si vous n'avez pas pcov/xdebug :
```yaml
- name: PHPUnit
  run: php vendor/bin/phpunit --no-coverage
```

### ❌ Chemins Windows dans le CI

**Problème** : le CI tourne sur **Ubuntu** (Linux), pas Windows.

```yaml
# ❌ Ne fonctionne pas sur Ubuntu
run: php vendor\bin\phpunit

# ✅ Fonctionne partout
run: php vendor/bin/phpunit
```

> **Astuce** : sur Windows, utilisez le `/` (slash) au lieu de `\` (backslash)
> dans les commandes.

## Erreurs de workflow

### ❌ Utiliser `sudo` inutilement

**Problème** : dans le CI, `sudo` n'est généralement pas nécessaire.

```yaml
# ❌ Inutile
run: sudo php bin/console cache:clear

# ✅ Correct
run: php bin/console cache:clear
```

### ❌ Ne pas attendre le serveur

**Problème** : si vous démarrez un serveur dans le CI, les étapes suivantes
peuvent s'exécuter avant qu'il soit prêt.

**Solution** : utilisez `sleep` ou des healthchecks :
```yaml
- name: Start server
  run: symfony server:start -d

- name: Wait for server
  run: sleep 5

- name: Run tests
  run: php vendor/bin/phpunit --filter=functional
```

### ❌ Oublier le lancement manuel (GitHub)

**Problème** : vous ne pouvez pas lancer manuellement le pipeline depuis GitHub.

**Solution** : ajoutez `workflow_dispatch` :
```yaml
on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
  workflow_dispatch:    # ← permet le lancement manuel
```

### ❌ Ne pas déclencher sur les Merge Requests (GitLab)

**Problème** : le pipeline ne se lance que sur `main`, pas sur les MR.

**Solution** : ajoutez `rules` :
```yaml
quality:
  rules:
    - if: $CI_MERGE_REQUEST_IID
    - if: $CI_COMMIT_BRANCH == $CI_DEFAULT_BRANCH
```

## Erreurs de code qui cassent le CI

### ❌ Ajouter une route sans controller

**Problème** : PHPStan échoue car la route référencée n'existe pas.

### ❌ Oublier `declare(strict_types=1)`

**Problème** : PHPStan peut échouer au niveau 8 si le strict typing est manquant.

### ❌ Modifier une Entity sans migration

**Problème** : Doctrine schema validate échoue :
```
The database schema is not in sync with the current mapping file(s).
```

### ❌ Ajouter un template Twig avec des erreurs

**Problème** : `lint:twig` échoue :
```
Twig\Error\SyntaxError
```

### ❌ Créer un service sans type-hint

**Problème** : PHPStan détecte un argument sans type :
```
Parameter #1 of method __construct() has no type specified.
```

## Checklist avant chaque push

Avant de pousser, exécutez ces commandes dans l'ordre :

```bash
# 1. Style de code
php vendor/bin/php-cs-fixer fix

# 2. Analyse statique
php vendor/bin/phpstan analyse --no-progress

# 3. Tests
php vendor/bin/phpunit --no-coverage

# 4. Lint
php bin/console lint:yaml config
php bin/console lint:twig templates

# 5. Doctrine
php bin/console doctrine:schema:validate --skip-sync

# 6. Fixtures
php bin/console app:fixtures:check

# 7. Si tout est vert, push
git add .
git commit -m "feat: votre message"
git push
```

## En cas de doute

1. **Lisez le log** du pipeline en entier
2. **Copiez la commande** qui échoue
3. **Lancez-la en local**
4. **Corrigez l'erreur**
5. **Push** et vérifiez que le pipeline devient verte

> **Règle d'or** : si le pipeline est rouge, **ne mergez jamais**. Corrigez d'abord.
