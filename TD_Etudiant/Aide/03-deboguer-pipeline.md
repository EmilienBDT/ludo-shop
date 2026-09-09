# 03 — Débuguer une pipeline rouge

> Étape 3 : quand le pipeline échoue, voici comment lire les logs et corriger.

## Le diagnostic en 4 étapes

### Étape 1 — Identifier l'étape en erreur

**GitHub Actions** : allez dans l'onglet **"Actions"** → cliquez sur le pipeline en cours →
cliquez sur le **job** en erreur.

**GitLab CI** : allez dans **"Build → Pipelines"** → cliquez sur le pipeline en cours →
cliquez sur le **job** `quality` en erreur.

Vous verrez quelque chose comme :

```
✅ Checkout code
✅ Setup PHP 8.3
✅ Install Composer dependencies
✅ Lint YAML config
✅ Lint Twig templates
✅ Lint container
❌ PHP-CS-Fixer          ← c'est ici que ça échoue
⏭️ PHPStan analyse       ← n'est pas exécuté (le pipeline s'arrête au premier échec)
⏭️ PHPUnit tests
```

### Étape 2 — Lire le log de l'étape en erreur

Cliquez sur l'étape **❌** en rouge. Le log déroulé affiche la sortie de la commande.
Cherchez les lignes **en rouge** ou contenant `ERROR`, `FAILED`, ou `exit code 1`.

**Exemple de log PHP-CS-Fixer :**
```
PHP-CS-Fixer needs to be run with --allow-risky=yes option.
Files that need fixing:
   * src/Service/CartService.php
   * src/Entity/Product.php
```

### Étape 3 — Reproduire en local

Copiez la commande qui a échoué et lancez-la **sur votre machine** :

```bash
# Exemple : PHP-CS-Fixer a échoué
php vendor/bin/php-cs-fixer fix --dry-run --diff
```

Vous verrez la même erreur. Corrigez-la.

### Étape 4 — Pousser la correction

```bash
git add .
git commit -m "fix: corriger le style de code"
git push
```

La pipeline se relance automatiquement. Si elle est verte, le merge est autorisé.

## Les erreurs les plus fréquentes et comment les lire

### 1. Erreur de lint YAML

```
Symfony\Component\Yaml\Exception\ParseException:
  Unable to parse at line 42
```

**Cause** : une erreur de syntaxe YAML (mauvais indent, deux-points manquant).

**Fix** : ouvrez le fichier YAML indiqué et corrigez la syntaxe. Vérifiez l'indentation
(espaceinsable, pas de tabulations).

### 2. Erreur de syntaxe Twig

```
Twig\Error\SyntaxError:
  Unexpected "endfor" tag (expecting a closing tag for the "for" block)
```

**Cause** : un tag `{% endfor %}` manquant dans un template Twig.

**Fix** : ouvrez le template indiqué et ajoutez le tag manquant.

### 3. PHP-CS-Fixer détecte des fichiers à corriger

```
PHP-CS-Fixer 3.x needs to be run with --allow-risky=yes option.
Files that need fixing:
   * src/Controller/Admin/ReviewController.php
```

**Cause** : le style de code ne respecte pas PSR-12.

**Fix** :
```bash
# Corriger automatiquement
php vendor/bin/php-cs-fixer fix

# Puis repousser
git add .
git commit -m "style: fix code style"
git push
```

### 4. PHPStan détecte une erreur de type

```
------ ------------------------------------------------------------------
  Line   src/Service/OrderService.php:45
 ------ ------------------------------------------------------------------
  45     Method App\Service\OrderService::getStatus()
         should return string but returns int.
 ------ ------------------------------------------------------------------
```

**Cause** : le type déclaré ne correspond pas au type retourné.

**Fix** : ouvrez le fichier indiqué (ligne 45) et corrigez le type.

### 5. PHPUnit échoue

```
There was 1 failure:

1) App\Tests\Functional\CatalogTest::testCatalogPage
Failed asserting that 404 status code is 200.
```

**Cause** : la page catalogue retourne un 404 (introuvable).

**Fix** : vérifiez que la route existe, que le controller fonctionne, et que les
templates sont présents.

### 6. Doctrine schema n'est pas à jour

```
In Doctrine\ORM\ORMException:
  The EntityManager is closed.
```

ou

```
[ERROR] The database schema is not in sync with the current mapping file(s).
```

**Cause** : vous avez modifié une Entity sans créer de migration.

**Fix** :
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
git add migrations/
git commit -m "feat: add migration for Product entity"
git push
```

### 7. Container lint échoue

```
Invalid service "App\Service\MyService":
  Method "App\Service\MyService::__construct()" has type-hinted argument
  "App\Repository\ProductRepository" that is not a service.
```

**Cause** : un service a un argument de constructeur qui n'est pas enregistré
dans le container.

**Fix** : vérifiez que le service existe bien dans `config/services.yaml` et
que le type-hint correspond à un service enregistré.

### 8. Composer audit détecte une vulnérabilité

```
Found 1 security vulnerability advisory:
  - cve-2024-XXXXX: Remote Code Execution in vendor/package
```

**Cause** : une dépendance a une faille de sécurité connue.

**Fix** :
```bash
composer update vendor/package --with-dependencies
git add composer.lock
git commit -m "fix(security): update vulnerable dependency"
git push
```

### 9. PHPStan dépasse la limite mémoire (GitLab CI)

```
Child process error: PHPStan process crashed because it reached
configured PHP memory limit: 128M
```

**Cause** : GitLab CI alloue par défaut 128M de mémoire par processus.

**Fix** : ajoutez `--memory-limit=512M` à la commande PHPStan dans `.gitlab-ci.yml` :
```yaml
script:
  - php vendor/bin/phpstan analyse --no-progress --memory-limit=512M
```

### 10. Composer validate échoue (GitHub)

```
./composer.json is valid for simple usage with Composer but has
strict errors that make it unable to be published as a package
# Publish errors
- name : The property name is required
```

**Cause** : `composer validate --strict` exige les champs `name` et `description`.

**Fix** : ajoutez-les dans `composer.json` :
```json
{
    "name": "votre-utilisateur/ludo-shop",
    "description": "Application e-commerce de jeux de société",
    ...
}
```

## Astuces de diagnostic

### Lire le log depuis le début

Le log peut être long.
- **GitHub** : utilisez le bouton **"Download log"** pour télécharger le fichier complet.
- **GitLab** : cliquez sur **"Download"** en haut à droite du log.

Puis ouvrez-le dans votre éditeur.

### Reproduire exactement la commande du CI

Le CI exécute des commandes sur Ubuntu avec PHP 8.3. Pour reproduire exactement :

```bash
# Utiliser les mêmes flags
php bin/console lint:yaml config
php vendor/bin/php-cs-fixer fix --dry-run --diff
php vendor/bin/phpstan analyse --no-progress
php vendor/bin/phpunit --no-coverage
```

> **Ne dites jamais** "ça marche chez moi" — reproduisez exactement la commande.

### Utiliser `--verbose`

Ajoutez `-v` ou `-vvv` pour plus de détails :
```bash
php vendor/bin/phpstan analyse -v
php vendor/bin/phpunit --testdox -v
```

## Checklist avant de push

Avant de pousser, exécutez ces commandes dans l'ordre :

```bash
# 1. Style de code (le plus rapide)
php vendor/bin/php-cs-fixer fix

# 2. Analyse statique
php vendor/bin/phpstan analyse --no-progress

# 3. Tests
php vendor/bin/phpunit --no-coverage

# 4. Si tout est vert, push
git add .
git commit -m "feat: votre message"
git push
```
