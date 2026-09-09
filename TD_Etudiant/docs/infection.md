# Infection

> Mutation testing — évalue la qualité des tests en injectant des bugs artificiels.

## Objectif

Infection **modifie automatiquement** le code source (« mutations ») puis exécute les tests.
Si un test **échoue**, la mutation est « tuée » (= le test l'a détectée). Si le test
**passe toujours**, la mutation est « survécue » (= le test ne couvre pas ce cas).

## Pourquoi ici

- **Qualité des tests** : la couverture de code seule ne suffit pas. Un test qui passe
  toujours est inutile. Infection vérifie que les tests **vraiment** détectent les bugs.
- **Phase 2** : quand l'enseignant injecte des bugs, les mutations d'Infection montrent
  si les tests existants sont capables de les attraper.
- **Indicateur objectif** : le Mutation Score Indicator (MSI) donne un score chiffré
  de la qualité des tests.

## Comment ça fonctionne

```
Code source          →  Mutation 1 : if (x > 0)  →  if (false)
                    →  Mutation 2 : return $a    →  return $b
                    →  Mutation 3 : $x + 1       →  $x - 1

Chaque mutation est exécutée avec les tests :
  - Test échoue  →  Mutation tuée (le test est efficace)
  - Test passe    →  Mutation survécue (le test a un trou)
```

## Métriques

| Métrique | Description | Seuil recommandé |
|----------|-------------|------------------|
| **MSI** | % de mutations tuées | >= 80 % |
| **Covered MSI** | % de mutations dans le code couvert tuées | >= 90 % |
| **Mutation Code MSI** | % de mutations générées avec succès | — |

## Configuration

Le fichier `infection.json` définit les mutations à appliquer :

```json
{
    "source": ["src"],
    "timeout": 10,
    "testFramework": "phpunit",
    "logs": {
        "text": "var/infection.log"
    },
    "mutators": {
        "@default": true
    }
}
```

## Comment lancer en local

```bash
# Lancer toutes les mutations
php vendor/bin/infection --threads=4

# Avec affichage en direct
php vendor/bin/infection --threads=4 --show-mutations

# Avec rapport HTML
php vendor/bin/infection --threads=4 --log-xml=var/infection.xml

# Avec un temps de timeout spécifique
php vendor/bin/infection --threads=4 --timeout=30

# Mode debug (affiche les mutations tuées)
php vendor/bin/infection --threads=4 -v
```

> **Windows** : utilisez `php vendor\bin\infection`.

> **Note** : les tests doivent passer sans couverture (`--no-coverage`) pour Infection.
> Ne lancez pas Infection après PHPUnit avec `--coverage`.

## Dans le pipeline CI

```bash
php vendor/bin/infection --threads=4 --no-progress
```

Le pipeline ne vérifie pas le MSI (il varie selon les codebases), mais Infection affiche
le score. Si Infection ne peut pas s'exécuter, le pipeline est **rouge**.

## Types de mutations courantes

| Type | Exemple | Ce qu'on teste |
|------|---------|----------------|
| **Arithmetic** | `$x + 1` → `$x - 1` | Calculs mathématiques |
| **Boolean** | `true` → `false` | Conditions |
| **Conditional** | `if ($a)` → `if (!$a)` | Logique conditionnelle |
| **Return value** | `return $x` → `return null` | Valeurs de retour |
| **Array** | `count($arr)` → `count($arr) - 1` | Opérations sur les tableaux |

## Ressources

- [Documentation Infection](https://infection.github.io/)
- [PHP mutate](https://infection.github.io/guide/usage.html)
- [Infection GitHub](https://github.com/infection/infection)
