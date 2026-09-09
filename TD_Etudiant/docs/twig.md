# Twig

> Moteur de templates pour PHP, utilisé par Symfony pour le rendu HTML.

## Objectif

Twig sépare la **logique** (PHP/Controller) de la **présentation** (HTML/Template). Il offre
un système de templates **héritables**, d'**extensions** (filtres, fonctions), et l'**échappement
automatique** des sorties pour prévenir les failles XSS.

## Pourquoi ici

- **Sécurité** : l'échappement automatique empêche les injections XSS par défaut. Pas besoin
  d'oublier un `htmlspecialchars()` — Twig le fait pour vous.
- **Lisibilité** : la syntaxe `{% %}` / `{{ }}` est plus lisible que les alternatives PHP pures.
- **Héritage de templates** : un layout de base (`base.html.twig`) est étendu par chaque page.
- **Intégration Symfony** : les variables transmises par les controllers sont automatiquement
  disponibles dans les templates.

## Syntaxe de base

```twig
{# Variable #}
<h1>{{ product.name }}</h1>

{# Condition #}
{% if product.isMature %}
    <span class="badge">+18</span>
{% endif %}

{# Boucle #}
{% for item in cart.items %}
    <tr>
        <td>{{ item.product.name }}</td>
        <td>{{ item.quantity }}</td>
        <td>{{ item.unitPrice }} €</td>
    </tr>
{% endfor %}

{# Héritage #}
{% extends 'base.html.twig' %}

{% block title %}Catalogue{% endblock %}

{% block body %}
    {# Contenu de la page #}
{% endblock %}
```

## Fichiers principaux

| Fichier | Rôle |
|---------|------|
| `templates/base.html.twig` | Layout de base (header, footer, nav) |
| `templates/catalog/` | Pages du catalogue |
| `templates/cart/` | Pages du panier |
| `templates/order/` | Pages des commandes |
| `templates/admin/` | Pages du back-office |
| `templates/security/` | Pages d'authentification |
| `templates/email/` | Templates des emails |

## Comment lancer en local

```bash
# Valider tous les templates Twig
php bin/console lint:twig templates

# Valider un template spécifique
php bin/console lint:twig templates/catalog/index.html.twig
```

La commande `lint:twig` vérifie que les templates sont syntaxiquement corrects. Elle détecte
les erreurs de syntaxe, les variables inconnues, et les problèmes d'héritage.

## Dans le pipeline CI

```bash
php bin/console lint:twig templates
```

Si un template contient une erreur de syntaxe, le pipeline échoue. Cela garantit que le rendu
ne plantera jamais en production.

## Ressources

- [Documentation Twig](https://twig.symfony.com/doc/3.x/)
- [Filtres Twig](https://twig.symfony.com/doc/3.x/filters/index.html)
- [Extensions Twig](https://twig.symfony.com/doc/3.x/extensions/index.html)
