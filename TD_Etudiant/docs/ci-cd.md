# CI/CD — GitHub Actions, GitLab CI, Dependabot

> Pipelines d'intégration continue et veille automatisée des dépendances.

## Objectif

L'intégration continue (CI) est le **cœur du cours**. À chaque push ou pull request,
un pipeline automatique vérifie que le code est :
- Syntaxiquement correct
- Conforme au style
- Couvert par des tests
- Sûr (pas de vulnérabilités connues)

Si une vérification échoue, le pipeline est **rouge** et le merge est bloqué.

## Pourquoi ici

- **Objectif pédagogique** : les étudiants apprennent à mettre en place et maintenir
  un pipeline CI.
- **Qualité garantie** : le CI empêche le code cassé d'atteindre la branche principale.
- **Phase 2** : les bugs injectés par l'enseignant doivent être **détectés par le pipeline**
  (CI rouge) — c'est le critère de validation.
- **Dependabot** : surveille automatiquement les dépendances obsolètes ou vulnérables.

## Pipelines

### GitHub Actions (`.github/workflows/ci.yml`)

Le pipeline s'exécute sur **GitHub** à chaque push/PR :

```
CI Pipeline (GitHub Actions)
│
├── 1. Lint (yaml, twig, container)
├── 2. PHP-CS-Fixer (style de code)
├── 3. PHPStan (analyse statique niveau 8)
├── 4. PHPUnit (180 tests)
├── 5. Infection (mutation testing)
├── 6. Composer audit (sécurité)
├── 7. Doctrine schema validate
├── 8. Fixtures check
├── 9. ESLint + Prettier (JS lint)
└── 10. pa11y (accessibilité)
```

### GitLab CI (`.gitlab-ci.yml`)

Pipeline équivalent pour **GitLab** :

```
CI Pipeline (GitLab CI)
│
└── quality (stage unique)
    ├── lint:yaml + twig + container
    ├── php-cs-fixer
    ├── phpstan (level 8)
    ├── phpunit --coverage-clover
    ├── infection
    ├── composer audit
    ├── doctrine:schema:validate
    └── app:fixtures:check
```

## Configuration technique

### Environnement CI

| Paramètre | Valeur | Description |
|-----------|--------|-------------|
| PHP | 8.3 | Version de PHP |
| Node.js | 20 | Version de Node.js |
| Extension PCOV | `php-pcov` | Couverture de code |
| Cache | `~/.cache/composer` | Cache des dépendances PHP |

### Fichiers de configuration

| Fichier | Outil | Rôle |
|---------|-------|------|
| `.github/workflows/ci.yml` | GitHub Actions | Pipeline CI GitHub |
| `.gitlab-ci.yml` | GitLab CI | Pipeline CI GitLab |
| `.github/dependabot.yml` | Dependabot | Veille des dépendances |
| `phpstan.neon` | PHPStan | Configuration analyse statique |
| `phpunit.dist.xml` | PHPUnit | Configuration tests |
| `infection.json` | Infection | Configuration mutation testing |
| `.php-cs-fixer.dist.php` | PHP-CS-Fixer | Configuration style de code |
| `.pa11yci.json` | pa11y | Configuration accessibilité |
| `eslint.config.js` | ESLint | Configuration lint JS |

## Dependabot

**Dependabot** surveille automatiquement les dépendances du projet et crée des pull
requests pour les mises à jour de sécurité.

### Écosystèmes surveillés

| Écosystème | Fichier | Fréquence |
|------------|---------|-----------|
| Composer | `composer.json` | Hebdomadaire |
| npm | `package.json` | Hebdomadaire |
| GitHub Actions | `.github/workflows/` | Hebdomadaire |

### Comment ça marche

1. Dependabot détecte une nouvelle version d'une dépendance
2. Il crée automatiquement une pull request avec la mise à jour
3. Le pipeline CI s'exécute sur la PR pour vérifier la compatibilité
4. Un mainteneur fusionne la PR si les tests passent

## Comment lancer en local

```bash
# Simuler le pipeline CI en local (un par un)
php bin/console lint:yaml config
php bin/console lint:twig templates
php bin/console lint:container
php vendor/bin/php-cs-fixer fix --dry-run --diff
php vendor/bin/phpstan analyse --no-progress
php vendor/bin/phpunit --no-coverage
php vendor/bin/infection --threads=4 --no-progress
composer audit --no-interaction
php bin/console doctrine:schema:validate --skip-sync
php bin/console app:fixtures:check
npm install
npm run lint
npm run format:check

# Ou exécuter tous les checks d'un coup
# (à lancer depuis la racine du projet)
composer lint && php vendor/bin/phpstan analyse --no-progress && php vendor/bin/phpunit --no-coverage
```

## Diagnostic quand le pipeline est rouge

| Symbole | État | Action |
|---------|------|--------|
| ✅ Vert | Tous les checks passent | Le merge est autorisé |
| ❌ Rouge | Un ou plusieurs checks échouent | Corriger l'erreur avant de push |
| 🟡 Orange | Le pipeline est en cours | Attendre la fin |
| ⚪ Gris | Pipeline non déclenché | Vérifier la configuration |

### Étapes de diagnostic

1. **Lire le log** du pipeline pour identifier la commande qui échoue
2. **Reproduire en local** : exécuter la même commande sur votre machine
3. **Corriger** le problème
4. **Push** et vérifier que le pipeline devient vert

## Ressources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [GitLab CI Documentation](https://docs.gitlab.com/ee/ci/)
- [Dependabot Documentation](https://docs.github.com/en/code-security/dependabot)
- [Symfony CI Best Practices](https://symfony.com/doc/current/best_practices/continuous_integration.html)
