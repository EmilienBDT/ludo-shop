# Cahier des charges — Tests à implémenter

> **Objectif** : écrire 10 tests automatisés qui couvrent les fonctionnalités critiques
> de LudoShop. Chaque test doit passer en local **et** dans le pipeline CI.
>
> **Public** : étudiants IUT, jamais ou peu familiarisés avec PHPUnit.
>
> **Durée estimée** : 6 à 8 heures.
>
> **Ordre** : du plus simple au plus complexe. Suivez les tests dans l'ordre.

---

## Règles générales

1. Chaque fichier de test commence par `declare(strict_types=1);`.
2. Les tests **unitaires** étendent `PHPUnit\Framework\TestCase`.
3. Les tests **fonctionnels** étendent `FunctionalTestCase` (qui réinitialise la base
   et charge les fixtures avant chaque test).
4. Les fixtures sont chargées automatiquement. Les utilisateurs de démo :
    - `admin@example.com` / `Admin123!` — administrateur
    - `client@example.com` / `Client123!` — client standard
    - `minor@example.com` / `Minor123!` — mineur
    - `root@root.com` / `Root123!` — super-admin (secours)
5. Les produits de démo : `CAT-001` (Catan), `LIM-001` (Limite Limite — produit mature).
6. Lancer les tests : `php vendor/bin/phpunit --no-coverage`.
7. Un test = une seule responsabilité. Pas de tests « fourre-tout ».

---

## Test 1 — Produit : vérification d'état (unitaire)

> **Complexité** : très faible. Aucune dépendance, aucune BDD, aucune injection.
> C'est le test le plus simple du projet — idéal pour comprendre la mécanique.

### Fichier

`tests/Unit/ProductTest.php` *(fichier à créer)*

### Objectif

Tester les méthodes simples d'une Entity `Product` : `isMature()`, `isAvailable()`,
etc. Ces méthodes contiennent de la logique conditionnelle qui peut casser silencieusement.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testIsMatureReturnsTrueWhenFlagged` | `isMature = true` | `true` |
| à créer `testIsMatureReturnsFalseByDefault` | `isMature = false` | `false` |
| (fourni) `testIsAvailableWhenActiveAndInStock` | `isActive = true, stock > 0` | `true` |
| à créer `testIsNotAvailableWhenInactive` | `isActive = false` | `false` |
| à créer `testIsNotAvailableWhenOutOfStock` | `stock = 0` | `false` |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testIsMatureReturnsTrueWhenFlagged(): void
    {
        $product = new Product();
        $product->setIsMature(true);

        $this->assertTrue($product->isMature());
    }

    public function testIsAvailableWhenActiveAndInStock(): void
    {
        $product = new Product();
        $product->setIsActive(true);
        $product->setStock(10);

        $this->assertTrue($product->isAvailable());
    }

    // ... autres méthodes
}
```

### Points clés

- C'est le test le plus simple : aucune injection de dépendance, aucune BDD.
- Idéal pour commencer et comprendre la mécanique de PHPUnit.
- Si `isAvailable()` n'existe pas sur l'Entity, vérifiez les méthodes existantes
  dans `src/Entity/Product.php`.

### Vérification

```bash
php vendor/bin/phpunit tests/Unit/ProductTest.php
```

---

## Test 2 — PromotionService::isOnPromotion() (unitaire)

> **Complexité** : faible. Un service avec une logique métier, pas de mock nécessaire.
> Plus de scénarios que le test 1, mais même principe : `new Service()` + assertions.

### Fichier

`tests/Unit/PromotionServiceTest.php` *(fichier à créer)*

### Objectif

