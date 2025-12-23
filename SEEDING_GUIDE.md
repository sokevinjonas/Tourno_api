# 🎮 Guide de Seeding du Système de Tournois MLM

## 📋 Vue d'ensemble

Ce guide explique comment utiliser le système de seeding pour générer des données de test complètes pour l'application MLM Tournament.

## 🚀 Données Générées

Le seeder `TournamentSystemSeeder` crée automatiquement:

### 👥 Utilisateurs (119 au total)
- **1 Administrateur** (`admin@mlm.com`)
- **5 Modérateurs** (`moderator1@mlm.com` à `moderator5@mlm.com`)
- **3 Organisateurs** (`organizer1@mlm.com` à `organizer3@mlm.com`)
- **110 Joueurs** (`player1@mlm.com` à `player110@mlm.com`)

### 📝 Profils
- Tous les profils sont **validés** automatiquement
- Numéros WhatsApp générés aléatoirement (format: +237XXXXXXXXX)
- Pays et villes aléatoires d'Afrique
- Date de validation: maintenant

### 💰 Portefeuilles (Wallets)
- Chaque utilisateur reçoit **20 pièces MLM** au départ
- Après inscription aux tournois, le solde est déduit de 4 pièces par tournoi

### 🎮 Comptes de Jeu (Game Accounts)
- Chaque joueur et organisateur a au moins 1 compte de jeu
- Jeux supportés: eFootball, FC Mobile, Dream League Soccer
- Screenshots d'équipe générés automatiquement (chemins fictifs)

### 🏆 Tournois (3 tournois)

| Tournoi | Jeu | Participants | Places | Frais | Date de début |
|---------|-----|--------------|--------|-------|---------------|
| Swiss Championship - eFootball | eFootball | 18/18 | COMPLET | 4 MLM | 25 Dec 2025, 12:00 PM |
| Swiss Championship - FC Mobile | FC Mobile | 18/18 | COMPLET | 4 MLM | 25 Dec 2025, 12:00 PM |
| Swiss Championship - Dream League | Dream League Soccer | 17/18 | 1 place libre | 4 MLM | 25 Dec 2025, 12:00 PM |

**Caractéristiques des tournois:**
- Format: Swiss (5 rounds)
- Limite: 18 participants maximum
- Statut: Ouvert (`open`)
- Distribution des prix:
  - 1er: 30 MLM
  - 2e: 20 MLM
  - 3e: 15 MLM
  - 4e: 10 MLM

### 📊 Inscriptions aux Tournois
- **53 inscriptions** au total (18 + 18 + 17)
- Statut: Tous enregistrés (`registered`)
- Frais d'entrée déduits automatiquement des portefeuilles

## 🔧 Utilisation

### 1. Réinitialiser et remplir la base de données

```bash
php artisan migrate:fresh --seed
```

Cette commande va:
1. Supprimer toutes les tables existantes
2. Recréer toutes les tables
3. Exécuter le seeder principal
4. Afficher un résumé de la création

### 2. Exécuter uniquement le seeder principal (sans migration)

```bash
php artisan db:seed --class=TournamentSystemSeeder
```

Ce seeder crée:
- 119 utilisateurs (1 admin + 5 mods + 3 orgs + 110 joueurs)
- 3 tournois Swiss avec inscriptions variées:
  - Tournoi 1: **COMPLET** (18/18)
  - Tournoi 2: **1 place libre** (17/18)
  - Tournoi 3: **2 places libres** (16/18)

### 3. Ajouter des inscriptions supplémentaires

```bash
php artisan db:seed --class=AdditionalTournamentRegistrationSeeder
```

Ce seeder **intelligent**:
- Détecte automatiquement les tournois avec des places disponibles
- Inscrit des joueurs aléatoires avec un solde suffisant
- Affiche le nombre d'inscriptions ajoutées pour chaque tournoi
- Peut être exécuté plusieurs fois pour remplir progressivement les tournois

**Exemple d'utilisation:**
```bash
# Créer les données initiales avec places disponibles
php artisan db:seed --class=TournamentSystemSeeder

# Remplir partiellement ou totalement les places
php artisan db:seed --class=AdditionalTournamentRegistrationSeeder
```

### 4. Vérifier les données créées

```bash
php artisan tournament:verify
```

