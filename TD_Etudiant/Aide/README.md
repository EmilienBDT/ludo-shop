# Guide CI/CD pour débutants

> Ce guide est destiné aux étudiants IUT qui n'ont **jamais** mis en place de pipeline
> d'intégration continue. Il explique tout, étape par étape, du premier commit à la
> première pipeline verte.

## Choix de la plateforme

Vous avez le choix entre **GitHub Actions** et **GitLab CI**. Les deux font la même
chose — choisissez celle que votre groupe utilise.

| | GitHub Actions | GitLab CI |
|---|---|---|
| **Fichier de config** | `.github/workflows/ci.yml` | `.gitlab-ci.yml` |
| **Interface** | Onglet "Actions" | Menu "Build → Pipelines" |
| **Gratuit** | ✅ (2000 min/mois) | ✅ (400 min/mois) |
| **Lien** | [github.com](https://github.com) | [gitlab.com](https://gitlab.com) |

## Contenu

### Communs

| Fichier | Contenu |
|---------|---------|
| [03-deboguer-pipeline.md](03-deboguer-pipeline.md) | Lire les logs et corriger une pipeline rouge |
| [04-pieges-courants.md](04-pieges-courants.md) | Les erreurs les plus fréquentes et comment les éviter |
| [05-glossaire.md](05-glossaire.md) | Dictionnaire des termes CI/CD |

### GitHub Actions

| Fichier | Contenu |
|---------|---------|
| [01-github-repo.md](01-github-repo.md) | Créer un dépôt GitHub et y pousser votre code |
| [02-workflow.md](02-workflow.md) | Écrire un workflow GitHub Actions pas à pas |
| [06-protection-github.md](06-protection-github.md) | Bloquer le merge si la CI est rouge — GitHub (github.com & Enterprise) |

### GitLab CI

| Fichier | Contenu |
|---------|---------|
| [01b-gitlab-repo.md](01b-gitlab-repo.md) | Créer un dépôt GitLab et y pousser votre code |
| [02b-gitlab-ci.md](02b-gitlab-ci.md) | Écrire un fichier `.gitlab-ci.yml` pas à pas |
| [06b-protection-gitlab.md](06b-protection-gitlab.md) | Bloquer le merge si la CI est rouge — GitLab (gitlab.com & self-hosted IUT) |

## Qu'est-ce que la CI ?

### Le problème

Vous travaillez sur votre code, ça fonctionne **chez vous**. Vous push sur la branche
principale. Le lendemain, le code de votre collègue ne marche plus. Personne ne sait
pourquoi, et ça prend des heures à retrouver le bug.

### La solution

L'**intégration continue** (CI) automatise la vérification du code. À chaque fois que
quelqu'un push du code, un serveur distant :

1. **Clone** votre projet
2. **Installe** les dépendances
3. **Lance** les vérifications (lint, tests, analyse statique…)
4. **Rapporte** le résultat : ✅ vert (tout va bien) ou ❌ rouge (il y a un problème)

Si le pipeline est **rouge**, le merge de la branche est bloqué. On ne casse jamais
la branche principale.

### Le cycle

```
1. Créer une branche         git checkout -b ma-feature
2. Écrire du code            (éditeur de code)
3. Tester en local           php vendor/bin/phpunit
4. Push                      git push origin ma-feature
5. Pipeline automatique      GitHub Actions / GitLab CI vérifie tout
6. Pipeline verte ?          Oui → merger  /  Non → corriger
```

## Pourquoi c'est important

| Sans CI | Avec CI |
|---------|---------|
| Les bugs passent inaperçus | Les bugs sont détectés automatiquement |
| Le style de code varie | Le style est vérifié à chaque push |
| Les dépendances obsolètes restent | Dependabot signale les mises à jour |
| La qualité dépend de discipline | La qualité est **automatisée** |

## Prérequis

Avant de commencer, assurez-vous d'avoir :

- [x] Un compte **GitHub** ou **GitLab** (gratuit)
- [x] **Git** installé sur votre machine
- [x] Un éditeur de code (VS Code recommandé)
- [x] Le projet LudoShop cloné localement

## Par où commencer ?

1. **Choisissez votre plateforme** :
   - GitHub → commencez par le [01-github-repo.md](01-github-repo.md)
   - GitLab → commencez par le [01b-gitlab-repo.md](01b-gitlab-repo.md)
2. Ensuite, suivez le fichier workflow correspondant :
   - GitHub → [02-workflow.md](02-workflow.md)
   - GitLab → [02b-gitlab-ci.md](02b-gitlab-ci.md)
3. Puis bloquez les merges cassés :
   - GitHub → [06-protection-github.md](06-protection-github.md) (github.com & Enterprise)
   - GitLab → [06b-protection-gitlab.md](06b-protection-gitlab.md) (gitlab.com & self-hosted IUT)
4. En cas de problème, consultez le [03-deboguer-pipeline.md](03-deboguer-pipeline.md).
4. Consultez le [04-pieges-courants.md](04-pieges-courants.md) pour éviter les erreurs
   classiques.
5. Utilisez le [05-glossaire.md](05-glossaire.md) comme dictionnaire de référence.