Tester la logique métier de calcul de promotion **sans base de données**. Le service
`PromotionService` détermine si un produit est en promotion selon la date courante,
le prix promo, et les dates de début/fin.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testReturnsNormalPriceWhenNoPromotion` | Pas de dates promo définies | Prix normal, `false` |
| à créer `testReturnsPromoPriceDuringPeriod` | Date courante dans la période | Prix promo, `true` |
| à créer `testReturnsNormalPriceBeforePromotionPeriod` | Date courante avant le début | Prix normal, `false` |
| à créer `testReturnsNormalPriceAfterPromotionPeriod` | Date courante après la fin | Prix normal, `false` |
| à créer `testPromoPriceEqualToNormalIsNotActive` | Prix promo = prix normal | Prix normal, `false` |
| à créer `testPromoPriceGreaterThanNormalIsNotActive` | Prix promo > prix normal | Prix normal, `false` |
| à créer `testInvertedDatesAreNotActive` | Date de début > date de fin | `false` |
| à créer `testBoundaryStartIsIncluded` | Date = exactement le début de la promo | `true` |
| à créer `testBoundaryEndIsIncluded` | Date = exactement la fin de la promo | `true` |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Service\PromotionService;
use PHPUnit\Framework\TestCase;

class PromotionServiceTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        $this->service = new PromotionService();
    }

    public function testReturnsNormalPriceWhenNoPromotion(): void
    {
        $product = $this->createProduct(50.00);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    // ... autres méthodes

    private function createProduct(float $price, ?float $promoPrice = null): Product
    {
        $product = new Product();
        $product->setName('Test Product')->setPrice($price);

        if (null !== $promoPrice) {
            $product->setPromoPrice($promoPrice);
            $product->setPromoStartsAt(new \DateTimeImmutable('2026-08-01 00:00:00'));
            $product->setPromoEndsAt(new \DateTimeImmutable('2026-08-31 23:59:59'));
        }

        return $product;
    }
}
```

### Points clés

- Pas de `FunctionalTestCase`, pas de BDD, pas de kernel Symfony.
- La méthode `createProduct()` est un helper privé qui fabrique un `Product` en mémoire.
- On teste `getCurrentPrice()` **et** `isOnPromotion()` dans le même test quand c'est logique.
- Les bornes (début et fin inclus) sont critiques : un bug de `>=` vs `>` casse tout.

### Vérification

```bash
php vendor/bin/phpunit tests/Unit/PromotionServiceTest.php
```

---

## Test 3 — Panier : calcul du total (unitaire)

> **Complexité** : moyenne. Introduction aux **stubs** : on simule les dépendances
> Doctrine pour isoler le service. C'est le concept le plus difficile des tests unitaires.

### Fichier

`tests/Unit/CartServiceTest.php` *(fichier à créer)*

### Objectif

Tester le calcul du total du panier. `CartService::getTotal()` additionne
`(prix × quantité)` pour chaque item. C'est la base de tout e-commerce.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testEmptyCartReturnsZero` | Panier vide | `0` |
| (fourni) `testSingleItemReturnsCorrectTotal` | 1 produit × 1 | Prix du produit |
| à créer `testMultipleItems` | 2 produits différents | Somme des sous-totaux |
| à créer `testQuantityMultiplier` | 1 produit × 3 | Prix × 3 |
| à créer `testPromotionalPriceIsUsed` | Produit en promo | Prix promo × quantité |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

