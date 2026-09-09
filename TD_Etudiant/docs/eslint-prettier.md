# ESLint + Prettier

> Lint et formatage automatique du code JavaScript et TypeScript.

## Objectif

- **ESLint** détecte les erreurs et les problèmes potentiels dans le code JS/TS
  (variables non utilisées, appels de fonctions incorrects, code mort, etc.).
- **Prettier** formate automatiquement le code (indentation, espaces, guillemets,
  points-virgules, etc.) pour un style uniforme.

## Pourquoi ici

- **Code frontend propre** : les assets JS gèrent le panier, le thème sombre, les toasts,
  le catalogue AJAX, le skeleton loading — autant de code qui peut contenir des bugs.
- **CI gate** : le pipeline vérifie que le JS est valide et formaté avant de continuer.
- **Standards modernes** : ESLint 9 avec la configuration flat (`eslint.config.js`)
  et Prettier 3 sont les versions actuelles.
- **Accessibilité** : les erreurs ESLint sur les patterns d'accessibilité (a11y) aident
  à respecter les normes WCAG.

## Configuration

| Fichier | Outil | Rôle |
|---------|-------|------|
| `eslint.config.js` | ESLint | Règles de lint (flat config) |
| `.prettierrc` | Prettier | Règles de formatage |
| `.prettierignore` | Prettier | Fichiers exclus du formatage |
| `package.json` | npm | Scripts et dépendances |

## Comment lancer en local

```bash
# Lint JavaScript
npm run lint

# Corriger automatiquement les erreurs de lint
npm run lint:fix

# Vérifier le formatage (sans modifier)
npm run format:check

# Corriger le formatage automatiquement
npm run format

# Les deux à la fois (lint + format)
npm run lint && npm run format:check
```

## Dans le pipeline CI

```bash
npm install
npm run lint
npm run format:check
```

Si ESLint trouve des erreurs ou Prettier détecte des différences de formatage, le pipeline
est **rouge**.

## Règles ESLint principales

| Catégorie | Règle | Impact |
|-----------|-------|--------|
| **Erreurs** | `no-undef` | Variables non définies |
| **Avertissements** | `no-unused-vars` | Variables déclarées mais non utilisées |
| **Style** | `prefer-const` | Utiliser `const` quand la variable n'est pas réassignée |
| **A11y** | `jsx-a11y/*` | Accessibilité (si JSX utilisé) |

## Règles Prettier principales

| Option | Valeur | Description |
|--------|--------|-------------|
| `semi` | `true` | Point-virgule en fin de ligne |
| `singleQuote` | `true` | Guillemets simples |
| `tabWidth` | `2` | 2 espaces par indentation |
| `trailingComma` | `es5` | Virgule finale (ES5 syntax) |
| `printWidth` | `120` | Largeur max de 120 caractères |

## Fichiers JS concernés

```
assets/js/
├── cart.js           # Gestion du panier
├── theme.js          # Thème sombre/clair
├── toasts.js         # Notifications toast
├── catalog.js        # Catalogue AJAX
├── wishlist.js       # Liste de souhaits
├── track-order.js    # Suivi de commande
└── skeleton.js       # Effet de chargement
```

## Ressources

- [Documentation ESLint](https://eslint.org/docs/latest/)
- [ESLint flat config](https://eslint.org/docs/latest/use/configure/configuration-files)
- [Documentation Prettier](https://prettier.io/docs/en/)
- [Intégration ESLint + Prettier](https://prettier.io/docs/en/integrating-with-linters.html)
