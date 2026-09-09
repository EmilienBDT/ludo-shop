# 01 — Créer un dépôt GitHub et y pousser votre code

> Étape 1 : mettre votre projet sur GitHub pour que les pipelines puissent s'exécuter.

## Créer le dépôt GitHub

1. Allez sur [github.com](https://github.com) et connectez-vous
2. Cliquez sur le bouton **"+"** en haut à droite → **"New repository"**
3. Remplissez :
   - **Repository name** : `ludo-shop` (ou le nom de votre choix)
   - **Description** : `Application e-commerce de jeux de société — projet CI`
   - **Public** (recommandé pour la formation)
   - **Ne cochez rien** (pas de README, pas de .gitignore, pas de licence pour l'instant)
4. Cliquez sur **"Create repository"**

## Connecter votre projet local au dépôt distant

Ouvrez un terminal dans le dossier de votre projet :

```bash
# Vérifier que Git est initialisé
git status

# Si "not a git repository", initialiser :
git init
```

Ajoutez le dépôt distant (remplacez `USER` par votre identifiant GitHub) :

```bash
# Ajouter le dépôt distant (utilisez l'URL SSH ou HTTPS depuis GitHub)
git remote add origin https://github.com/VOTRE_UTILISATEUR/ludo-shop.git

# Vérifier que la remote est ajoutée
git remote -v
```

## Premier commit et push

```bash
# Ajouter tous les fichiers
git add .

# Créer le premier commit
git commit -m "feat: initial project setup"

# Pousser sur GitHub (premier push)
git push -u origin main
```

> **Note :** la première fois, Git demande de configurer votre nom et email :
> ```bash
> git config user.name "Votre Nom"
> git config user.email "votre.email@example.com"
> ```

## Vérifier sur GitHub

Allez sur `https://github.com/VOTRE_UTILISATEUR/ludo-shop` — vous devriez voir tous vos
fichiers. Si le pipeline CI est déjà configuré (fichier `.github/workflows/ci.yml`),
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

GitHub proposera automatiquement de créer une **Pull Request** (merge request) pour
fusionner votre branche dans `main`.

## Commandes essentielles Git

| Commande | Action |
|----------|--------|
| `git status` | Voir les fichiers modifiés |
| `git add .` | Ajouter tous les fichiers |
| `git commit -m "..."` | Créer un commit avec un message |
| `git push` | Pousser sur GitHub |
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

Une fois votre code sur GitHub, passez au [02-workflow.md](02-workflow.md) pour écrire
votre premier workflow GitHub Actions.