> **Note** : `CartService` utilise Doctrine (`EntityManagerInterface`).
> En mode unitaire, il faut ** stubber cette dépendance :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $this->service = new CartService($em);
    }

    public function testEmptyCartReturnsZero(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $this->assertSame(0.0, $this->service->getTotal($cart));
    }

    public function testSingleItemReturnsCorrectTotal(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);
        $cart->addItem($item);

        $this->assertSame(25.00, $this->service->getTotal($cart));
    }

    // ... autres méthodes
}
```

### Points clés

- `Cart` exige un `User` en constructeur — il faut un stub (`createStub`) pour le fabriquer.
- `CartItem` exige un `Product` en constructeur.
- La méthode de calcul s'appelle `getTotal()` (pas `calculateSubtotal()`).
- Utilisez `createStub()` (et non `createMock()`) quand vous n'avez pas d'attente
  sur les méthodes de l'objet fictif — sinon PHPUnit émet des notices.
- `CartService` ne prend qu'un seul argument : `EntityManagerInterface $em`
  (pas de `ProductRepository`).

### Vérification

```bash
php vendor/bin/phpunit tests/Unit/CartServiceTest.php
```

---

## Test 4 — Validation du schéma Doctrine (fonctionnel)

> **Complexité** : très faible pour un test fonctionnel. Une seule assertion, pas de HTTP,
> pas de fixtures à manipuler. Idéal pour découvrir `FunctionalTestCase` sans se compliquer.

### Fichier

`tests/Functional/SchemaValidationTest.php` *(fichier à créer)*

### Objectif

Vérifier que le schéma de la base de données est synchronisé avec les mappings Doctrine.
Ce test n'est pas un test de code métier mais un test de **cohérence infrastructure** :
si un étudiant modifie une Entity sans créer de migration, ce test le détecte.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testSchemaIsSynchronized` | Schéma BDD vs mappings Doctrine | Pas d'erreur |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;

class SchemaValidationTest extends FunctionalTestCase
{
    public function testSchemaIsSynchronized(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->entityManager();

        $validator = new SchemaValidator($em);

        $this->assertTrue(
            $validator->schemaInSyncWithMetadata(),
            'Le schéma Doctrine n\'est pas synchronisé avec les métadonnées.',
        );
    }
}
```

### Points clés

- `SchemaValidator::schemaInSyncWithMetadata()` retourne `true` si le schéma est OK,
  `false` s'il est désynchronisé.
- Ce test est **très rapide** (pas de BDD, juste une vérification de métadonnées).
- Dans le pipeline CI, cette vérification est aussi faite via
  `php bin/console doctrine:schema:validate` — mais l'avantage du test PHPUnit
  est qu'il apparaît dans le rapport de couverture et peut être exécuté partiellement.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/SchemaValidationTest.php
```

---

## Test 5 — Commande console : app:fixtures:check (fonctionnel)

> **Complexité** : faible+. Introduction à `CommandTester` : un outil PHPUnit dédié aux
> commandes Symfony Console. Plus onéreux qu'un GET simple, mais conceptuellement isolé.

### Fichier

`tests/Functional/FixturesCheckCommandTest.php` *(fichier à créer)*

### Objectif

Tester la commande console `app:fixtures:check` qui vérifie la cohérence des données
de démo. Un test de commande = un test atypique qui montre qu'on peut tester autre chose
qu'une page web.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testCommandPassesOnValidFixtures` | Fixtures valides | Exit code 0, message de succès |
| (fourni) `testCommandFailsOnInvalidData` | Produit mature avec âge min incorrect | Exit code 1, message d'erreur |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Command\FixturesCheckCommand;
use App\Entity\Product;
use Symfony\Component\Console\Tester\CommandTester;

class FixturesCheckCommandTest extends FunctionalTestCase
{
    private function getTester(): CommandTester
    {
        return new CommandTester(new FixturesCheckCommand(
            $this->entityManager(),
        ));
    }

    public function testCommandPassesOnValidFixtures(): void
    {
        $tester = $this->getTester();

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('vérifications', $tester->getDisplay());
    }

    public function testCommandFailsOnInvalidData(): void
    {
        // Corrompre les fixtures : produit mature avec âge min trop bas
        $product = $this->repository(Product::class)->findOneBy([]);
        $this->assertNotNull($product);

        $product->setIsMature(true);
        $product->setMinAge(14);
        $this->entityManager()->flush();
        $this->entityManager()->clear();

        $tester = $this->getTester();
        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('mature', $tester->getDisplay());
    }
}
```

### Points clés

- `CommandTester` est un outil PHPUnit dédié aux commandes Symfony Console.
- On construit la commande **manuellement** en lui injectant ses dépendances.
- `tester->execute([])` exécute la commande avec les arguments donnés.
- `tester->getDisplay()` retourne la sortie console (comme un `echo`).
- `$exitCode === 0` = succès, `$exitCode === 1` = échec.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/FixturesCheckCommandTest.php
```

