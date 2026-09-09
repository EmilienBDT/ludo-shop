# 00 — Étape 0 : Configurer le pipeline CI et bloquer les merges cassés

> **Objectif :** avant d'écrire le moindre test, votre pipeline doit être vert et la branche `main` protégée. Sans ça, vos 10 tests ne servent à rien.

> **Durée :** 45-60 min. **Prérequis :** `Aide/01-github-repo.md` ou `Aide/01b-gitlab-repo.md` (dépôt créé et push initial fait).

## 1. Choisir votre plateforme

| | GitHub Actions | GitLab CI |
|---|---|---|
| Fichier | `.github/workflows/ci.yml` | `.gitlab-ci.yml` |
| Déclencheur | `on: push/pull_request` (`Aide/02-workflow.md:38`) | `rules: $CI_MERGE_REQUEST_IID` (`Aide/02b-gitlab-ci.md:43`) |
| UI | Onglet **Actions** | **Build → Pipelines** |

Vous n'en choisissez qu'une (celle de votre groupe). Les deux font la même chose.

## 2. Pousser le workflow / CI

### GitHub

```bash
# Vérifier que .github/workflows/ci.yml existe (copié depuis le projet)
ls .github/workflows/ci.yml
git add .github/workflows/ci.yml
git commit -m "ci: add GitHub Actions workflow"
git push -u origin main
# → Allez sur github.com/VOTRE_GROUPE/ludo-shop → Actions → pipeline en cours
```

Suivez le guide pas à pas : `Aide/02-workflow.md`.

### GitLab

```bash
ls .gitlab-ci.yml
git add .gitlab-ci.yml
git commit -m "ci: add GitLab CI pipeline"
git push -u origin main
# → Allez sur gitlab.com / gitlab.iut.fr → Build → Pipelines
```

Suivez : `Aide/02b-gitlab-ci.md`. Vérifiez `workflow:rules` en tête de `.gitlab-ci.yml` pour éviter les doublons.

## 3. Activer le blocage du merge (obligatoire, 3 pts du barème)

### GitHub (github.com et GitHub Enterprise IUT)

Voir le guide complet : `Aide/06-protection-github.md`.

Résumé :
1. `Settings → Branches → Add classic branch protection rule` → `Branch name pattern: main`
2. ✅ `Require a pull request before merging`
3. ✅ `Require status checks to pass` → cocher **tous** les jobs : `composer-validate`, `lint-config`, `cs-fixer`, `phpstan`, `security-audit`, `unit-tests`, `integration-tests`, `functional-tests`, `schema-verify`, `infection`
4. ✅ `Require branches to be up to date` + ✅ `Do not allow bypassing`

### GitLab (gitlab.com et self-hosted IUT)

Voir : `Aide/06b-protection-gitlab.md`.

Résumé :
1. `Settings → General → Merge requests` → ✅ `Pipelines must succeed` + ✅ `Pipelines must succeed for the latest push` + ❌ `Skipped pipelines are considered successful` (décoché)
2. `Settings → Repository → Protected branches` → `main` → `Allowed to merge: Maintainers`, `Allowed to push: No one`

## 4. Vérifier que ça bloque bien

> Cette vérification est un **livrable** (capture `capture-merge-bloque.png`).

1. Créez une branche qui casse volontairement la CI :

```bash
git checkout -b test/break-ci
echo "// break" >> src/Service/CartService.php
git add . && git commit -m "test: break ci" && git push -u origin test/break-ci
```

2. Ouvrez une Pull Request / Merge Request vers `main`
3. Attendez : vous devez voir `Checks: failed` (GitHub) ou `Pipeline: failed` (GitLab) et le bouton **Merge** grisé avec `Merging is blocked`.
4. Corrigez puis repoussez :

```bash
git restore src/Service/CartService.php
git commit -m "fix: restore" && git push
```

→ La CI repasse verte → le bouton devient vert.

## 5. Livrable de cette étape

- **capture-pipeline-global.png** — `Actions` ou `Build → Pipelines` avec 9-10 jobs verts sur `main` après le fix.
- **capture-merge-bloque.png** — MR avec pipeline rouge et Merge bloqué.

Sans ces 2 captures, **-1 pt/capture** (bloc B du barème).

## Suite

Une fois `00` vert, passez à [01-product.md](01-product.md) — le test le plus simple.

> En cas de pipeline rouge, voir `Aide/03-deboguer-pipeline.md` et `Aide/04-pieges-courants.md`.

