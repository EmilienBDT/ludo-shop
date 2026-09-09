# 05 — Glossaire CI/CD

> Dictionnaire des termes techniques utilisés dans le cours, expliqués simplement.

## A

### Action (GitHub Actions)
Composant réutilisable dans un workflow. Par exemple, `actions/checkout@v4` clone
votre dépôt. C'est comme une "application" qu'on installe dans le pipeline.

### Analyse statique
Vérification du code **sans l'exécuter**. L'outil examine le texte et cherche des
erreurs (types incorrects, variables non utilisées). **Exemple** : PHPStan.

## B

### Branch (branche)
Copie indépendante du code. On crée des branches pour travailler sur des
fonctionnalités sans impacter `main`.

### Build
Étape de compilation ou de préparation du code. En PHP, le "build" = installer les
dépendances et valider la configuration.

## C

### Cache
Stockage temporaire pour accélérer le pipeline. Le cache Composer évite de réinstaller
toutes les dépendances à chaque exécution.

### CI (Intégration Continue)
Vérification automatique du code à chaque push. Le serveur exécute tests, lint,
analyse statique, et rapporte le résultat (vert/rouge).

### Commit
Sauvegarde du code dans Git avec un message descriptif.
**Exemple** : `feat: add product catalog page`.

### Composer
Gestionnaire de dépendances PHP. Il installe les bibliothèques tierces (Symfony,
Doctrine, PHPUnit) définies dans `composer.json`.

### Coverage (couverture)
Pourcentage de code exécuté par les tests. 70 % signifie que 70 % du code est "touché"
par les tests au moins une fois.

## D

### Dependabot
Outil GitHub qui surveille les dépendances et crée automatiquement des pull requests
pour les mises à jour de sécurité.

### Doctrine
ORM pour PHP. Manipule la base de données via des objets PHP au lieu du SQL.

## E

### Entity
Classe PHP représentant une table de la base de données.

### Env (environnement)
Paramètres qui changent selon le contexte (dev, test, production).
**Exemple** : `MAILER_DSN` est `smtp://localhost:1025` en dev et `null://null` en test.

## F

### Fixtures
Données de démo reproductibles pour peupler la base lors du développement et des tests.

### Flat config
Nouveau format ESLint 9. Le fichier `eslint.config.js` utilise du JS pur (remplace
`.eslintrc`).

## G

### GitHub Actions
Plateforme CI de GitHub. Elle exécute des workflows YAML à chaque push ou pull request.

### GitLab CI
Plateforme CI de GitLab. Elle exécute des pipelines définis dans `.gitlab-ci.yml`
à chaque push ou merge request. Similaire à GitHub Actions mais avec une syntaxe
et une interface différentes.

### GitLab Runner
Machine qui exécute les jobs GitLab CI. Contrairement à GitHub Actions, les runners
peuvent être partagés (instances managées) ou installés sur vos propres machines.

## I

### Infection
Outil de mutation testing. Il injecte des bugs artificiels dans le code et vérifie
que les tests les détectent. Score MSI = % de mutations "tuées" par les tests.

### Integration (intégration)
Action de fusionner des parties séparées du code. L'intégration continue = fusionner
et vérifier le code automatiquement et fréquemment.

## L

### Lint (linting)
Vérification du code source selon des règles de syntaxe et de style. **Exemples** :
`lint:yaml` pour la configuration, `lint:twig` pour les templates.

## M

### Merge
Fusionner une branche dans une autre (généralement `feat/xxx` → `main`).
Se fait via une Pull Request (GitHub) ou Merge Request (GitLab) après validation du pipeline.

### Merge Request (GitLab)
Équivalent de la Pull Request sur GitHub. C'est une demande de fusion de branche
qui déclenche le pipeline CI.

### MSI (Mutation Score Indicator)
Pourcentage de mutations (bugs injectés) détectées par les tests. MSI 80 % = les tests
attrapent 80 % des bugs artificiels.

### Mutation testing
Technique qui modifie le code source et vérifie que les tests échouent. Si le test
passe toujours, il y a un "trou" dans la couverture.

## P

### Pipeline
Enchaînement d'étapes automatiques : lint → analyse → tests → sécurité.
Un pipeline est "verte" si tout passe, "rouge" si une étape échoue.

### PR (Pull Request)
Demande de fusion de branche sur **GitHub**. Le pipeline CI s'exécut sur la PR avant de
permettre le merge.

### PSR-12
Standard de style de code PHP. PHP-CS-Fixer vérifie que le code le respecte.

## R

### Runner
Machine virtuelle qui exécute le pipeline.
- **GitHub Actions** : runners Ubuntu, Windows, macOS (fournis par GitHub)
- **GitLab CI** : runners partagés ou auto-hébergés

## S

### Schema validate
Vérification que le schéma Doctrine est synchronisé avec les mappings PHP.

## W

### Workflow
Fichier YAML qui définit un pipeline CI sur **GitHub Actions**. Il spécifie le
déclencheur, la machine, et les étapes à exécuter. L'équivalent GitLab est le
fichier `.gitlab-ci.yml`.

## Vocabulaire des résultats

| Terme | Signification |
|-------|---------------|
| **Vert (green / passed)** | Tous les checks passent |
| **Rouge (red / failed)** | Au moins un check échoue |
| **En cours (pending / running)** | Le pipeline s'exécut |
| **Annulé (cancelled)** | Le pipeline a été arrêté manuellement |
| **Ignoré (skipped)** | Une étape a été ignorée |

## Vocabulaire Git

| Terme | Signification |
|-------|---------------|
| **Push** | Envoyer des commits vers le dépôt distant |
| **Pull** | Récupérer les commits depuis le dépôt distant |
| **Clone** | Copier un dépôt distant sur sa machine |
| **Fork** | Copier le dépôt de quelqu'un sur son propre compte |
| **HEAD** | Le dernier commit de la branche courante |
| **Rebase** | Réécrire l'historique pour mettre à jour une branche |

## Vocabulaire GitHub vs GitLab

| Concept | GitHub | GitLab |
|---------|--------|--------|
| Demande de fusion | Pull Request (PR) | Merge Request (MR) |
| Fichier CI | `.github/workflows/ci.yml` | `.gitlab-ci.yml` |
| Actions / Jobs | `uses:` + `run:` | `script:` (commandes directes) |
| Lancer manuellement | `workflow_dispatch` | `workflow_dispatch:` ou bouton "Run" |
| Protections de branche | Branch protection rules | Protected branches |
| Variable secrète | Secrets | CI/CD Variables |
