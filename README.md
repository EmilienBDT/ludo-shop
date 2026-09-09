# LudoShop — Guide de démarrage

[![CI](https://github.com/USER/ludo-shop/actions/workflows/ci.yml/badge.svg)](https://github.com/USER/ludo-shop/actions/workflows/ci.yml)

Application e-commerce de jeux de société, projet pédagogique pour l'apprentissage de
**l'intégration continue (CI)** à l'IUT (15 h).

---

## Prérequis

| Outil | Version | Rôle |
|-------|---------|------|
| **PHP** | ≥ 8.3 | Langage backend |
| **Composer** | ≥ 2.x | Gestionnaire de dépendances PHP |
| **Symfony CLI** | ≥ 1.x | Serveur de développement (optionnel) |
| **Node.js** | ≥ 18 | Outils frontend (ESLint, Prettier, pa11y) |
| **npm** | ≥ 9 | Installation des dépendances JS |
| **Mailpit** | ≥ 1.x | Serveur SMTP local pour les emails (optionnel) |
| **Git** | ≥ 2.x | Versionning |

> PHP inclut SQLite par défaut, aucun serveur de base de données n'est nécessaire.

---

## Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/USER/ludo-shop.git
cd ludo-shop

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances JS (ESLint, Prettier, pa11y)
npm install

# 4. Créer la base de données et charger les données de démo
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

#5 compiler le SCSS
php bin/console sass:build
# attendu : [OK] var/sass/app-*.output.css généré
# Alternative : php bin/console sass:build --watch
php bin/console cache:clear   # optionnel si cache stale

# 6. Lancer le serveur de développement
symfony server:start
# → http://localhost:8000

# 6. Lancer Mailpit (serveur SMTP local pour les emails)
# Installer (une seule fois) :
scoop install mailpit        # via Scoop
# choco install mailpit      # OU via Chocolatey
# Puis lancer (dans un terminal séparé) :
mailpit
# → http://localhost:8025 (interface web)
```

---

## Comptes de démonstration

| Rôle | E-mail | Mot de passe | Description |
|------|--------|-------------|-------------|
| **Admin** | `admin@example.com` | `Admin123!` | Accès back-office complet |
| **Client** | `client@example.com` | `Client123!` | Parcours utilisateur standard |
| **Mineur** | `minor@example.com` | `Minor123!` | Pas d'accès au contenu +18 |

---

## Vérifications qualité

Chaque outil peut être lancé individuellement en local :

```bash
# Analyse statique (PHPStan)
php vendor/bin/phpstan analyse --no-progress

# Style de code (PHP-CS-Fixer)
php vendor/bin/php-cs-fixer fix --dry-run --diff

# Tests (PHPUnit)
php vendor/bin/phpunit --no-coverage

# Mutation testing (Infection)
php vendor/bin/infection --threads=4 --no-progress

# Audit de sécurité
composer audit --no-interaction

# Validation de la configuration Symfony
php bin/console lint:yaml config
php bin/console lint:twig templates
php bin/console lint:container

# Cohérence des fixtures
php bin/console app:fixtures:check

# Lint des assets JS
npm run lint

# Formatage des assets
npm run format:check

# Accessibilité
npm run a11y
```

> **Note :** Sous Windows, remplacez `php vendor/bin/...` par `php vendor\bin\...` ou utilisez
> les alias définis dans `composer.json`.

---

## Structure du projet

```
ludo-shop/
├── assets/
│   ├── js/                  # JavaScript (panier, thème, toasts, catalogue AJAX)
│   └── styles/              # SCSS (_base, _components)
├── config/                  # Configuration Symfony
├── migrations/              # Migrations Doctrine
├── public/                  # Point d'entrée web (index.php)
├── src/
│   ├── Command/             # Commandes console (backup, restore, fixtures check)
│   ├── Controller/          # Contrôleurs HTTP
│   │   └── Admin/           # Contrôleurs back-office
│   ├── DataFixtures/        # Données de démo
│   ├── Entity/              # Entités Doctrine (modèle de données)
│   ├── Enum/                # Énumérations (OrderStatus)
│   ├── Form/                # Types de formulaire
│   ├── Repository/          # Repositories Doctrine (requêtes)
│   └── Service/             # Services métier
├── templates/               # Templates Twig
├── tests/
│   ├── Unit/                # Tests unitaires
│   ├── Integration/         # Tests d'intégration
│   └── Functional/          # Tests fonctionnels (WebTestCase)
├── docs/                    # Documentation technique (un fichier par outil)
├── plan/                    # Planification pédagogique
├── .github/
│   ├── workflows/ci.yml     # Pipeline GitHub Actions
│   └── dependabot.yml       # Veille des dépendances
├── .gitlab-ci.yml           # Pipeline GitLab CI
├── phpstan.neon             # Configuration PHPStan
├── phpunit.dist.xml         # Configuration PHPUnit
├── infection.json           # Configuration Infection
├── .php-cs-fixer.dist.php   # Configuration PHP-CS-Fixer
├── eslint.config.js         # Configuration ESLint
├── .prettierrc              # Configuration Prettier
└── .pa11yci.json            # Configuration pa11y
```

---

## Documentation technique

Chaque outil utilisé dans le projet est documenté dans le dossier `TD_Etudiant/docs/` (déplacé depuis `docs/`) :

| Fichier | Outil | Objectif |
|---------|-------|----------|
| [TD_Etudiant/docs/symfony.md](TD_Etudiant/docs/symfony.md) | Symfony | Framework PHP backend |
| [TD_Etudiant/docs/doctrine.md](TD_Etudiant/docs/doctrine.md) | Doctrine ORM | ORM et base de données |
| [TD_Etudiant/docs/twig.md](TD_Etudiant/docs/twig.md) | Twig | Moteur de templates |
| [TD_Etudiant/docs/phpunit.md](TD_Etudiant/docs/phpunit.md) | PHPUnit | Tests unitaires et fonctionnels |
| [TD_Etudiant/docs/phpstan.md](TD_Etudiant/docs/phpstan.md) | PHPStan | Analyse statique |
| [TD_Etudiant/docs/php-cs-fixer.md](TD_Etudiant/docs/php-cs-fixer.md) | PHP-CS-Fixer | Style de code |
| [TD_Etudiant/docs/infection.md](TD_Etudiant/docs/infection.md) | Infection | Mutation testing |
| [TD_Etudiant/docs/eslint-prettier.md](TD_Etudiant/docs/eslint-prettier.md) | ESLint + Prettier | Lint et formatage JS |
| [TD_Etudiant/docs/pa11y.md](TD_Etudiant/docs/pa11y.md) | pa11y | Tests d'accessibilité |
| [TD_Etudiant/docs/mailpit.md](TD_Etudiant/docs/mailpit.md) | Mailpit | Test des emails en local |
| [TD_Etudiant/docs/ci-cd.md](TD_Etudiant/docs/ci-cd.md) | CI/CD | Pipelines GitHub Actions & GitLab CI |

> Les guides pédagogiques CI sont dans `TD_Etudiant/Aide/` (déplacés depuis `Aide/`). TD complet : [TD_Etudiant/README.md](TD_Etudiant/README.md)

---

## Pipeline CI

Le pipeline CI s'exécute automatiquement à chaque push ou pull request. Il vérifie :

1. **Lint** — Configuration YAML, Twig, container
2. **PHP-CS-Fixer** — Conformité PSR-12
3. **PHPStan** — Analyse statique niveau 8
4. **PHPUnit** — Tests unitaires + fonctionnels (180 tests)
5. **Infection** — Mutation testing (qualité des tests)
6. **Composer audit** — Vulnérabilités de dépendances
7. **Schema validate** — Schéma Doctrine à jour
8. **Fixtures check** — Cohérence des données de démo

> Consultez [TD_Etudiant/docs/ci-cd.md](TD_Etudiant/docs/ci-cd.md) pour les détails du pipeline.

---

## Ressources

- [Documentation Symfony 7.4](https://symfony.com/doc/7.4)
- [PHPUnit 12](https://phpunit.readthedocs.io/)
- [PHPStan](https://phpstan.org/)
- [Infection](https://infection.github.io/)
