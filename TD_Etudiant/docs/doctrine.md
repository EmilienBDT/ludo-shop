# Doctrine ORM + SQLite

> ORM (Object-Relational Mapping) pour PHP + base de données légère intégrée à PHP.

## Objectif

**Doctrine ORM** permet de manipuler la base de données en utilisant des **objets PHP** au
lieu d'écrire du SQL directement. Chaque table est représentée par une **Entity** (classe PHP),
et chaque ligne par un **objet**.

**SQLite** est le moteur de base de données : zéro configuration, inclus avec PHP, idéal pour
un projet de développement et de formation.

## Pourquoi ici

- **Productivité** : les entities et migrations sont générées automatiquement (MakerBundle).
- **Sécurité** : Doctrine utilise des **requêtes paramétrées** qui protègent contre les
  injections SQL.
- **Portabilité** : SQLite ne nécessite aucun serveur. La base est un simple fichier
  (`var/data.db`).
- **CI friendly** : pas de service externe à démarrer dans le pipeline — la base est créée
  et peuplée en quelques secondes.
- **Migrations** : les changements de schéma sont versionnés et reproductibles.

## Architecture

```
Entity (PHP)  ←→  Table (SQL)
    ↕                  ↕
Repository      ←→    SELECT/INSERT/UPDATE/DELETE
    ↕
Service (logique métier)
```

| Concept | Description | Exemple |
|---------|-------------|---------|
| **Entity** | Classe PHP representant une table | `User`, `Product`, `Order` |
| **Repository** | Classe pour les requêtes | `ProductRepository::findActive()` |
| **Migration** | Fichier SQL versionné | `migrations/Version20260101.php` |
| **Fixture** | Données de démo reproductibles | `AppFixtures::load()` |

## Entités du projet

| Entity | Table | Description |
|--------|-------|-------------|
| `User` | `users` | Comptes utilisateurs |
| `Product` | `product` | Jeux de société |
| `Cart` / `CartItem` | `cart` / `cart_item` | Paniers |
| `Order` / `OrderItem` | `order` / `order_item` | Commandes |
| `Invoice` | `invoice` | Factures immuables |
| `Category` | `category` | Catégories de jeux |
| `Review` | `review` | Avis clients |
| `WishlistItem` | `wishlist_item` | Liste de souhaits |
| `StockAlert` | `stock_alert` | Alertes de stock |
| `Address` | `address` | Adresses de livraison |
| `OrderStatusHistory` | `order_status_history` | Historique des statuts |
| `AuditLog` | `audit_log` | Journal d'audit (append-only) |

## Comment lancer en local

```bash
# Créer la base de données (si elle n'existe pas)
php bin/console doctrine:database:create --if-not-exists

# Appliquer les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Charger les données de démo (produits, utilisateurs)
php bin/console doctrine:fixtures:load --no-interaction

# Vérifier que le schéma est à jour
php bin/console doctrine:schema:validate --skip-sync
```

## Dans le pipeline CI

```bash
# Valider le schéma Doctrine
php bin/console doctrine:schema:validate --skip-sync

# Appliquer les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Charger les fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Vérifier la cohérence des fixtures
php bin/console app:fixtures:check
```

La commande `app:fixtures:check` vérifie que les données de démo sont cohérentes
(références uniques, produits matures réservés aux adultes, etc.).

## Ajouter une nouvelle Entity

```bash
# Générer une entity avec le Maker
php bin/console make:entity Product

# Générer la migration
php bin/console make:migration

# Appliquer la migration
php bin/console doctrine:migrations:migrate
```

## Ressources

- [Documentation Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/3.0/index.html)
- [Documentation Doctrine DBAL (SQLite)](https://www.doctrine-project.org/projects/doctrine-dbal/en/4.0/index.html)
- [Symfony Doctrine Bundle](https://symfony.com/doc/current/doctrine.html)
