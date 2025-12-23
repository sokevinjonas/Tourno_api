# Tourno API - Configuration Docker

Ce projet utilise Docker pour faciliter le développement et le déploiement avec PostgreSQL.

## 📋 Prérequis

- Docker Desktop installé (https://www.docker.com/products/docker-desktop)
- Docker Compose (inclus dans Docker Desktop)

## 🚀 Installation et Démarrage

### 1. Copier le fichier d'environnement Docker

```bash
cp .env.docker .env
```

### 2. Construire et démarrer les conteneurs

```bash
docker-compose up -d --build
```

Cette commande va:
- Construire l'image Docker de l'application Laravel
- Démarrer le conteneur PostgreSQL
- Démarrer le conteneur Adminer (interface de gestion de base de données)
- Créer le réseau Docker entre les services

### 3. Installer les dépendances Composer

```bash
docker-compose exec app composer install
```

### 4. Générer la clé d'application

```bash
docker-compose exec app php artisan key:generate
```

### 5. Exécuter les migrations et seeders

```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### 6. Donner les permissions appropriées

```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

## 🌐 Accès aux Services

- **Application Laravel**: http://localhost:8000
- **Adminer (DB Manager)**: http://localhost:8080
  - Système: PostgreSQL
  - Serveur: db
  - Utilisateur: tourno_user
  - Mot de passe: tourno_password
  - Base de données: tourno

## 📦 Commandes Docker Utiles

### Voir les logs de l'application
```bash
docker-compose logs -f app
```

### Voir les logs de la base de données
```bash
docker-compose logs -f db
```

### Exécuter des commandes Artisan
```bash
docker-compose exec app php artisan [commande]
```

### Accéder au shell du conteneur
```bash
docker-compose exec app bash
```

### Accéder à PostgreSQL
```bash
docker-compose exec db psql -U tourno_user -d tourno
```

### Arrêter les conteneurs
```bash
docker-compose down
```

### Arrêter et supprimer les volumes (⚠️ supprime les données)
```bash
docker-compose down -v
```

### Redémarrer un service spécifique
```bash
docker-compose restart app
```

## 🔧 Configuration

### Variables d'environnement

Modifiez le fichier `.env` pour configurer:
- Les credentials de la base de données
- L'URL de l'application
- Les paramètres de mail
- Les clés OAuth

### Modifier la configuration Docker

- `Dockerfile`: Configuration de l'image de l'application
- `docker-compose.yml`: Orchestration des services
- `.dockerignore`: Fichiers exclus du build Docker

## 🐛 Dépannage

### Erreur de permission
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
```

### Reconstruire les conteneurs
```bash
docker-compose down
docker-compose up -d --build
```

### Vider le cache de l'application
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Problème de connexion à la base de données
Vérifiez que le conteneur PostgreSQL est en cours d'exécution:
```bash
docker-compose ps
```

## 📊 Base de Données

### Créer un backup
```bash
docker-compose exec db pg_dump -U tourno_user tourno > backup.sql
```

### Restaurer un backup
```bash
cat backup.sql | docker-compose exec -T db psql -U tourno_user -d tourno
```

## 🔄 Migration depuis SQLite vers PostgreSQL

Si vous migrez depuis SQLite:

1. Exportez vos données SQLite si nécessaire
2. Suivez les étapes d'installation ci-dessus
3. Exécutez les migrations: `docker-compose exec app php artisan migrate:fresh --seed`

## 📝 Notes

- Le volume `postgres_data` persiste les données de la base de données
- Les fichiers de l'application sont montés en volume pour le développement en temps réel
- Adminer est accessible pour gérer facilement la base de données PostgreSQL
