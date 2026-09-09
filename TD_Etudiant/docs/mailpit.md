# Mailpit

> Serveur SMTP local pour tester les emails sans les envoyer réellement.

## Objectif

Mailpit intercepte tous les emails envoyés par l'application et les affiche dans une
**interface web** accessible à `http://localhost:8025`. Aucun email n'est réellement envoyé.

## Pourquoi ici

- **Test des emails** : LudoShop envoie des emails de confirmation de commande, de changement
  de statut, et d'abonnement à la newsletter. Mailpit permet de les vérifier visuellement.
- **Aucun envoi réel** : pas de risque d'envoyer des emails à de vrais utilisateurs en
  développement.
- **Configuration simple** : un seul paramètre dans `.env` suffit (`MAILER_DSN`).
- **Interface pratique** : l'interface web montre tous les emails capturés avec leur contenu
  HTML/texte, headers, et pièces jointes.

## Emails du projet

| Type | Destinataire | Sujet | Trigger |
|------|-------------|-------|---------|
| Confirmation de commande | Client | `Confirmation - Commande #XXX` | Paiement réussi |
| Changement de statut | Client | `Commande #XXX - Statut mis à jour` | Admin change le statut |
| Abonnement newsletter | Client | `Confirmation d'abonnement` | Inscription newsletter |
| Désabonnement newsletter | Client | `Vous êtes désabonné(e)` | Désinscription newsletter |

## Configuration

### Fichier `.env` (environnement de développement)

```
MAILER_DSN=smtp://localhost:1025
```

### Fichier `.env.test` (tests)

```
MAILER_DSN=null://null
```

En mode test, les emails ne sont ni envoyés ni interceptés — ils sont simplement ignorés.

### Fichier `.env.local` (optionnel, pour Mailpit)

```
MAILER_DSN=smtp://localhost:1025
```

## Comment lancer en local

```bash
# Option 1 : avec Docker (recommandé)
docker run -d --name mailpit -p 8025:8025 -p 1025:1025 axllent/mailpit

# Option 2 : avec Symfony CLI (si Mailpit est installé)
symfony server:start --no-proxy

# Option 3 : télécharger Mailpit
# https://github.com/axllent/mailpit/releases
mailpit
```

### Accès à l'interface Mailpit

| URL | Description |
|-----|-------------|
| `http://localhost:8025` | Interface web (lecture des emails) |
| `localhost:1025` | Port SMTP (envoi des emails) |

## Utilisation

1. **Démarrez Mailpit** (Docker ou binaire)
2. **Démarrez l'application** (`symfony server:start`)
3. **Effectuez une action** qui déclenche un email (ex: passer une commande)
4. **Ouvrez Mailpit** (`http://localhost:8025`) pour voir l'email intercepté

## Dans le pipeline CI

Mailpit **n'est pas nécessaire** dans le pipeline CI. Les tests utilisent `null://null`
(pas d'envoi), et les tests fonctionnels vérifient que les emails sont correctement
construits via le profil Symfony (`assertEmailCount()`, etc.).

```yaml
# Le pipeline CI n'a pas besoin de Mailpit
# Les tests utilisent MAILER_DSN=null://null
```

## Ressources

- [Mailpit GitHub](https://github.com/axllent/mailpit)
- [Documentation Symfony Mailer](https://symfony.com/doc/current/mailer.html)
- [Docker Hub axllent/mailpit](https://hub.docker.com/r/axllent/mailpit)
