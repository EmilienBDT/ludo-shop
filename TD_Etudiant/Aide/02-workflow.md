# 02 — Écrire un workflow GitHub Actions pas à pas

> Étape 2 : créer le fichier qui définit votre pipeline CI.

## Qu'est-ce qu'un workflow ?

Un workflow est un fichier **YAML** que GitHub exécute automatiquement. Il définit :
- **Quand** il s'exécute (à chaque push, à chaque PR, etc.)
- **Sur quelle machine** (Ubuntu, Windows, etc.)
- **Quelles étapes** exécuter (installer, tester, analyser…)

## Créer le fichier

Créez ce répertoire et ce fichier dans votre projet :

```
.github/
└── workflows/
    └── ci.yml
```

**Astuce** : créez le répertoire avec la commande :
```bash
mkdir -p .github/workflows
```

Puis créez le fichier `.github/workflows/ci.yml`.

## Anatomy d'un workflow

Voici le workflow minimal pour LudoShop, avec des explications ligne par ligne :

```yaml
# Nom du workflow (affiché sur GitHub)
name: CI

# Quand le workflow se déclenche
on:
  push:
    branches: [main]        # Quand on push sur main
  pull_request:
    branches: [main]        # Quand on ouvre une PR vers main

# Les étapes du pipeline
jobs:
  quality:                    # Nom du job (on peut en avoir plusieurs)
    name: Quality checks      # Nom affiché dans l'interface
    runs-on: ubuntu-latest    # Machine virtuelle Ubuntu (gratuit)

    steps:
      # Étape 1 : récupérer le code
      - name: Checkout code
        uses: actions/checkout@v4

      # Étape 2 : installer PHP
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: sqlite3, intl, mbstring, zip
          coverage: pcov

      # Étape 3 : installer Composer (dépendances PHP)
      - name: Install Composer dependencies
        run: composer install --no-progress --prefer-dist

      # Étape 4 : lancer PHPStan
      - name: PHPStan analyse
        run: php vendor/bin/phpstan analyse --no-progress

      # Étape 5 : lancer PHPUnit
      - name: PHPUnit tests
        run: php vendor/bin/phpunit --no-coverage
```

## Chaque ligne expliquée

### `on:` — Le déclencheur

```yaml
on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
```

Le pipeline se lance **deux fois** :
- Quand quelqu'un **push** sur `main`
- Quand quelqu'un ouvre une **Pull Request** vers `main`

C'est la configuration standard. Vous pouvez aussi ajouter :
```yaml
  workflow_dispatch:  # Permet de lancer manuellement depuis GitHub
```

### `jobs:` — Les travaux

Un **job** est un ensemble d'étapes qui s'exécutent sur la **même machine**. On peut
avoir plusieurs jobs parallèles :

```yaml
jobs:
  lint:
    runs-on: ubuntu-latest
    steps: ...

  tests:
    runs-on: ubuntu-latest
    steps: ...
```

### `steps:` — Les étapes

Chaque étape fait **une seule chose**. Il y a deux types :

#### Étape avec `uses` (action pré-fabriquée)

```yaml
- name: Checkout code
  uses: actions/checkout@v4
```

`actions/checkout@v4` est une **action** créée par GitHub qui clone votre dépôt.
C'est comme faire `git clone` automatiquement.

#### Étape avec `run` (commande shell)

```yaml
- name: Install dependencies
  run: composer install --no-progress --prefer-dist
```

C'est une commande terminal classique. Vous pouvez chaîner plusieurs commandes :

```yaml
- name: Run all checks
  run: |
    composer install --no-progress
    php bin/console lint:yaml config
    php vendor/bin/phpstan analyse --no-progress
```

> **Note** : le `|` permet d'écrire plusieurs lignes dans un seul `run`.

## Exemple complet pour LudoShop

Voici le workflow complet adapté au projet :

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
  workflow_dispatch:

jobs:
  quality:
    name: Quality checks
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP 8.3
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: sqlite3, intl, mbstring, zip
          coverage: pcov

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --no-progress --prefer-dist

      - name: Lint YAML config
        run: php bin/console lint:yaml config

      - name: Lint Twig templates
        run: php bin/console lint:twig templates

      - name: Lint container
        run: php bin/console lint:container --env=prod

      - name: PHP-CS-Fixer
        run: php vendor/bin/php-cs-fixer fix --dry-run --diff

      - name: PHPStan analyse
        run: php vendor/bin/phpstan analyse --no-progress

      - name: PHPUnit tests
        run: php vendor/bin/phpunit --no-coverage

      - name: Composer audit
        run: composer audit --no-interaction

      - name: Doctrine schema validate
        run: php bin/console doctrine:schema:validate --skip-sync

      - name: Fixtures check
        run: php bin/console app:fixtures:check
```

## Pousser le workflow

```bash
git add .github/workflows/ci.yml
git commit -m "ci: add GitHub Actions workflow"
git push
```

Allez sur GitHub → onglet **"Actions"** pour voir la pipeline s'exécuter en temps réel.

## Débuguer

Si le pipeline échoue :
1. Cliquez sur le **commit** qui a déclenché le pipeline
2. Cliquez sur le **job** en erreur (❌ rouge)
3. Déroulez l'étape en erreur pour lire le **log**
4. Reproduisez la commande en local et corrigez

→ Voir le [03-deboguer-pipeline.md](03-deboguer-pipeline.md) pour plus de détails.

## Et ensuite ? Bloquer le merge si la CI est rouge

Ne laissez pas un merge passer alors que la CI est rouge. Configurez la protection de branche :

* **Sur GitHub :** suivez le guide [06-protection-github.md](06-protection-github.md) — pour **github.com** et **GitHub Enterprise** (IUT)
* **Sur GitLab :** suivez le pendant [06b-protection-gitlab.md](06b-protection-gitlab.md) — pour **gitlab.com** et **GitLab self-hosted IUT**
