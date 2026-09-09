# 01b — Créer un dépôt GitLab et y pousser votre code

> Étape 1 : mettre votre projet sur GitLab pour que les pipelines puissent s'exécuter.

## Créer le dépôt GitLab

1. Allez sur [gitlab.com](https://gitlab.com) (ou le GitLab de votre IUT) et connectez-vous
2. Cliquez sur **"New project"** en haut à droite
3. Cliquez sur **"Create blank project"**
4. Remplissez :
   - **Project name** : `ludo-shop` (ou le nom de votre choix)
   - **Project slug** : `ludo-shop`
   - **Description** : `Application e-commerce de jeux de société — projet CI`
   - **Visibility** : **Private** (recommandé pour la formation)
   - **Initialize repository with a README** : **décochez** (on a déjà un projet)
5. Cliquez sur **"Create project"**
6. Copiez l'URL du dépôt (ex: `https://gitlab.com/VOTRE_UTILISATEUR/ludo-shop.git`)

## Connecter votre projet local au dépôt distant

Ouvrez un terminal dans le dossier de votre projet :

```bash
# Vérifier que Git est initialisé
git status

# Si "not a git repository", initialiser :
git init
git branch -M main
```

Ajoutez le dépôt distant (remplacez `USER` par votre identifiant GitLab) :

```bash
# Ajouter le dépôt distant
git remote add origin https://gitlab.com/VOTRE_UTILISATEUR/ludo-shop.git

# Vérifier que la remote est ajoutée
git remote -v
```

## Premier commit et push

```bash
# Ajouter tous les fichiers
git add .

# Créer le premier commit
git commit -m "feat: initial project setup"

# Pousser sur GitLab (premier push)
git push -u origin main
```

> **Note :** la première fois, Git demande de configurer votre nom et email :
> ```bash
> git config user.name "Votre Nom"
> git config user.email "votre.email@example.com"
> ```

> **Authentification** : si Git vous demande un mot de passe, créez un
> **Personal Access Token** dans GitLab :
> **Settings → Access Tokens → Add new token** → cochez `write_repository`.
> Utilisez ce token comme mot de passe lors du push.

## Vérifier sur GitLab

Allez sur `https://gitlab.com/VOTRE_UTILISATEUR/ludo-shop` — vous devriez voir tous vos
fichiers. Si le pipeline CI est déjà configuré (fichier `.gitlab-ci.yml`),
la première pipeline se lance automatiquement.

## Travailler avec des branches

Ne travaillez **jamais** directement sur `main`. Créez toujours une branche pour chaque
nouvelle fonctionnalité :

```bash
# Créer et basculer sur une nouvelle branche
git checkout -b feat/ma-nouvelle-fonctionnalite

# Travailler, commit, push
git add .
git commit -m "feat: ajouter la fonctionnalité X"
git push -u origin feat/ma-nouvelle-fonctionnalite
```

GitLab proposera automatiquement de créer une **Merge Request** pour
fusionner votre branche dans `main`.

## Commandes essentielles Git

| Commande | Action |
|----------|--------|
| `git status` | Voir les fichiers modifiés |
| `git add .` | Ajouter tous les fichiers |
| `git commit -m "..."` | Créer un commit avec un message |
| `git push` | Pousser sur GitLab |
| `git pull` | Récupérer les changements distants |
| `git log --oneline -10` | Voir les 10 derniers commits |
| `git branch` | Lister les branches |
| `git checkout -b nom` | Créer et basculer sur une branche |

## Vérification

```bash
# Après avoir poussé, vérifiez :
git log --oneline -1

# Vous devriez voir quelque chose comme :
# a1b2c3d feat: initial project setup
```

## Prochaine étape

Une fois votre code sur GitLab, passez au [02b-gitlab-ci.md](02b-gitlab-ci.md) pour écrire
votre premier fichier `.gitlab-ci.yml`.
