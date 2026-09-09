# Symfony

> Framework PHP pour la création d'applications web.

## Objectif

Symfony est le **framework** sur lequel repose toute l'application LudoShop. Il fournit
l'architecture MVC, le routage, les formulaires, la sécurité, l'ORM, le système d'emails, et
beaucoup d'autres composants réutilisables.

## Pourquoi ici

- **Standard de l'industrie** : Symfony est l'un des frameworks PHP les plus utilisés en
  entreprise. Le connaître est un atout professionnel.
- **Écosystème riche** : Doctrine (ORM), Twig (templates), Security (authentification),
  Mailer (emails), Form (formulaires) — tout est intégré.
- **Excellente documentation** : la [documentation officielle](https://symfony.com/doc/7.4)
  est complète et traduite en français.
- **Fonctionnalités CI** : Symfony inclut des outils de vérification (`lint:yaml`,
  `lint:twig`, `lint:container`) qui s'intègrent naturellement dans un pipeline CI.

## Comment ça fonctionne

```
Requête HTTP → Route → Controller → Service/Repository → Entity → Twig → Réponse HTML
```

| Composant | Rôle | Répertoire |
|-----------|------|------------|
| **Routes** | Mapent les URLs aux contrôleurs | `src/Controller/` |
| **Controllers** | Traitent les requêtes, appellent les services | `src/Controller/` |
| **Entities** | Représentent les données (modèle) | `src/Entity/` |
| **Repositories** | Requêtes vers la base de données | `src/Repository/` |
| **Services** | Logique métier | `src/Service/` |
| **Form Types** | Définissent les formulaires | `src/Form/` |
| **Templates** | Rendu HTML | `templates/` |
| **Config** | Paramètres de l'application | `config/` |

## Lancer en local

```bash
# Installer les dépendances
composer install

# Lancer le serveur de développement
symfony server:start
# → http://localhost:8000

# Ou avec PHP intégré
php -S 127.0.0.1:8000 -t public/
```

## Vérifications Symfony dans le pipeline CI

```bash
# Valider la configuration YAML
php bin/console lint:yaml config

# Valider les templates Twig
php bin/console lint:twig templates

# Valider le container (services, paramètres)
php bin/console lint:container
```

Ces commandes vérifient que la configuration est syntaxiquement correcte et que le container
de dépendances peut être construit. Une erreur ici empêche le démarrage de l'application.

## Ressources

- [Documentation Symfony 7.4](https://symfony.com/doc/7.4)
- [Tutoriels officiels](https://symfony.com/doc/current/the-fast-track/fr/index.html)
- [Symfony MakerBundle](https://symfony.com/bundles/maker-bundle/current/index.html)
