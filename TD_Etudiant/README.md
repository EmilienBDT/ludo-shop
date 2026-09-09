# TD Étudiant — Tests à implémenter (LudoShop)

> **Objectif :** écrire les tests qui couvrent les fonctionnalités critiques de LudoShop. Chaque test doit passer en local **et** dans le pipeline CI.
> **Durée :** 7-8 h (00: 1h, 01-06: 3h, 07-10: 2h, 11: 1h TDD).
> **Ordre :** du plus simple au plus complexe — suivez l'ordre 00 → 11.

## Étape 0 obligatoire
- **[00-pipeline.md](00-pipeline.md)** — Configurer le pipeline CI et la protection de branche (`Aide/02-workflow.md`, `Aide/02b-gitlab-ci.md`, `Aide/06-protection-github.md` / `06b-protection-gitlab.md`). **Sans `00` vert, le TD est bloqué.**

## Les 10 tests + feature obligatoire

| # | Fichier | Type | Concept | Tests à créer | Points (Bloc C 8pts + D 4pts) |
|---|---------|------|---------|---------------|-------------------------------|
| 1 | [01-product.md](01-product.md) | Unitaire | Entity `isMature`/`isAvailable` | 3 | 1,0 |
| 2 | [02-promotion.md](02-promotion.md) | Unitaire | `PromotionService` dates/bornes | 8 | 2,0 |
| 3 | [03-cart-service.md](03-cart-service.md) | Unitaire | Stubs `CartService` | 2 | 0,75 |
| 4 | [04-schema-validation.md](04-schema-validation.md) | Fonctionnel | `SchemaValidator` | 0 (1 fourni) | 0 |
| 5 | [05-fixtures-check.md](05-fixtures-check.md) | Fonctionnel | `CommandTester` | 0 (2 fournis) | 0 |
| 6 | [06-login.md](06-login.md) | Fonctionnel | CSRF, redirects | 1 (`Logout`) | 0,5 |
| 7 | [07-catalog.md](07-catalog.md) | Fonctionnel | Filtres, mature/minor | 1 (`FilterByCategory`) | 0,5 |
| 8 | [08-cart.md](08-cart.md) | Fonctionnel | Session panier | 2 | 1,0 |
| 9 | [09-checkout.md](09-checkout.md) | Fonctionnel | Parcours 4 étapes | 2 | 1,25 |
| 10 | [10-order-status.md](10-order-status.md) | Fonctionnel | Sécurité, history, mail | 0 (4 fournis) | 0 |
| 11 | [11-promo-panier.md](11-promo-panier.md) | Feature obligatoire | Implémenter promo au panier | 1 (test + code) | **4** |

**Détail barème global 20 pts (voir énoncé complet) :**
- **A — 5 pts :** 0,5 pt × 10 fichiers `tests/**` existants **et** verts en CI (`5` + `Actions`/`Pipelines` vert)
- **B — 3 pts :** Pipeline MR vert + protection `Require status checks` (GitHub) ou `Pipelines must succeed` (GitLab) + capture Merge bloqué (1+1+1)
- **C — 8 pts :** 19 tests `à créer` ci-dessus (pondéré)
- **D — 4 pts :** Feature 11 obligatoire (code + test vert)

## Livrables obligatoires (3 captures)

1. **capture-merge-bloque.png** — MR avec `Checks failed` / `Pipeline failed` + bouton `Merge` grisé
2. **capture-assertions.png** — `php vendor/bin/phpunit --testdox --no-coverage` (≥43 tests, ≥XX assertions) + `php vendor/bin/phpunit --testdox | grep assertions`
3. **capture-pipeline-global.png** — `Actions` ou `Build → Pipelines` avec 9-10 jobs verts sur `main`

> Nommer les fichiers exactement ainsi dans `rendu/` ou PDF Moodle. Sans ces 3 captures, **-1 pt/capture** (bloc B).

## Règles générales & références

* `Aide/03-deboguer-pipeline.md` pour lire un log rouge, `Aide/04-pieges-courants.md` pour les erreurs fréquentes, `TD_Etudiant/Aide/` et `TD_Etudiant/docs/` contiennent le cours et les sources techniques.
* Voir chaque `0X-*.md` pour le patron complet, les points clés et la commande `php vendor/bin/phpunit tests/...`.
