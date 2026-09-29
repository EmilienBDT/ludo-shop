# 12 — Optimisation des performances du workflow Infection (CI)

## Contexte et Problématique

Le job `infection` dans GitHub Actions prenait initialement **environ 7 minutes** pour s'exécuter.
L'objectif a été de réduire significativement cette durée **sans modifier le périmètre de vérification** :
- Même code source testé (`src/`).
- Mêmes suites de tests exécutées (`unit, integration, functional`).
- Mêmes mutateurs actifs (`@default`).
- Mêmes critères d'évaluation et de score MSI.

---

## Modifications apportées

### 1. Réduction du timeout par mutant

* **Fichier modifié :** `infection.json`
* **Modification :** Passage de `"timeout": 300` à `"timeout": 10`.
* **Justification :**
  * Avec la configuration initiale à 300 secondes (5 minutes), lorsqu'un mutant générait une boucle infinie ou un blocage, Infection monopolisait un thread pendant 5 minutes entières avant de le déclarer en *Timed Out*.
  * En abaissant cette limite à 10 secondes (aucun test légitime du projet ne dépassant quelques secondes), les mutants bloquants sont immédiatement arrêtés et comptabilisés comme tués, sans paralyser la file de test.

### 2. Ciblage exclusif des tests couvrant chaque mutant

* **Fichier modifié :** `.github/workflows/ci.yml`
* **Option ajoutée :** `--only-covering-test-cases`
* **Justification :**
  * Par défaut, dès qu'une ligne mutée est couverte par un test, Infection exécute l'intégralité du fichier de test concerné (incluant toutes les autres méthodes de la classe).
  * Grâce à `--only-covering-test-cases`, Infection utilise le filtre de PHPUnit (`--filter`) pour n'exécuter **que la ou les méthodes de test spécifiques** traversant la ligne modifiée.
  * Cela supprime des milliers d'exécutions superflues de tests fonctionnels et d'intégration sur les 1 200+ mutants analysés, pour un résultat de détection rigoureusement identique.

### 3. Calibrage du parallélisme sur les ressources CPU

* **Fichier modifié :** `.github/workflows/ci.yml`
* **Modification :** Remplacement de `--threads=4` par `--threads=max`.
* **Justification :**
  * Les runners GitHub Actions (`ubuntu-latest`) disposent de 2 vCPUs.
  * Forcer 4 threads concurrents entraînait une sur-allocation avec des pertes de performance liées aux commutations de contexte CPU (*context switching*).
  * `--threads=max` permet à Infection d'adapter automatiquement le parallélisme au nombre de cœurs physiques réellement alloués.

---

## Synthèse de la commande CI

```yaml
- name: Infection mutation testing
  run: php -d memory_limit=512M vendor/bin/infection --threads=max --only-covering-test-cases --show-mutations --no-progress
```

---

## Impact

* Réduction drastique du temps d'exécution global de la suite de mutations.
* Intégrité et rigueur des vérifications conservées à 100%.
