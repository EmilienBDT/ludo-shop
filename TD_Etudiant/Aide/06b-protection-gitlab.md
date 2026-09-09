# 06b — Bloquer le merge si la CI est rouge — GitLab

> Pour **gitlab.com** (SaaS) et **GitLab self-hosted** (IUT, ex: `gitlab.iut.fr`, `git.iut-xxx.fr`). Les chemins diffèrent légèrement selon la version (15.x / 16.x / 17.x) — les deux variantes sont indiquées.

> Prérequis : avoir déjà un `.gitlab-ci.yml` qui passe au vert (`02b-gitlab-ci.md`). Sans pipeline, la protection n'a rien à vérifier.

## Pourquoi ?

Sans protection, n'importe qui peut cliquer sur **Merge** même si `phpstan` ou `infection` est rouge. La branche `main` est alors cassée pour tout le groupe. L'option **Pipelines must succeed** grise le bouton tant que le *merge request pipeline* n'est pas vert.

## Instance : où cliquer ?

| Instance | Chemin dans l'interface | Note |
|----------|-------------------------|------|
| **gitlab.com** (cloud) | `Projet → Settings → General → Merge requests` | UI la plus récente |
| **GitLab self-hosted** IUT (16.x/17.x) | `Settings → General → Merge requests` **ou** `Settings → Merge requests` | Selon la version, `General` est un sous-onglet |
| Ancienne version IUT (15.x) | `Settings → General → Merge requests` | Même toggle, nom identique |

> **Rôle requis :** `Maintainer` (ou `Owner`). Les `Developer` ne voient pas `Settings`. Demandez à votre enseignant.

## Configuration pas à pas — gitlab.com

### Étape A — Interdire le merge si pipeline rouge

1. Allez sur `https://gitlab.com/VOTRE_GROUPE/ludo-shop`
2. Menu gauche **Settings → General** (tout en bas) → déroulez **Merge requests**
3. Cochez :
   - ✅ **Pipelines must succeed** — *« A pipeline must have succeeded before merging »*
     > C'est le réglage qui bloque réellement. Le bouton `Merge` affiche `Pipeline must succeed` tant que le pipeline MR n'est pas vert.
   - ✅ **Pipelines must succeed for the latest push** (ou `Strict` selon version) — évite de merger un vieux pipeline vert alors que le dernier push est rouge.
   - ❌ **Skipped pipelines are considered successful** → **décocher** (sinon un MR sans pipeline passe pour vert).
4. Cliquez **Save changes**

> **Effet :** une Merge Request vers `main` affiche `Pipeline: failed` + bouton `Merge` grisé + message `Pipeline must succeed`.

### Étape B — Protéger la branche `main`

1. Toujours dans **Settings → Repository → Protected branches** (ou `Settings → Repository → Branch protection`)
2. **Branch:** `main` → **Allowed to merge:** `Maintainers` → **Allowed to push and merge:** `No one` (ou `Maintainers` si vous voulez autoriser le push direct en secours)
3. Décochez **Allowed to force push**
4. Cliquez **Protect**

> Résultat : personne ne peut `git push origin main` directement — on doit passer par une Merge Request, qui elle-même est bloquée par l'étape A.

### Variante IUT self-hosted (ex: `gitlab.iut.fr`)

Le chemin est quasi identique, seul le domaine change :

* `https://gitlab.iut.fr/votre-groupe/ludo-shop → Settings → General → Merge requests` — mêmes 3 cases à cocher.
* Si vous ne voyez pas `Pipelines must succeed`, cherchez **Settings → Merge requests** (sans `General`) — c'est la même page déplacée en 16.x.
* **Premium IUT :** si votre instance est en `Premium`, vous verrez aussi `Require approval` / `Code Owners` / `Merge trains` — cochez `Require 1 approval` pour l'atelier IUT (revue entre étudiants).

> **Astuce IUT :** l'URL de l'API change : `https://gitlab.iut.fr/api/v4/...` et non `gitlab.com`.

## Vérifier que le pipeline MR est le bon (code)

### `workflow:rules` recommandé (en tête de `.gitlab-ci.yml`)

Sans cette règle, GitLab peut créer un *branch pipeline* et un *merge request pipeline* en double. Ajoutez :

