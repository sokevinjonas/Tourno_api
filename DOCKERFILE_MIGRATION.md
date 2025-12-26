# 🚀 Migration vers Dockerfile Alpine

## 📊 Comparaison des tailles d'images

| Version | Taille | Économie |
|---------|--------|----------|
| **Actuel (Bookworm)** | 1.09 GB | - |
| **Optimisé (Bookworm)** | ~350-400 MB | -70% |
| **Alpine (Recommandé)** | ~200-250 MB | **-80%** |

---

## 🎯 Migration vers Alpine (Recommandé)

### Étape 1: Backup de l'ancien Dockerfile

```bash
# Sauvegarder l'actuel
mv Dockerfile Dockerfile.bookworm.backup
```

### Étape 2: Utiliser le Dockerfile Alpine

```bash
# Renommer le Dockerfile Alpine
cp Dockerfile.alpine Dockerfile
```

### Étape 3: Rebuild l'image

```bash
# Arrêter les containers
docker-compose down

# Rebuild l'image (sans cache pour tout recompiler)
docker-compose build --no-cache app

# Vérifier la taille
docker images tourno_app
# Attendu: ~200-250 MB au lieu de 1.09 GB
```

### Étape 4: Redémarrer les services

```bash
# Démarrer tous les services
docker-compose up -d

# Vérifier que tout fonctionne
docker-compose logs -f app
```

### Étape 5: Test

```bash
# Tester que l'API fonctionne
curl http://localhost/api/health

# Tester les jobs
docker-compose exec -T app php artisan test:check-match-deadlines
```

---

## 🔍 Différences principales Alpine vs Bookworm

| Aspect | Bookworm (Debian) | Alpine |
|--------|-------------------|--------|
| **Taille de base** | ~500 MB | ~50 MB |
| **Package manager** | apt-get | apk |
| **Utilisateur Apache** | www-data | apache |
| **Commande Apache** | apache2-foreground | httpd -D FOREGROUND |
| **Libc** | glibc | musl |

---

## ⚠️ Dépendances spécifiques

Si vous avez des packages PHP personnalisés, vérifiez qu'ils sont compatibles avec Alpine (musl).

**Packages Alpine installés:**
- PostgreSQL libs
- GD (images)
- ZIP
- XML
- Oniguruma (regex)

---

## 🔄 Rollback en cas de problème

Si Alpine pose des problèmes:

```bash
# Revenir à l'ancien Dockerfile
mv Dockerfile.bookworm.backup Dockerfile

# Rebuild
docker-compose build --no-cache app
docker-compose up -d
```

---

## 📦 Optimisations appliquées dans Alpine

✅ **Multi-stage build**: Compile dans Bookworm, runtime dans Alpine
✅ **composer --no-dev**: Pas de phpunit, pest, mockery
✅ **apk del .build-deps**: Suppression des outils de compilation après build
✅ **--no-cache**: Pas de cache apk dans l'image
✅ **Utilisateur Apache natif**: Pas de conversion www-data

---

## 🎉 Résultat attendu

Après migration vers Alpine:

```bash
$ docker images tourno_app
REPOSITORY    TAG      IMAGE ID       CREATED         SIZE
tourno_app    latest   abc123def456   2 minutes ago   237MB  # Au lieu de 1.09GB!
```

**Économie de bande passante:**
- Push vers registry: **~850 MB économisés** par push
- Pull sur VPS: **~850 MB économisés** par pull
- Stockage disque VPS: **~850 MB économisés**

**Pour 10 déploiements:** ~8.5 GB de bande passante économisée! 🚀
