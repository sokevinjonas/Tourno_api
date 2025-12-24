# 🐳 Publication sur Docker Hub - Tourno API

Ce guide vous explique comment publier l'image Docker de Tourno API sur Docker Hub.

---

## 📋 Prérequis

* Compte Docker Hub : [https://hub.docker.com/](https://hub.docker.com/)
* Docker installé localement
* Accès au dépôt GitHub du projet

---

## 🔐 Étape 1 : Se connecter à Docker Hub

```bash
docker login
```

Entrez votre **username** et **password** Docker Hub.

---

## 🏗️ Étape 2 : Construire l'image Docker

### Choisir un nom d'image

Format : `username/repository:tag`

Exemple : `votre-username/tourno-api:latest`

### Construire l'image

```bash
# Depuis la racine du projet
docker build -t votre-username/tourno-api:latest .
```

**Options recommandées :**

```bash
# Avec un tag de version spécifique
docker build -t votre-username/tourno-api:1.0.0 -t votre-username/tourno-api:latest .

# Avec une plateforme spécifique (pour ARM/AMD)
docker buildx build --platform linux/amd64,linux/arm64 -t votre-username/tourno-api:latest .
```

---

## 🧪 Étape 3 : Tester l'image localement

Avant de publier, testez que l'image fonctionne :

```bash
# Créer un réseau Docker
docker network create tourno-network

# Lancer PostgreSQL
docker run -d \
  --name tourno-db \
  --network tourno-network \
  -e POSTGRES_DB=tourno \
  -e POSTGRES_USER=tourno_user \
  -e POSTGRES_PASSWORD=tourno_password \
  postgres:17-alpine

# Lancer Redis
docker run -d \
  --name tourno-redis \
  --network tourno-network \
  redis:7-alpine

# Lancer votre image
docker run -d \
  --name tourno-app \
  --network tourno-network \
  -p 80:80 \
  -e DB_HOST=tourno-db \
  -e DB_DATABASE=tourno \
  -e DB_USERNAME=tourno_user \
  -e DB_PASSWORD=tourno_password \
  -e REDIS_HOST=tourno-redis \
  votre-username/tourno-api:latest

# Vérifier les logs
docker logs -f tourno-app

# Tester l'API
curl http://localhost/api/tournaments
```

---

## 📤 Étape 4 : Publier sur Docker Hub

```bash
# Push de l'image
docker push votre-username/tourno-api:latest

# Si vous avez plusieurs tags
docker push votre-username/tourno-api:1.0.0
docker push votre-username/tourno-api:latest
```

---

## 🏷️ Stratégie de tags recommandée

### Tags de version sémantique

```bash
# Version majeure.mineure.patch
docker tag tourno-api:latest votre-username/tourno-api:1.0.0
docker tag tourno-api:latest votre-username/tourno-api:1.0
docker tag tourno-api:latest votre-username/tourno-api:1
docker tag tourno-api:latest votre-username/tourno-api:latest

# Push de tous les tags
docker push votre-username/tourno-api:1.0.0
docker push votre-username/tourno-api:1.0
docker push votre-username/tourno-api:1
docker push votre-username/tourno-api:latest
```

### Tags d'environnement

```bash
# Production
docker tag tourno-api:latest votre-username/tourno-api:production
docker push votre-username/tourno-api:production

# Staging
docker tag tourno-api:latest votre-username/tourno-api:staging
docker push votre-username/tourno-api:staging

# Development
docker tag tourno-api:latest votre-username/tourno-api:dev
docker push votre-username/tourno-api:dev
```

---

## 📥 Utiliser l'image depuis Docker Hub

Une fois publiée, n'importe qui peut utiliser votre image :

### docker-compose.yml simplifié

```yaml
version: '3.8'

services:
  app:
    image: votre-username/tourno-api:latest
    ports:
      - "80:80"
    environment:
      - DB_HOST=db
      - DB_DATABASE=tourno
      - DB_USERNAME=tourno_user
      - DB_PASSWORD=tourno_password
      - REDIS_HOST=redis
    depends_on:
      - db
      - redis
    networks:
      - tourno

  db:
    image: postgres:17-alpine
    environment:
      - POSTGRES_DB=tourno
      - POSTGRES_USER=tourno_user
      - POSTGRES_PASSWORD=tourno_password
    volumes:
      - db_data:/var/lib/postgresql/data
    networks:
      - tourno

  redis:
    image: redis:7-alpine
    networks:
      - tourno

volumes:
  db_data:

networks:
  tourno:
```

### Lancement rapide

```bash
docker-compose up -d
```

---

## 🔄 Mise à jour de l'image

### Workflow recommandé

1. **Faire des changements** dans le code
2. **Tester localement** avec docker-compose
3. **Construire une nouvelle version** :
   ```bash
   docker build -t votre-username/tourno-api:1.1.0 -t votre-username/tourno-api:latest .
   ```
4. **Publier** :
   ```bash
   docker push votre-username/tourno-api:1.1.0
   docker push votre-username/tourno-api:latest
   ```

---

## 🤖 Automatisation avec GitHub Actions

Créez `.github/workflows/docker-publish.yml` :

```yaml
name: Publish Docker Image

on:
  push:
    tags:
      - 'v*'
  workflow_dispatch:

jobs:
  docker:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v3

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v2

      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKERHUB_USERNAME }}
          password: ${{ secrets.DOCKERHUB_TOKEN }}

      - name: Extract metadata
        id: meta
        uses: docker/metadata-action@v4
        with:
          images: votre-username/tourno-api
          tags: |
            type=ref,event=branch
            type=ref,event=pr
            type=semver,pattern={{version}}
            type=semver,pattern={{major}}.{{minor}}
            type=semver,pattern={{major}}
            type=sha

      - name: Build and push
        uses: docker/build-push-action@v4
        with:
          context: .
          platforms: linux/amd64,linux/arm64
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
          cache-from: type=gha
          cache-to: type=gha,mode=max
```

**Configuration des secrets GitHub :**

1. Aller dans **Settings > Secrets and variables > Actions**
2. Ajouter :
   * `DOCKERHUB_USERNAME` : votre username Docker Hub
   * `DOCKERHUB_TOKEN` : votre access token Docker Hub

---

## 📊 Vérifier sur Docker Hub

Après publication, vérifiez :

1. Aller sur [https://hub.docker.com/](https://hub.docker.com/)
2. Connexion avec votre compte
3. Accéder à **Repositories**
4. Cliquer sur **tourno-api**
5. Vérifier :
   * Tags disponibles
   * Taille de l'image
   * Date de dernière mise à jour
   * Nombre de pulls

---

## 🎯 Bonnes pratiques

### Sécurité

* ✅ Ne jamais inclure de secrets dans l'image
* ✅ Utiliser des variables d'environnement pour les configurations sensibles
* ✅ Scanner l'image pour des vulnérabilités :
  ```bash
  docker scan votre-username/tourno-api:latest
  ```

### Performance

* ✅ Utiliser `.dockerignore` pour exclure les fichiers inutiles
* ✅ Minimiser le nombre de layers
* ✅ Nettoyer les caches après installation :
  ```dockerfile
  RUN composer install --no-dev --optimize-autoloader \
      && rm -rf /root/.composer/cache
  ```

### Documentation

* ✅ Ajouter un README sur Docker Hub
* ✅ Documenter les variables d'environnement requises
* ✅ Fournir un exemple de docker-compose.yml

---

## ❓ Dépannage

### Erreur : "unauthorized: authentication required"

```bash
# Se reconnecter à Docker Hub
docker logout
docker login
```

### Erreur : "denied: requested access to the resource is denied"

Vérifiez que vous avez les droits sur le repository :
* Le nom doit correspondre à votre username Docker Hub
* Le repository doit exister sur Docker Hub

### Image trop volumineuse

```bash
# Analyser les layers
docker history votre-username/tourno-api:latest

# Utiliser docker-slim pour réduire la taille
docker-slim build votre-username/tourno-api:latest
```

### Build échoue

```bash
# Vérifier le Dockerfile
docker build --no-cache -t test-image .

# Voir les logs détaillés
docker build --progress=plain -t test-image .
```

---

## 📬 Support

Pour toute question :
* Issues GitHub
* Docker Hub Community Forum
* Discord Tourno API

---

**Bon déploiement ! 🚀**
