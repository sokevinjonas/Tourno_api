# Infrastructure Queue - Configuration VPS

## 📋 Vue d'ensemble

Ce projet utilise une architecture asynchrone avec **Laravel Queues** pour supporter la scalabilité à 1000+ tournois. Les jobs sont exécutés en arrière-plan par des workers dédiés.

## 🏗️ Architecture

### Composants

1. **Container `tourno_app`**: API Laravel principale
2. **Container `tourno_queue`**: Queue worker dédié
3. **Container `tourno_db`**: Base de données PostgreSQL
4. **Scheduler**: Exécute les jobs planifiés toutes les 5-15 minutes

### Jobs Asynchrones

Tous les jobs suivants implémentent `ShouldQueue` et sont exécutés de manière asynchrone:

- **AutoStartTournamentsJob**: Démarre automatiquement les tournois complets (toutes les 5 minutes)
- **CheckFullTournamentsJob**: Vérifie les tournois pleins non démarrés (toutes les 5 minutes)
- **CheckMatchDeadlinesJob**: Gère les matchs expirés (toutes les 15 minutes)
- **SendMatchDeadlineWarningsJob**: Envoie des alertes 1h avant expiration (toutes les 15 minutes)
- **SendBulkEmailsJob**: Envoie les emails en masse avec rate limiting

## 🚀 Démarrage

### Lancer l'infrastructure complète

```bash
docker-compose up -d
```

Cela démarre:
- Application Laravel (port 8000)
- Queue worker
- PostgreSQL (port 5433)
- Adminer (port 8080)

### Vérifier le statut du queue worker

```bash
docker ps | grep tourno_queue
```

### Voir les logs du queue worker

```bash
docker logs tourno_queue -f
```

## 📊 Monitoring

### Vérifier les jobs en attente

```bash
docker exec tourno_app php artisan queue:work database --once
```

### Voir les jobs échoués

```bash
docker exec tourno_app php artisan queue:failed
```

### Rejouer un job échoué

```bash
docker exec tourno_app php artisan queue:retry {job_id}
```

### Rejouer tous les jobs échoués

```bash
docker exec tourno_app php artisan queue:retry all
```

## ⚙️ Configuration

### Queue Connection

Le projet utilise `database` comme driver de queue (ligne 24 de docker-compose.yml):

```yaml
QUEUE_CONNECTION=database
```

Pour passer à **Redis** (meilleure performance pour 1000+ tournois):

1. Ajouter service Redis au docker-compose.yml
2. Changer `QUEUE_CONNECTION=redis` dans les environnements
3. Configurer `config/queue.php` pour Redis

### Worker Configuration

Le worker est configuré avec les paramètres suivants (ligne 63):

```bash
php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=3600
```

**Paramètres:**
- `--sleep=3`: Attend 3 secondes entre chaque polling si la queue est vide
- `--tries=3`: Retry le job 3 fois en cas d'échec
- `--max-time=3600`: Redémarre le worker après 1h (évite les fuites mémoire)
- `--timeout=3600`: Timeout de 1h par job

## 🔧 Scalabilité

### Ajouter plus de workers

Pour traiter plus de jobs en parallèle, augmentez le nombre de workers dans docker-compose.yml:

```yaml
services:
  queue:
    # Worker 1
    ...

  queue2:
    # Worker 2 (copie de queue)
    container_name: tourno_queue2
    ...

  queue3:
    # Worker 3
    container_name: tourno_queue3
    ...
```

Ou utilisez `--scale`:

```bash
docker-compose up -d --scale queue=3
```

### Migration vers Redis

Pour 1000+ tournois, Redis est recommandé:

```yaml
  redis:
    image: redis:7-alpine
    container_name: tourno_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - tourno_network
```

Puis:
```env
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

## 📝 Fichiers importants

- `docker-compose.yml`: Configuration des services Docker
- `docker-queue-entrypoint.sh`: Script de démarrage du queue worker
- `supervisord.conf`: Configuration supervisor (alternative si pas Docker)
- `routes/console.php`: Configuration du scheduler Laravel

## 🐛 Debugging

### Le queue worker ne traite pas les jobs

1. Vérifier que le worker tourne:
   ```bash
   docker ps | grep tourno_queue
   ```

2. Vérifier les logs:
   ```bash
   docker logs tourno_queue
   ```

3. Vérifier la table `jobs`:
   ```bash
   docker exec tourno_app php artisan tinker
   DB::table('jobs')->count();
   ```

4. Redémarrer le worker:
   ```bash
   docker-compose restart queue
   ```

### Jobs échoués en boucle

1. Voir les jobs échoués:
   ```bash
   docker exec tourno_app php artisan queue:failed
   ```

2. Vider la queue de jobs échoués:
   ```bash
   docker exec tourno_app php artisan queue:flush
   ```

## 🎯 Performance attendue

| Nombre de tournois | Workers recommandés | Driver recommandé |
|---------------------|---------------------|-------------------|
| < 100               | 1                   | database          |
| 100-500             | 2-3                 | database          |
| 500-1000            | 3-5                 | redis             |
| 1000+               | 5-10                | redis             |

## 📚 Ressources

- [Laravel Queues Documentation](https://laravel.com/docs/11.x/queues)
- [Supervisor Configuration](http://supervisord.org/configuration.html)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
