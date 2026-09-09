> Retour au [README](README.md) — Étape 6 / 11

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



---

**Suite :** [07-catalog.md](07-catalog.md) | **Retour :** [README](README.md)
