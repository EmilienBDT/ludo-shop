# 02b — Écrire un fichier `.gitlab-ci.yml` pas à pas

> Étape 2 : créer le fichier qui définit votre pipeline CI sur GitLab.

## Qu'est-ce qu'un fichier CI ?

Un fichier **YAML** à la racine du projet que GitLab exécute automatiquement. Il définit :
- **Quand** il s'exécute (à chaque push, à chaque MR, etc.)
- **Sur quelle machine** (l'image Docker)
- **Quelles étapes** exécuter (installer, tester, analyser…)

## Créer le fichier

Créez le fichier `.gitlab-ci.yml` **à la racine** du projet (pas dans un sous-dossier) :

```
ludo-shop/
├── .gitlab-ci.yml      ← ici
├── composer.json
├── src/
└── ...
```

> **Important** : le fichier doit s'appeler exactement `.gitlab-ci.yml` (avec le point
> au début). Pas `gitlab-ci.yml`, pas `.gitlab-ci.yaml`.

## Anatomie d'un fichier CI GitLab

Voici le fichier minimal pour LudoShop, avec des explications ligne par ligne :

```yaml
# Les étapes du pipeline (exécutées dans l'ordre)
stages:
  - quality

# Variables disponibles dans tout le pipeline
variables:
  COMPOSER_CACHE_DIR: "$CI_PROJECT_DIR/.composer-cache"
  PHP_VERSION: "8.3"

# Cache entre les exécutions du pipeline
cache:
  key: "${CI_JOB_REF_SLUG}"
  paths:
    - .composer-cache/
    - vendor/

# Le job principal
quality:
  stage: quality
  image: php:${PHP_VERSION}

  before_script:
    - apt-get update && apt-get install -y git unzip libicu-dev libzip-dev
    - docker-php-ext-install intl zip opcache
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --prefer-dist --no-progress --no-interaction

  script:
    - php vendor/bin/phpstan analyse --no-progress
    - php vendor/bin/phpunit --no-coverage
```

## Chaque bloc expliqué

### `stages:` — Les étapes du pipeline

```yaml
stages:
  - quality
```

GitLab exécute les jobs par **stage**. Ici on n'a qu'un seul stage `quality`.
On pourrait en ajouter d'autres :

```yaml
stages:
  - quality       # Vérifications de code
  - deploy        # Déploiement (plus tard)
```

### `variables:` — Les variables

```yaml
variables:
  COMPOSER_CACHE_DIR: "$CI_PROJECT_DIR/.composer-cache"
  PHP_VERSION: "8.3"
```

Ce sont des variables disponibles dans toutes les commandes. `$CI_PROJECT_DIR`
est une variable GitLab qui pointe vers la racine du projet.

### `cache:` — Le cache

```yaml
cache:
  key: "${CI_JOB_REF_SLUG}"
  paths:
    - .composer-cache/
    - vendor/
```

GitLab sauvegarde ces dossiers entre les exécutions. Le 2e run sera plus rapide
car les dépendances Composer seront déjà installées.

### `image:` — La machine

```yaml
quality:
  image: php:8.3
```

C'est l'image Docker utilisée pour exécuter le job. Ici, une image PHP 8.3
officielle. GitLab lance un conteneur Docker avec cette image.

### `before_script:` — Installation

```yaml
before_script:
  - apt-get update && apt-get install -y git unzip libicu-dev libzip-dev
  - docker-php-ext-install intl zip opcache
  - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  - composer install --prefer-dist --no-progress --no-interaction
```

Ces commandes s'exécutent **avant** le `script`. C'est ici qu'on installe les
outils et les dépendances.

### `script:` — Les vérifications

```yaml
script:
  - php vendor/bin/phpstan analyse --no-progress
  - php vendor/bin/phpunit --no-coverage
```

Ce sont les commandes qui vérifient votre code. Si l'une échoue, le pipeline
devient **rouge**.

### `rules:` — Quand exécuter le pipeline

```yaml
rules:
  - if: $CI_MERGE_REQUEST_IID
  - if: $CI_COMMIT_BRANCH == $CI_DEFAULT_BRANCH
```

Le pipeline se lance :
- Quand une **Merge Request** est créée ou mise à jour
- Quand on **push** sur la branche principale

## Exemple complet pour LudoShop

Voici le fichier CI complet adapté au projet :

```yaml
stages:
  - quality

variables:
  COMPOSER_CACHE_DIR: "$CI_PROJECT_DIR/.composer-cache"
  PHP_VERSION: "8.3"

cache:
  key: "${CI_JOB_REF_SLUG}"
  paths:
    - .composer-cache/
    - vendor/

quality:
  stage: quality
  image: php:${PHP_VERSION}
  before_script:
    - apt-get update && apt-get install -y git unzip libicu-dev libzip-dev
    - docker-php-ext-install intl zip opcache
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --prefer-dist --no-progress --no-interaction
  script:
    - composer validate --strict
    - composer audit --no-interaction
    - php bin/console lint:yaml config
    - php bin/console lint:twig templates
    - php bin/console lint:container
    - php vendor/bin/php-cs-fixer fix --dry-run --diff
    - php vendor/bin/phpstan analyse --no-progress --memory-limit=512M
    - php vendor/bin/phpunit --no-coverage
    - php bin/console doctrine:schema:validate --skip-sync
    - php bin/console doctrine:migrations:migrate --no-interaction
    - php bin/console doctrine:fixtures:load --no-interaction
    - php vendor/bin/infection --threads=4 --show-mutations --no-progress
  artifacts:
    paths:
      - var/coverage.xml
    expire_in: 7 days
  rules:
    - if: $CI_MERGE_REQUEST_IID
    - if: $CI_COMMIT_BRANCH == $CI_DEFAULT_BRANCH
```

## Pousser le fichier

```bash
git add .gitlab-ci.yml
git commit -m "ci: add GitLab CI pipeline"
git push
```

Allez sur GitLab → **Build → Pipelines** pour voir la pipeline s'exécuter en temps réel.

## Débuguer

Si le pipeline échoue :
1. Cliquez sur le **pipeline** en erreur (❌ rouge)
2. Cliquez sur le **job** `quality` en erreur
3. Déroulez les étapes pour lire le **log**
4. Reproduisez la commande en local et corrigez

→ Voir le [03-deboguer-pipeline.md](03-deboguer-pipeline.md) pour plus de détails.

## Différences avec GitHub Actions

| Concept | GitHub Actions | GitLab CI |
|---------|---------------|-----------|
| Fichier | `.github/workflows/ci.yml` | `.gitlab-ci.yml` |
| Déclencheur | `on: push:` | `rules:` |
| Machine | `runs-on: ubuntu-latest` | `image: php:8.3` |
| Étapes | `steps:` avec `uses:` ou `run:` | `script:` avec commandes directes |
| Actions | `uses: actions/checkout@v4` | Pas nécessaire (Git clone automatiquement) |
| Cache | `uses: actions/cache@v4` | `cache:` (intégré) |
| Variables | `${{ secrets.X }}` | `$CI_PROJECT_DIR`, `$CI_COMMIT_BRANCH` |

## Et ensuite ? Bloquer le merge si la CI est rouge

Ne laissez pas un merge passer alors que la CI est rouge. Configurez la protection de branche :

* **Sur GitLab :** suivez le guide [06b-protection-gitlab.md](06b-protection-gitlab.md) — pour **gitlab.com** et **GitLab self-hosted IUT**
* **Sur GitHub :** suivez le pendant [06-protection-github.md](06-protection-github.md) — pour **github.com** et **GitHub Enterprise** (IUT)