Cette commande affiche des statistiques détaillées sur:
- Les utilisateurs par rôle
- Les profils par statut
- Les portefeuilles (solde total, moyenne, min/max)
- Les comptes de jeu par plateforme
- Les tournois avec leur taux de remplissage
- Les inscriptions par statut

## 📁 Fichiers Importants

```
database/seeders/
├── DatabaseSeeder.php           # Seeder principal
└── TournamentSystemSeeder.php   # Seeder complet du système

app/Console/Commands/
└── VerifyTournamentData.php     # Commande de vérification
```

## 🔑 Comptes de Test

### Administrateur
- Email: `admin@mlm.com`
- Rôle: `admin`

### Modérateurs
- `moderator1@mlm.com` à `moderator5@mlm.com`
- Rôle: `moderator`

### Organisateurs
- `organizer1@mlm.com` à `organizer3@mlm.com`
- Rôle: `organizer`

### Joueurs
- `player1@mlm.com` à `player110@mlm.com`
- Rôle: `player`

**Note:** Tous les comptes n'ont pas de mot de passe car le système utilise l'authentification par Magic Link.

## 🎯 Scénarios de Test

### Scénario 1: Inscription à un tournoi complet
1. Tenter de s'inscrire au tournoi "Swiss Championship - eFootball"
2. Devrait recevoir une erreur car le tournoi est complet (18/18)

### Scénario 2: Inscription à un tournoi avec places disponibles
1. S'inscrire au tournoi "Swiss Championship - Dream League"
2. Il reste 1 place (17/18)
3. L'inscription devrait réussir si le joueur a au moins 4 MLM

### Scénario 3: Vérifier le solde du portefeuille
1. Les joueurs qui se sont inscrits ont un solde de 16 MLM (20 - 4)
2. Les joueurs non inscrits ont un solde de 20 MLM

## 🧪 Tests Automatisés

Le seeder crée des données cohérentes pour tester:
- ✅ Le système d'inscription aux tournois
- ✅ La gestion des portefeuilles et transactions
- ✅ La limitation du nombre de participants
- ✅ La validation des profils
- ✅ Les différents rôles d'utilisateurs
- ✅ Les comptes de jeu multi-plateformes

## 📈 Statistiques Attendues

Après l'exécution du seeder, vous devriez avoir:
- **119 utilisateurs** (1 admin + 5 mods + 3 orgs + 110 joueurs)
- **119 profils validés** (100%)
- **~2,168 MLM** en circulation totale
- **149 comptes de jeu** (certains joueurs ont plusieurs comptes)
- **3 tournois** au format Swiss
- **53 inscriptions** aux tournois

## 🔄 Personnalisation

Pour personnaliser le seeder, éditez le fichier `database/seeders/TournamentSystemSeeder.php`:

```php
// Changer le nombre de joueurs
for ($i = 1; $i <= 200; $i++) { // 200 au lieu de 110

// Changer le solde initial des portefeuilles
'balance' => 50.00, // 50 au lieu de 20

// Changer les frais d'entrée des tournois
'entry_fee' => 10.00, // 10 au lieu de 4

// Changer la date de début des tournois
$startDate = '2026-01-01 14:00:00';
```

## ⚠️ Important

- Le seeder utilise une **transaction** pour garantir que toutes les données sont créées ou aucune en cas d'erreur
- Les **numéros WhatsApp** sont générés aléatoirement et ne sont pas réels
- Les **screenshots d'équipes** sont des chemins fictifs (vous devrez les remplacer avec de vrais fichiers si nécessaire)
- Le premier utilisateur (Admin) a l'ID 1 et est utilisé comme `validated_by` pour tous les profils

## 🐛 Dépannage

### Erreur: "SQLSTATE[23000]: Integrity constraint violation"
- La base de données contient déjà des données
- Solution: `php artisan migrate:fresh --seed`

### Erreur: "Class 'TournamentSystemSeeder' not found"
- Assurez-vous que le fichier existe dans `database/seeders/`
- Solution: `composer dump-autoload`

### Les données ne correspondent pas
- Vérifiez les migrations
- Solution: `php artisan migrate:fresh --seed`

## 📞 Support

Pour toute question ou problème, consultez la documentation Laravel sur les seeders:
https://laravel.com/docs/seeding
