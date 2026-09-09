# 11 — Feature obligatoire : Prix promo au panier — Introduction au TDD (4 pts)

> **Barème : voir `README.md` bloc D (4 pts). Pas de barème détaillé ici.**
> **Objectif pédagogique :** découvrir le **TDD (Test-Driven Development)** : on écrit le test **avant** le code, on le voit échouer, on implémente le minimum pour le faire passer, puis on nettoie.

## Pourquoi cette feature ?

Le catalogue affiche déjà le prix promo grâce à `PromotionService::getCurrentPrice()` (`src/Service/PromotionService.php:11`, utilisé dans `templates/catalog/_product_grid.html.twig:43`). Par contre `CartService` l'ignore :

* `src/Service/CartService.php:13` ne prend qu'un `EntityManagerInterface`
* `src/Service/CartService.php:45` fait ` $item->setUnitPrice($product->getPrice())` — toujours le prix normal

Résultat : le client voit une promo sur le catalogue mais paie le prix fort au panier. C'est l'incohérence à corriger **en TDD**.

## User story & critères d'acceptation

> **En tant que** client
> **Je veux que** le panier utilise le prix courant (promo si active, sinon prix normal)
> **Afin de** payer le bon montant

**Règles métier (à coder) :**

1. Un produit est en promo si `PromotionService::isOnPromotion()` retourne `true` — c'est-à-dire : `promoPrice < prix normal` **et** `promoStart <= now <= promoEnd` (bornes incluses, `PromotionService.php:34`).
2. Pour ce TD, la période de promo à tester est **du 1 janvier 2026 au 1 janvier 2099 inclus** (`2026-01-01T00:00:00` → `2099-01-01T00:00:00`). Cette plage garantit que le test ne sera jamais hors période en CI, même en 2030.
3. Si la promo est active, `CartService::addProduct()` doit enregistrer `unitPrice = prix promo`, sinon `prix normal`.
4. `CartService::getTotal()` fait ensuite `somme(unitPrice × quantité)` via `CartItem::getLineTotal()` (`src/Entity/CartItem.php:101`) — rien à y changer si `unitPrice` est correct.

## Consigne TDD — À écrire de 0 (aucun code fourni)

Vous allez vivre le cycle **Red → Green → Refactor** :

### Étape 1 — RED : écrire le test qui échoue (20 min)

1. Ouvrez `tests/Unit/CartServiceTest.php` (celui du Test 3) et **créez de zéro** une nouvelle méthode `testPromotionalPriceIsUsed`.
2. Le test doit :
   * Créer un `Product` `price=50.00` `promoPrice=35.00` avec `promoStart = 2026-01-01` et `promoEnd = 2099-01-01`
   * Créer un `Cart` pour un `User` stubbé, appeler `addProduct(cart, product, 2)` puis vérifier `getTotal(cart) === 70.00` (35 × 2)
3. Lancez :
   ```bash
   php vendor/bin/phpunit tests/Unit/CartServiceTest.php --filter testPromotionalPriceIsUsed
   ```
   **Attendu : ROUGE** — le total actuel est `100.00` (50 × 2) car `CartService` ignore la promo.

> Indice : vous devrez stubber `EntityManagerInterface` et `PromotionService` (vu en Test 3), mais aucun code n'est donné — cherchez comment `PromotionService::getCurrentPrice()` est testé en `02-promotion.md`.

### Étape 2 — GREEN : faire passer le test au vert (20 min)

Modifiez **uniquement** le code métier pour que le test passe :

* Faites en sorte que `CartService` dépende de `PromotionService` (actuellement 1 seul argument `EntityManagerInterface` en `CartService.php:13`)
* Utilisez le prix courant dans `addProduct()` (`CartService.php:45`)

Ne donnez pas plus d’indice — trouvez l’injection et l’appel.

Vérifiez :
```bash
php vendor/bin/phpunit tests/Unit/CartServiceTest.php --filter testPromotionalPriceIsUsed
# doit être VERT
php vendor/bin/phpstan analyse --no-progress  # doit rester vert
```

### Étape 3 — REFACTOR & vérification globale (15 min)

* Vérifiez si `OrderService.php:56` a le même défaut (prix commande) — corrigez si besoin.
* Extraire un helper `createProductWithPromo()` si vous répétez du code.
* Lancer tout :
```bash
php vendor/bin/phpunit --no-coverage   # tous verts
php vendor/bin/phpunit --testdox | grep assertions
```

## Livrable & vérification CI

* Le test doit être vert en local **et** dans le pipeline `unit-tests` (`TD_Etudiant/docs/ci-cd.md`, `.gitlab-ci.yml:99` / `.github/workflows/ci.yml:205`).
* Pensez aux 3 captures du `README.md` : `capture-assertions.png` doit montrer le nouveau test compté.

## Suite : c'est la dernière étape — pensez aux 3 captures obligatoires

Félicitations, vous avez terminé le TD ! N'oubliez pas les 3 captures obligatoires du `README.md` : `capture-merge-bloque.png`, `capture-assertions.png`, `capture-pipeline-global.png`.

> Retour : [README](README.md) | Précédent : [10-order-status.md](10-order-status.md) | Aide TDD : `TD_Etudiant/docs/phpunit.md`
