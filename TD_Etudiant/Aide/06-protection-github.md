# 06 — Bloquer le merge si la CI est rouge — GitHub

> Pour **github.com** (SaaS) et **GitHub Enterprise Server** (IUT / entreprise). Le chemin est identique, seule la position du menu change légèrement selon la version.

> Prérequis : avoir déjà un workflow qui passe au vert (`02-workflow.md`). Sans pipeline, la protection n'a rien à vérifier.

## Pourquoi ?

Sans protection, n'importe qui peut cliquer sur **Merge pull request** même si `phpstan` ou `infection` est rouge. La branche `main` est alors cassée pour tout le groupe. L'option **Require status checks** grise le bouton tant que le pipeline n'est pas vert.

## Instance : où cliquer ?

| Instance | Chemin dans l'interface | Note |
|----------|-------------------------|------|
| **github.com** (cloud) | `Votre repo → Settings → Branches → Add classic branch protection rule` | UI la plus récente : `Settings → Branches → Branch protection rules` |
| **GitHub Enterprise Server** (IUT, ex: `github.iut.fr`) | `Settings → Branches` **ou** `Settings → Repository → Branches` selon version GHE | Si vous voyez **Rulesets** (GHE ≥ 3.10), voir encadré ci-dessous |

> **Rôle requis :** `Admin` sur le dépôt (ou `Maintain` avec droits `Admin`). Les étudiants en `Write` ne voient pas `Settings`.

## Configuration pas à pas (github.com)

1. Allez sur `https://github.com/VOTRE_UTILISATEUR/ludo-shop`
2. Onglet **Settings** (en haut) → menu gauche **Branches**
3. Cliquez **Add classic branch protection rule** (ou **Add rule**)
4. **Branch name pattern :** tapez `main` (exactement, sans `*`)
5. Cochez :
   - ✅ **Require a pull request before merging**
     - `Required approvals: 0` (IUT : mettre `1` si vous voulez une revue entre étudiants)
     - ✅ `Dismiss stale pull request approvals when new commits are pushed`
   - ✅ **Require status checks to pass before merging**
     - ✅ `Require branches to be up to date before merging` (cochez-la : GitHub relance la CI si `main` a bougé)
     - Dans la recherche **Status checks**, tapez et cochez **tous** les jobs définis dans `.github/workflows/ci.yml` :
       - `composer-validate`
       - `lint-config`
       - `cs-fixer`
       - `phpstan`
       - `security-audit`
       - `unit-tests`
       - `integration-tests`
       - `functional-tests`
       - `schema-verify`
       - `infection`
       > **Astuce :** les noms n'apparaissent qu'après **un premier run** du workflow. Si la liste est vide, poussez une fois sur `main` puis revenez.
   - ✅ **Do not allow bypassing the above settings** (personne ne peut forcer le merge, même un Admin)
   - ✅ **Restrict who can push to matching branches** → laissez vide ou `No one` (on ne push jamais direct sur `main`, on passe par PR)
6. Cliquez **Create** / **Save changes**

> **Résultat :** une Pull Request vers `main` affiche `Checks: 10 jobs — Some checks have not completed` puis `Required status checks have not succeeded — Merging is blocked`.

### Variante GitHub Enterprise — Rulesets (nouvelle UI)

Si votre IUT affiche **Settings → Rules → Rulesets → New ruleset** :

1. **Ruleset Name:** `Protection main`
2. **Target branches:** `Include by pattern: main`
3. **Branch protections →** ✅ `Require status checks` → `Add checks` → listez les 10 jobs ci-dessus
4. **Bypass list:** `No one`

C'est l'équivalent moderne du *classic branch protection rule*.

## Via ligne de commande (optionnel, pour automatiser)

Avec la CLI `gh` (authentifiée) :

```bash
# Nécessite gh auth login
gh api repos/OWNER/ludo-shop/branches/main/protection \
  -f required_status_checks='{"strict":true,"contexts":["composer-validate","lint-config","cs-fixer","phpstan","security-audit","unit-tests","integration-tests","functional-tests","schema-verify","infection"]}' \
  -f enforce_admins=true \
  -f required_pull_request_reviews='{"required_approving_review_count":0}' \
  -f restrictions=null
```

Ou via API REST :

```bash
curl -L -X PUT -H "Accept: application/vnd.github+json" \
  -H "Authorization: Bearer $GITHUB_TOKEN" \
  https://api.github.com/repos/OWNER/ludo-shop/branches/main/protection \
  -d '{"required_status_checks":{"strict":true,"contexts":["lint-config","phpstan","functional-tests"]},"enforce_admins":true,"required_pull_request_reviews":{"required_approving_review_count":0},"restrictions":null}'
```

## Vérifier que ça bloque bien

1. Créez une branche qui casse volontairement la CI :
   ```bash
   git checkout -b test/break-ci
   echo "// break" >> src/Service/CartService.php
   git add . && git commit -m "test: break ci" && git push -u origin test/break-ci
   ```
2. Sur GitHub, cliquez **Compare & pull request** → `Create pull request` vers `main`
3. Attendez : vous devez voir `Some checks were not successful — 1 failing` et le bouton **Merge pull request** grisé avec `Merging is blocked`.
4. Corrigez (`php vendor/bin/php-cs-fixer fix`, `git commit -m "fix: style" && git push`) → la CI repasse verte → le bouton devient **vert** et le merge est autorisé.

## Dépannage GitHub

| Symptôme | Cause | Fix |
|----------|-------|-----|
| La liste `Status checks` est vide | Le workflow n'a jamais tourné | Poussez une fois sur `main` ou ouvrez une PR — les noms apparaissent après le 1er run |
| `Merge` reste grisé même vert | `Require branches to be up to date` coché et `main` a avancé | Cliquez `Update branch` dans la PR, la CI se relance |
| Un Admin peut quand même merger rouge | `Do not allow bypassing` décoché | Recocher `Do not allow bypassing the above settings` |
| `Skipped` considéré comme succès | Un job a `if: false` | Ne pas mettre `allow_failure` / `continue-on-error` sur les jobs critiques |

## Prochaine étape

* Si vous êtes sur **GitLab**, suivez le pendant : [06b-protection-gitlab.md](06b-protection-gitlab.md).
* En cas de pipeline rouge, voir [03-deboguer-pipeline.md](03-deboguer-pipeline.md).
* Pour les erreurs fréquentes, voir [04-pieges-courants.md](04-pieges-courants.md).

## Références

* [GitHub Docs — About protected branches](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches)
* [GitHub Docs — Require status checks](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-a-branch-protection-rule#require-status-checks-before-merging)
* Fichier réel du projet : `.github/workflows/ci.yml`
