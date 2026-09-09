# pa11y

> Tests d'accessibilité automatisés — vérifie le respect des normes WCAG.

## Objectif

pa11y **charge les pages** de l'application dans un navigateur headless (sans interface)
et vérifie qu'elles respectent les normes d'accessibilité **WCAG 2.1** (niveau AA).
Il détecte les problèmes comme :

- Images sans attribut `alt`
- Labels de formulaire manquants
- Contraste de couleurs insuffisant
- Éléments non accessibles au clavier
- ARIA mal utilisé

## Pourquoi ici

- **Inclusivité** : l'application doit être utilisable par tous, y compris les personnes
  en situation de handicap.
- **Normes WCAG** : respecter les normes d'accessibilité est une obligation légale
  dans de nombreux contextes (établissements publics, services en ligne).
- **CI automatique** : pa11y vérifie l'accessibilité à chaque push, ce qui évite les
  régressions silencieuses.
- **Éducation** : sensibiliser les étudiants à l'accessibilité dès le début.

## Configuration

Le fichier `.pa11yci.json` définit les URLs à tester et les options :

```json
{
    "defaults": {
        "standard": "WCAG2AA",
        "timeout": 10000,
        "wait": 2000,
        "reporters": ["cli"]
    },
    "urls": [
        "http://localhost:8000/",
        "http://localhost:8000/catalog",
        "http://localhost:8000/login",
        "http://localhost:8000/register"
    ]
}
```

## Comment lancer en local

```bash
# Lancer les tests d'accessibilité (nécessite le serveur en marche)
symfony server:start &
npm run a11y

# Ou avec pa11y-ci directement
npx pa11y-ci

# Tester une URL spécifique
npx pa11y-ci --config .pa11yci.json http://localhost:8000/catalog

# Rapport JSON
npx pa11y-ci --reporter json > var/a11y-report.json
```

> **Prérequis** : le serveur de développement doit être lancé (`symfony server:start`)
> car pa11y charge les pages dans un navigateur.

## Dans le pipeline CI

```bash
symfony server:start -d
sleep 3
npm run a11y
symfony server:stop
```

Le pipeline démarre le serveur, exécute pa11y, puis arrête le serveur. Si pa11y
détecte des violations WCAG critiques, le pipeline est **rouge**.

## Types de violations détectées

| Catégorie | Exemple | WCAG |
|-----------|---------|------|
| **Images** | `<img>` sans `alt` | 1.1.1 Non-text Content |
| **Formulaires** | `<input>` sans `<label>` | 1.3.1 Info and Relationships |
| **Contraste** | Texte trop peu contrasté | 1.4.3 Contrast (Minimum) |
| **Navigation** | Éléments non atteignables au clavier | 2.1.1 Keyboard |
| **Titres** | Saut de niveau de titre (h1 → h3) | 1.3.1 Info and Relationships |
| **ARIA** | Rôle ARIA inexistant | 4.1.2 Name, Role, Value |

## Niveaux WCAG

| Niveau | Description | Obligatoire ? |
|--------|-------------|---------------|
| **A** | Accessibilité de base | Oui |
| **AA** | Standard recommandé (niveau de LudoShop) | Recommandé |
| **AAA** | Accessibilité renforcée | Optionnel |

## Ressources

- [Documentation pa11y](https://pa11y.org/)
- [WCAG 2.1](https://www.w3.org/TR/WCAG21/)
- [ pa11y-ci GitHub](https://github.com/pa11y/pa11y-ci)
- [Contraste de couleurs](https://webaim.org/resources/contrastchecker/)
