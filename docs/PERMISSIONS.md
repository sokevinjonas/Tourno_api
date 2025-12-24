# 🔐 Gestion des Permissions - Projet Docker

## 📌 Problème

Lorsque vous créez de nouveaux fichiers (via Claude Code, votre IDE, ou manuellement), ils ont les permissions de votre utilisateur local (`jonas-dev`), mais Docker utilise l'utilisateur `www-data`. Cela peut causer des erreurs comme :

```
Permission denied
Failed to open stream
```

---

## ✅ Solution Automatique

### 1. Script de Correction Rapide

Utilisez le script `fix-permissions.sh` à la racine du projet :

```bash
./fix-permissions.sh
```

Ce script :
- ✅ Met tous les dossiers en `755` (rwxr-xr-x)
- ✅ Met tous les fichiers en `644` (rw-r--r--)
- ✅ Rend les scripts exécutables
- ✅ Préserve les permissions spéciales de `storage/` et `bootstrap/cache/`

**Quand l'utiliser ?**
- Après avoir créé de nouveaux fichiers
- Après un `git pull`
- Si vous rencontrez des erreurs de permissions

---

### 2. Correction Automatique au Démarrage Docker

Le script `docker-entrypoint.sh` s'exécute **automatiquement** à chaque démarrage du conteneur et corrige les permissions.

Pour le redémarrer :

```bash
docker-compose restart app
```

Ou reconstruire l'image :

```bash
docker-compose down
docker-compose up -d --build
```

---

## 📂 Permissions Standards

### Dossiers : `755` (rwxr-xr-x)
```
app/
config/
database/
resources/
routes/
public/
```

### Fichiers : `644` (rw-r--r--)
```
*.php
*.blade.php
*.json
.env
```

### Dossiers Spéciaux : `775` (rwxrwxr-x)
```
storage/          → www-data:www-data
bootstrap/cache/  → www-data:www-data
```

---

## 🛠️ Commandes Manuelles (si nécessaire)

### Corriger un fichier spécifique

```bash
chmod 644 resources/views/emails/my-new-template.blade.php
```

### Corriger un dossier et son contenu

```bash
chmod -R 755 app/Services/
```

### Corriger les vues Blade

```bash
find resources/views -type f -name "*.blade.php" -exec chmod 644 {} \;
```

---

## 🚀 Workflow Recommandé

### Option 1 : Après chaque création de fichier
```bash
./fix-permissions.sh
```

### Option 2 : Ajouter au Git Hook

Créez `.git/hooks/post-merge` :

```bash
#!/bin/bash
./fix-permissions.sh
```

Puis rendez-le exécutable :

```bash
chmod +x .git/hooks/post-merge
```

### Option 3 : Alias Bash

Ajoutez à votre `~/.bashrc` ou `~/.zshrc` :

```bash
alias fix-perms='cd /home/jonas-dev/Bureau/Tourno/api && ./fix-permissions.sh'
```

Puis utilisez simplement :

```bash
fix-perms
```

---

## ❌ Erreurs Courantes

### `Permission denied` lors de la lecture d'un fichier

**Cause :** Le fichier n'est pas lisible par `www-data`
**Solution :**
```bash
chmod 644 /path/to/file
```

### `file_get_contents(): Failed to open stream`

**Cause :** Fichier blade.php non lisible
**Solution :**
```bash
chmod 644 resources/views/path/to/template.blade.php
# ou
./fix-permissions.sh
```

### `Cannot write to storage/logs/laravel.log`

**Cause :** `storage/` n'appartient pas à `www-data`
**Solution :** Redémarrer Docker (le entrypoint.sh va corriger)
```bash
docker-compose restart app
```

---

## 🔍 Vérifier les Permissions

### Vérifier un fichier

```bash
ls -la resources/views/emails/my-template.blade.php
```

**Résultat attendu :**
```
-rw-r--r-- 1 jonas-dev jonas-dev 2066 déc. 23 21:26 my-template.blade.php
```

### Vérifier un dossier

```bash
ls -la storage/
```

**Résultat attendu :**
```
drwxrwxr-x 5 www-data www-data 4096 déc. 23 20:00 storage
```

---

## 📝 Notes Importantes

1. **NE PAS modifier** les permissions de `storage/` et `bootstrap/cache/` manuellement - laissez Docker s'en occuper

2. **Utilisez le script** `fix-permissions.sh` plutôt que des commandes `chmod` manuelles

3. **Après un git pull**, pensez à exécuter `./fix-permissions.sh`

4. **Les fichiers créés par Claude Code** auront besoin d'une correction de permissions

---

## 🆘 En Cas de Doute

**Toujours exécuter :**
```bash
./fix-permissions.sh
docker-compose restart app
```

Cela résoudra 99% des problèmes de permissions ! 🎯