---

## Test 6 — Authentification : login réussi + échoué (fonctionnel)

> **Complexité** : moyenne. Premier test avec HTTP réel : GET pour afficher le formulaire,
> POST avec CSRF token, vérification des redirections et du contenu HTML.

### Fichier

`tests/Functional/LoginTest.php` *(fichier à créer)*

### Objectif

Tester le formulaire de connexion : un login valide doit rediriger vers le catalogue,
un login invalide doit rester sur la page de login avec un message d'erreur.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testLoginPageIsAccessible` | GET /login | 200 OK, formulaire(s) présent(s) |
| (fourni) `testSuccessfulLoginRedirectsToCatalog` | POST avec bonnes credentials | Redirection vers / |
| (fourni) `testFailedLoginShowsError` | POST avec mauvais mot de passe | Reste sur /login, message d'erreur |
| à créer `testLogoutRedirectsToHome` | GET /logout | Redirection vers / |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

class LoginTest extends FunctionalTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('form')->count());
    }

    public function testSuccessfulLoginRedirectsToCatalog(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        $this->client->request('POST', '/login', [
            '_username' => 'client@example.com',
            '_password' => 'Client123!',
            '_csrf_token' => $csrfToken,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_home');
    }

    public function testFailedLoginShowsError(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        $this->client->request('POST', '/login', [
            '_username' => 'client@example.com',
            '_password' => 'wrong-password',
            '_csrf_token' => $csrfToken,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials');
    }
}
```

### Points clés

- Le **CSRF token** est obligatoire pour tout POST. On l'extrait du formulaire GET.
- `$this->client->followRedirect()` suit la redirection HTTP pour vérifier la page cible.
- `$this->login('email')` est un raccourci qui évite le formulaire — utile pour les tests
  qui ont juste besoin d'être connectés, mais pas ici (on teste justement le formulaire).
- Pour `testLogoutRedirectsToHome` : la cible de déconnexion est `app_home`
  (configurée dans `config/packages/security.yaml` sous `logout.target`).
  Vérifiez avec `$this->assertRouteSame('app_home')` après `followRedirect()`.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/LoginTest.php
```

---

## Test 7 — Catalogue : affichage + filtres (fonctionnel)

> **Complexité** : moyenne+. Introduction aux fixtures Doctrine (`persist`/`flush`), au
> login multi-rôles, et aux assertions de contenu HTML avec filtres query params.

### Fichier

`tests/Functional/CatalogTest.php` *(fichier à créer)*

### Objectif

Tester que la page catalogue affiche les produits, que les filtres fonctionnent,
et que les produits inactifs/matures sont correctement masqués.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testCatalogPageDisplaysProducts` | GET /products | 200, Catan visible |
| à créer `testFilterByCategory` | GET /categories/{slug} | Seuls les produits de la catégorie |
| (fourni) `testInactiveProductIsHidden` | Produit inactif en BDD | Non visible dans le catalogue |
| (fourni) `testMatureProductHiddenForMinor` | Login minor + GET /products/{id} | 404 (produit masqué) |
| (fourni) `testMatureProductVisibleForAdult` | Login client + GET /products/{id} | 200, produit visible |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;

class CatalogTest extends FunctionalTestCase
{
    public function testCatalogPageDisplaysProducts(): void
    {
        $this->client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testInactiveProductIsHidden(): void
    {
        $inactive = new Product();
        $inactive->setName('Produit Test');
        $inactive->setReference('TEST-999');
        $inactive->setPrice(10.00);
        $inactive->setStock(5);
        $inactive->setIsActive(false);
        $this->entityManager()->persist($inactive);
        $this->entityManager()->flush();

        $this->client->request('GET', '/products');

        $this->assertSelectorTextNotContains('body', 'Produit Test');
    }

    public function testMatureProductHiddenForMinor(): void
    {
        $this->login('minor@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'LIM-001']);
        $this->assertNotNull($product);

        $this->client->request('GET', '/products/'.$product->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMatureProductVisibleForAdult(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'LIM-001']);
        $this->assertNotNull($product);

        $this->client->request('GET', '/products/'.$product->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Limite Limite');
    }
}
```

### Points clés

- `assertSelectorTextContains('body', 'Catan')` vérifie que le texte est dans le HTML rendu.
- `$this->entityManager()->persist()` + `flush()` insère directement en BDD pour le test.
- `$this->entityManager()->clear()` vide le cache Doctrine pour re-lire depuis la BDD.
- Les produits matures sont paginés (9 par page). Pour les tester, ciblez directement
  `/products/{id}` (retourne 404 si le produit est masqué pour le rôle connecté).
- La route des catégories est `/categories/{slug}` — vérifiez `src/Controller/CategoryController.php`.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/CatalogTest.php
```

---

## Test 8 — Panier : ajout, modification, suppression (fonctionnel)

> **Complexité** : moyenne+. Introduction à la **session utilisateur** : le panier persiste
> entre les requêtes. Plusieurs étapes POST avec des dépendances entre elles.

### Fichier

`tests/Functional/CartTest.php` *(fichier à créer)*

### Objectif

Tester le parcours utilisateur du panier : ajouter un produit, modifier la quantité,
supprimer un article. C'est un parcours multi-étapes qui implique la session.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testAddProductToCart` | POST /cart/add/{id} avec quantité | Redirection, produit dans le panier |
| à créer `testCartQuantityIsUpdated` | POST /cart/items/{id}/update | Nouvelle quantité affichée |
| à créer `testRemoveProductFromCart` | POST /cart/items/{id}/remove | Produit plus présent |
| (fourni) `testCartShowsCorrectTotal` | Après ajout de produits | Total = somme des prix × quantités |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

> Le patron dépend de la route de votre `CartController`. Adaptez les URLs.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;
use App\Service\CartService;

class CartTest extends FunctionalTestCase
{
    public function testAddProductToCart(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testCartShowsCorrectTotal(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 2);

        $this->client->request('GET', '/cart');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#cart-total');
    }
}
```

### Points clés

- Le panier est en **session** : l'utilisateur doit être connecté.
- La route d'ajout est `POST /cart/add/{id}` où `{id}` est l'identifiant du produit (pas un champ de formulaire).
- La route de mise à jour est `POST /cart/items/{id}/update` où `{id}` est l'identifiant de l'item.
- La route de suppression est `POST /cart/items/{id}/remove`.
- `$this->repository(Product::class)->findOneBy(...)` cherche directement en BDD.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/CartTest.php
```

---

## Test 9 — Checkout : parcours complet de commande (fonctionnel)

> **Complexité** : élevée. Parcours en 4 étapes : panier → checkout (adresse) → paiement
> → confirmation. Chaque étape dépend de la précédente. C'est le test business-critical.

### Fichier

`tests/Functional/CheckoutTest.php` *(fichier à créer)*

### Objectif

Tester le parcours complet : panier → checkout (adresse) → paiement → confirmation.
C'est le test le plus business-critical de l'application.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| à créer `testCheckoutPageRequiresCart` | GET /checkout sans panier | Redirection ou message d'erreur |
| (fourni) `testCheckoutCreatesOrder` | POST /checkout avec adresse | Order créée avec statut `pending` |
| (fourni) `testPaymentCreatesPaidOrder` | POST /orders/{id}/pay | Statut passe à `paid` |
| à créer `testConfirmationPageIsDisplayed` | Après paiement | Page de confirmation avec numéro |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

> Ce test est le plus complexe. Il enchaîne plusieurs étapes.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

class CheckoutTest extends FunctionalTestCase
{
    public function testCheckoutCreatesOrder(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        // 1. Ajouter un produit au panier
        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        // 2. Remplir le formulaire de checkout
        $crawler = $this->client->request('GET', '/checkout');
        $token = $crawler->filter('input[name="checkout_form[_token]"]')->attr('value');

        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        // 3. Vérifier que l'order est créée
        $this->assertResponseRedirects();
        $order = $this->repository(Order::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->getStatus()->value);
    }

    public function testPaymentCreatesPaidOrder(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        // Setup panier + checkout (comme ci-dessus)
        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        $crawler = $this->client->request('GET', '/checkout');
        $token = $crawler->filter('input[name="checkout_form[_token]"]')->attr('value');

        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        $order = $this->repository(Order::class)->findOneBy(['user' => $user]);

        // 4. Payer la commande
        $this->client->request('POST', '/orders/'.$order->getId().'/pay');

        $this->assertResponseRedirects();
        $this->entityManager()->clear();

        $paidOrder = $this->repository(Order::class)->find($order->getId());
        $this->assertSame('paid', $paidOrder->getStatus()->value);
    }
}
```

### Points clés

- Ce test reprend le **même patron** que `OrderConfirmationEmailTest::createPaidOrder()`.
- `$this->client->getContainer()->get(CartService::class)` récupère le service depuis le container.
- `$this->entityManager()->clear()` vide le cache Doctrine avant de re-lire l'entité.
- Le CSRF token est **obligatoire** pour le formulaire de checkout.
- Si un test est trop long, extrayez la logique de setup dans une méthode privée.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/CheckoutTest.php
```

---

## Test 10 — Changement de statut de commande par l'admin (fonctionnel)

> **Complexité** : très élevée. Combine **tout** : le parcours checkout (test 9), la
> sécurité (403), l'historique en BDD, et la vérification d'emails via le profiler.
> C'est le test le plus complet du projet.

### Fichier

`tests/Functional/OrderStatusTest.php` *(fichier à créer)*

### Objectif

Tester le workflow de modération : seul un admin peut changer le statut d'une commande,
l'historique est créé, et un email de notification est envoyé.

### Ce que le test doit vérifier

| Méthode de test | Scénario | Résultat attendu |
|----------------|----------|------------------|
| (fourni) `testAdminCanChangeStatus` | Login admin + POST changement de statut | Statut mis à jour |
| (fourni) `testClientCannotChangeStatus` | Login client + POST changement de statut | 403 Forbidden |
| (fourni) `testStatusChangeCreatesHistoryEntry` | Après changement | Entrée dans `OrderStatusHistory` |
| (fourni) `testStatusChangeSendsEmail` | Après changement | Email envoyé (profiler) |

> **Légende** : (fourni) = code fourni dans le patron · à créer = à créer vous-même

### Patron à suivre

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use App\Entity\Product;
use App\Service\CartService;

class OrderStatusTest extends FunctionalTestCase
{
    private function createPaidOrder(): Order
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        $crawler = $this->client->request('GET', '/checkout');
        $token = $crawler->filter('input[name="checkout_form[_token]"]')->attr('value');

        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        $order = $this->repository(Order::class)->findOneBy(['user' => $user]);
        $this->client->request('POST', '/orders/'.$order->getId().'/pay');
        $this->entityManager()->clear();

        return $this->repository(Order::class)->find($order->getId());
    }

    private function changeStatus(Order $order, string $status): void
    {
        $crawler = $this->client->request('GET', '/admin/orders/'.$order->getId());
        $form = $crawler->filter('form[action*="status"]');
        $this->assertGreaterThan(0, $form->count(), 'Le formulaire de changement de statut est introuvable.');

        $this->client->submit($form->form(), [
            'order_status_form[status]' => $status,
        ]);

        $this->assertResponseRedirects();
    }

    public function testAdminCanChangeStatus(): void
    {
        $order = $this->createPaidOrder();

        $this->login('admin@example.com');
        $this->changeStatus($order, 'shipped');

        $this->entityManager()->clear();

        $updated = $this->repository(Order::class)->find($order->getId());
        $this->assertSame('shipped', $updated->getStatus()->value);
    }

    public function testClientCannotChangeStatus(): void
    {
        $order = $this->createPaidOrder();

        $this->login('client@example.com');

        $this->client->request('POST', '/admin/orders/'.$order->getId().'/status', [
            'order_status_form[status]' => 'shipped',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testStatusChangeCreatesHistoryEntry(): void
    {
        $order = $this->createPaidOrder();

        $this->login('admin@example.com');
        $this->changeStatus($order, 'shipped');

        $this->entityManager()->clear();

        $history = $this->repository(OrderStatusHistory::class)->findBy(
            ['order' => $this->repository(Order::class)->find($order->getId())],
            ['changedAt' => 'ASC'],
        );

        $this->assertGreaterThanOrEqual(1, count($history));
    }

    public function testStatusChangeSendsEmail(): void
    {
        $order = $this->createPaidOrder();

        $this->login('admin@example.com');
        $this->changeStatus($order, 'shipped');

        $profile = $this->client->getProfile();
        $this->assertNotNull($profile);
        $collector = $profile->getCollector('mailer');
        $messages = $collector->getEvents()->getMessages();

        $this->assertGreaterThanOrEqual(1, count($messages));
    }
}
```

### Points clés

- La méthode privée `createPaidOrder()` est **réutilisée** dans plusieurs tests (D.R.Y.).
- La méthode privée `changeStatus()` utilise `$this->client->submit()` pour soumettre
  le formulaire correctement — le champ s'appelle `order_status_form[status]` (pas `status`).
- `$this->assertResponseStatusCodeSame(403)` vérifie que l'accès est interdit.
- Les tests d'email utilisent le **profiler Symfony** (`getProfile()->getCollector('mailer')`).
- `$this->entityManager()->clear()` est **obligatoire** après un flush pour re-lire depuis la BDD.

### Vérification

```bash
php vendor/bin/phpunit tests/Functional/OrderStatusTest.php
```

---

## Récapitulatif

| # | Fichier | Type | Concept PHPUnit | Nb tests mini |
|---|---------|------|----------------|---------------|
| 1 | `tests/Unit/ProductTest.php` | Unitaire | Entity seule, assertions simples | 5 |
| 2 | `tests/Unit/PromotionServiceTest.php` | Unitaire | Service, dates, bornes | 9 |
| 3 | `tests/Unit/CartServiceTest.php` | Unitaire | Stubs, `createStub()` | 5 |
| 4 | `tests/Functional/SchemaValidationTest.php` | Fonctionnel | `SchemaValidator`, infrastructure | 1 |
| 5 | `tests/Functional/FixturesCheckCommandTest.php` | Fonctionnel | `CommandTester`, exit codes | 2 |
| 6 | `tests/Functional/LoginTest.php` | Fonctionnel | CSRF, redirects, formulaires | 4 |
| 7 | `tests/Functional/CatalogTest.php` | Fonctionnel | Fixtures, login multi-rôles, filtres | 5 |
| 8 | `tests/Functional/CartTest.php` | Fonctionnel | Session, ajout/suppression | 4 |
| 9 | `tests/Functional/CheckoutTest.php` | Fonctionnel | Parcours complet, emails | 4 |
| 10 | `tests/Functional/OrderStatusTest.php` | Fonctionnel | Sécurité, historique, emails, profiler | 4 |

**Total minimum** : 43 tests.

---

## Conseils

1. **Commencez par le test 1** (le plus simple) pour comprendre la mécanique.
2. **Lisez les fichiers existants** : chaque test du projet suit le même patron.
3. **Un test à la fois** : écrivez → lancez → corrigez → passez au suivant.
4. **Ne copiez-collez pas aveuglément** : comprenez ce que chaque ligne fait.
5. **Adaptez les URLs** : les routes exactes dépendent de vos controllers. Vérifiez
   avec `php bin/console debug:router`.
6. **En cas de doute** : lisez la [documentation PHPUnit](https://phpunit.readthedocs.io/)
   et la [documentation Symfony sur les tests](https://symfony.com/doc/7.4/testing.html).