```yaml
workflow:
  rules:
    - if: $CI_MERGE_REQUEST_IID
    - if: $CI_COMMIT_BRANCH == $CI_DEFAULT_BRANCH
```

Votre projet a déjà l'équivalent via l'ancre `.default-rules:43` (`rules: - if: $CI_MERGE_REQUEST_IID`) — c'est bien le *merge request pipeline* qui sera exigé par `Pipelines must succeed`.

> **Important :** aucun job ne doit avoir `allow_failure: true` — sinon un job rouge n'échoue pas le pipeline. Vérifiez `.gitlab-ci.yml:59-153` : aucun `allow_failure` n'est présent, c'est correct (tout job fail = pipeline fail).

## Via ligne de commande (optionnel, pour automatiser)

Avec un `Personal Access Token` (`Settings → Access Tokens → Add new token` → `api`) :

```bash
# gitlab.com
curl --header "PRIVATE-TOKEN: $GITLAB_TOKEN" \
  --request PUT "https://gitlab.com/api/v4/projects/$PROJECT_ID" \
  --data "only_allow_merge_if_pipeline_succeeds=true" \
  --data "only_allow_merge_if_all_discussions_are_resolved=true"

# IUT self-hosted — remplacer le domaine
curl --header "PRIVATE-TOKEN: $GITLAB_TOKEN" \
  --request PUT "https://gitlab.iut.fr/api/v4/projects/$PROJECT_ID" \
  --data "only_allow_merge_if_pipeline_succeeds=true"
```

Pour protéger `main` via API :

```bash
curl --header "PRIVATE-TOKEN: $GITLAB_TOKEN" \
  --request POST "https://gitlab.com/api/v4/projects/$PROJECT_ID/protected_branches" \
  --data "name=main&push_access_level=0&merge_access_level=40"
# push_access_level 0 = No one, 40 = Maintainers
```

## Vérifier que ça bloque bien

1. Créez une branche qui casse volontairement la CI :
   ```bash
   git checkout -b test/break-ci
   echo "// break" >> src/Service/CartService.php
   git add . && git commit -m "test: break ci" && git push -u origin test/break-ci
   ```
2. Sur GitLab, cliquez **Create merge request** vers `main`
3. Attendez : vous devez voir `Pipeline: failed` (rouge) et le bouton **Merge** grisé avec `Pipeline must succeed — A pipeline must have succeeded`.
4. Corrigez (`php vendor/bin/php-cs-fixer fix`, `git commit -m "fix: style" && git push`) → la pipeline MR repasse verte → le bouton devient **vert** et le merge est autorisé.

## Dépannage GitLab

| Symptôme | Cause | Fix |
|----------|-------|-----|
| Le bouton Merge reste actif même rouge | `Pipelines must succeed` non coché | Recocher dans `Settings → General → Merge requests` + `Save` |
| `Pipeline: skipped` considéré vert | `Skipped pipelines are considered successful` coché | **Décocher** cette case |
| Le merge reste grisé même vert | Ancien pipeline vert mais dernier push rouge | Activer `Pipelines must succeed for the latest push` (Strict) |
| MR depuis un fork ne lance pas de pipeline | `Pipelines for merged results` non activé | `Settings → General → Merge requests` → ✅ `Pipelines for merged results` (ou `Pipelines for merge request` selon version) |
| Besoin d'une revue avant merge (IUT) | Option Premium | `Settings → General → Merge requests` → `Require 1 approval` + `Code Owners` |

## Prochaine étape

* Si vous êtes sur **GitHub**, suivez le pendant : [06-protection-github.md](06-protection-github.md).
* En cas de pipeline rouge, voir [03-deboguer-pipeline.md](03-deboguer-pipeline.md).
* Pour les erreurs fréquentes, voir [04-pieges-courants.md](04-pieges-courants.md).

## Références

* [GitLab Docs — Merge request pipelines](https://docs.gitlab.com/ee/ci/pipelines/merge_request_pipelines.html)
* [GitLab Docs — Pipelines must succeed](https://docs.gitlab.com/ee/user/project/merge_requests/merge_when_pipeline_succeeds.html)
* [GitLab Docs — Protected branches](https://docs.gitlab.com/ee/user/project/repository/branches/protected.html)
* Fichier réel du projet : `.gitlab-ci.yml`
