
---

# ⚽ Tourno API (Mobile League Manager)

**La plateforme de référence pour l'organisation et la gestion de tournois de jeux de simulation de football mobile.**

<p align="center">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg" />
  <img src="https://img.shields.io/badge/Laravel-11.x-red.svg" />
  <img src="https://img.shields.io/badge/PHP-8.4-blue.svg" />
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED.svg" />
  <img src="https://img.shields.io/badge/Tests-38%20Passed-brightgreen.svg" />
  <img src="https://img.shields.io/badge/Open%20Source-%E2%9D%A4-brightgreen.svg" />
</p>

---

# 📘 Sommaire

* [📌 À propos du projet](#-à-propos-du-projet)
* [🎯 Objectifs du MVP](#-objectifs-du-mvp)
* [✨ Fonctionnalités principales](#-fonctionnalités-principales)
* [🧰 Stack technique](#-stack-technique)
* [⚙️ Installation](#️-installation)
  * [Installation avec Docker (Recommandé)](#-installation-avec-docker-recommandé)
  * [Installation manuelle](#-installation-manuelle)
* [📚 Documentation](#-documentation)
* [🧪 Tests](#-tests)
* [🤝 Contribution](#-contribution)
* [🗺️ Roadmap](#️-roadmap)
* [📄 Licence](#-licence)
* [📬 Contact & Support](#-contact--support)

---

# 📌 À propos du projet

**Tourno API (Mobile League Manager - GPA)** est une API REST développée avec **Laravel** permettant aux joueurs de jeux mobiles de football (Dream League Soccer, E-football, FC Mobile…) d'organiser et gérer des compétitions automatiquement.

---

# 🎯 Objectifs du MVP

* ✅ **Simplicité** : Inscription et création de tournoi en quelques clics
* ✅ **Automatisation** : Système Suisse avec appariements automatiques
* ✅ **Validation** : Modération des profils avant participation
* ✅ **Économie simple** : Système de wallet avec blocage de fonds
* ✅ **Multi-jeux** : Support E-football, FC Mobile, Dream League Soccer

---

# ✨ Fonctionnalités principales

## 👥 Gestion des utilisateurs & Rôles

* ✅ **Authentification sécurisée** (OAuth Google + Magic Link)
* ✅ **4 rôles** : Admin, Modérateur, Organisateur, Joueur
* ✅ **Profil joueur complet** :
  * Informations personnelles (WhatsApp, Pays, Ville)
  * Multi-sélection de jeux (E-football, FC Mobile, Dream League Soccer)
  * Pour chaque jeu : Pseudo + Screenshot de l'équipe
* ✅ **Validation de profil** : Les modérateurs valident les profils avant participation
* ✅ **Système d'organisateurs** avec badges (Certified, Verified, Partner)

## 🎮 Tournois Format Suisse

* ✅ **Création de tournois** par les Organisateurs
* ✅ **Frais d'inscription** avec système de wallet
* ✅ **Calcul automatique des tours** : N = ⌈log₂(P)⌉ où P = nombre de participants
* ✅ **Appariement intelligent** : Joueurs avec même score s'affrontent
* ✅ **Aucune élimination** : Tout le monde joue toutes les rondes
* ✅ **Classement final** basé sur les points accumulés
* ✅ **Saisie des résultats** avec preuves (screenshots)
* ✅ **Distribution automatique des gains** aux gagnants
* ✅ **Blocage de fonds** des organisateurs pendant le tournoi
* ✅ **Chat par match** avec upload de preuves

## 💰 Économie Complète

* ✅ **Système de wallet** avec balance et blocked_balance
* ✅ **Transactions** : crédit, débit, inscription tournoi, gains
* ✅ **Blocage automatique** des fonds organisateur au début du tournoi
* ✅ **Distribution automatique** des prix aux gagnants
* ✅ **Remboursements** en cas de retrait avant le tournoi
* ✅ **Gestion admin** : ajout de fonds, historique complet

## 🛡️ Modération & Administration

* ✅ **Dashboard modérateur** :
  * Validation des profils utilisateurs
  * Gestion des matchs disputés
  * Validation des vérifications d'organisateurs
* ✅ **Dashboard admin** :
  * Gestion complète des utilisateurs
  * Gestion des wallets (ajout de fonds)
  * Gestion des tournois
  * Statistiques avancées

## 📧 Notifications

* ✅ **Emails automatiques** :
  * Confirmation d'inscription au tournoi
  * Notification organisateur (nouvelle inscription)
  * Bienvenue organisateur (nouveau badge)
  * Validation/rejet de vérification

---

# 🧰 Stack technique

* **Backend :** Laravel 11.x
* **PHP :** 8.4
* **Base de données :** PostgreSQL 17
* **Authentification :** Laravel Sanctum + OAuth
* **Cache/Sessions :** Redis
* **Mail :** SMTP (Mailtrap/Gmail)
* **Storage :** Local (Public disk)
* **Tests :** PHPUnit (38 tests, 113 assertions)
* **Containerisation :** Docker + Docker Compose
* **Web Server :** Nginx

---

# ⚙️ Installation

## 🐳 Installation avec Docker (Recommandé)

### Prérequis

* Docker 20.10+
* Docker Compose 2.0+

### Étapes

#### 1️⃣ Cloner le dépôt

```bash
git clone https://github.com/votre-username/tourno-api.git
cd tourno-api
```

#### 2️⃣ Configuration de l'environnement

```bash
cp .env.example .env
```

Modifier le fichier `.env` avec vos paramètres :

```env
APP_NAME="Tourno API"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

# Base de données (PostgreSQL dans Docker)
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=tourno
DB_USERNAME=tourno_user
DB_PASSWORD=tourno_password

# Redis (dans Docker)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (Mailtrap pour dev)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tourno.app"
MAIL_FROM_NAME="${APP_NAME}"

# OAuth Google
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/api/auth/oauth/google/callback"
```

#### 3️⃣ Démarrer les containers Docker

```bash
docker-compose up -d
```

Cela va démarrer :
- ✅ App PHP 8.4 + Nginx
- ✅ PostgreSQL 17
- ✅ Redis

#### 4️⃣ Installer les dépendances

```bash
docker-compose exec app composer install
```

#### 5️⃣ Générer la clé d'application

```bash
docker-compose exec app php artisan key:generate
```

#### 6️⃣ Corriger les permissions

```bash
docker-compose exec app chmod +x /var/www/html/fix-permissions.sh
docker-compose exec app /var/www/html/fix-permissions.sh
```

#### 7️⃣ Migrations & seeders

```bash
docker-compose exec app php artisan migrate:fresh --seed
```

Cela va créer :
- 2 admins
- 5 modérateurs
- 50 joueurs

#### 8️⃣ Vérifier que tout fonctionne

```bash
# Tests
docker-compose exec app php artisan test

# Voir les logs
docker-compose logs -f app
```

L'API sera disponible sur :
👉 **http://localhost:80**

### Commandes Docker utiles

```bash
# Arrêter les containers
docker-compose down

# Redémarrer
docker-compose restart

# Voir les logs
docker-compose logs -f app

# Accéder au container
docker-compose exec app bash

# Exécuter des commandes artisan
docker-compose exec app php artisan [commande]

# Lancer les tests
docker-compose exec app php artisan test
```

---

## 🔧 Installation manuelle

### Prérequis

* PHP 8.2+
* Composer
* PostgreSQL 14+ (ou MySQL 8.0+)
* Redis (optionnel)

### Étapes

#### 1️⃣ Cloner le dépôt

```bash
git clone https://github.com/votre-username/tourno-api.git
cd tourno-api
```

#### 2️⃣ Installer les dépendances

```bash
composer install
```

#### 3️⃣ Configuration de l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Configurer la base de données dans `.env` :

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tourno
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### 4️⃣ Migrations & seeders

```bash
php artisan migrate:fresh --seed
```

#### 5️⃣ Lancer le serveur

```bash
php artisan serve
```

L'API sera disponible sur :
👉 [http://localhost:8000](http://localhost:8000)

---

# 📚 Documentation

### Documentation API disponible

* **Guide Admin & Modérateur** : [`docs/ADMIN_MODERATOR_ENDPOINTS.md`](docs/ADMIN_MODERATOR_ENDPOINTS.md)
* **Inscription aux tournois** : [`docs/TOURNAMENT_REGISTRATION.md`](docs/TOURNAMENT_REGISTRATION.md)
* **Wallet & Blocage de fonds** : [`docs/WALLET_LOCKED_BALANCE.md`](docs/WALLET_LOCKED_BALANCE.md)

### Endpoints principaux

```http
# Authentification
POST   /api/auth/oauth/{provider}/redirect
POST   /api/auth/magic-link/send
POST   /api/auth/magic-link/verify

# Utilisateurs (Admin)
GET    /api/users
GET    /api/users/{id}
PATCH  /api/users/{id}/role
GET    /api/users/statistics

# Profils (Modérateur)
GET    /api/profiles/pending
POST   /api/profiles/{id}/validate
POST   /api/profiles/{id}/reject

# Tournois
GET    /api/tournaments
POST   /api/tournaments
GET    /api/tournaments/{id}
POST   /api/tournaments/{id}/register
POST   /api/tournaments/{id}/start

# Matchs
GET    /api/matches/{id}
POST   /api/matches/{id}/submit-result
GET    /api/matches/disputed/all
POST   /api/matches/{id}/validate

# Organisateurs
POST   /api/organizers/upgrade
POST   /api/organizers/verification/submit
GET    /api/organizers/verification/pending

# Wallet
GET    /api/wallet
POST   /api/wallet/add-funds (Admin)
GET    /api/wallet/transactions
```

---

# 🧪 Tests

Le projet inclut une suite complète de tests :

```bash
# Avec Docker
docker-compose exec app php artisan test

# Sans Docker
php artisan test
```

**Résultats actuels :**
- ✅ **38 tests** passés
- ✅ **113 assertions**
- ✅ Couverture : Feature (7) + Unit (6)

### Suites de tests

* **TournamentRegistrationTest** - Tests d'inscription aux tournois
* **TournamentStatusTest** - Tests de changement de statut
* **AdminModeratorTest** - Tests des endpoints admin/modérateur
* **WalletLockTest** - Tests de blocage de fonds
* **TournamentRegistrationServiceTest** - Tests unitaires service
* **WalletLockServiceTest** - Tests unitaires wallet

---

# 🤝 Contribution

Toutes les contributions sont les bienvenues !

### Comment contribuer ?

1. 🐞 **Signaler un bug** → Issues GitHub
2. 💡 **Proposer une fonctionnalité** → Discussions
3. 🧩 **Soumettre du code** :
   * Fork
   * Branch : `git checkout -b feature/AmazingFeature`
   * Commit : `git commit -m "Add AmazingFeature"`
   * Push & Pull Request

### Domaines où vous pouvez aider

* Tests unitaires / intégration
* App mobile (Angular/Ionic)
* UI/UX design
* Traductions
* Sécurité & audits
* Documentation

---

# 🗺️ Roadmap

## ✅ Phase 1 : MVP (TERMINÉ)

* [x] Architecture Laravel
* [x] **Auth & Rôles**
  * [x] Authentification OAuth Google
  * [x] Magic Link Authentication
  * [x] Laravel Sanctum
  * [x] Système de rôles (Admin, Modérateur, Organisateur, Joueur)
* [x] **Profil Joueur**
  * [x] Modèles & migrations (User, Profile, GameAccount)
  * [x] Multi-sélection de jeux (E-football, FC Mobile, DLS)
  * [x] Upload de screenshots par jeu
  * [x] Workflow de validation par modérateurs
* [x] **Wallet Complet**
  * [x] Balance et blocked_balance
  * [x] Historique des transactions
  * [x] Blocage automatique des fonds organisateur
  * [x] Distribution automatique des gains
* [x] **Tournois Format Suisse**
  * [x] CRUD Tournois (création par Organisateurs)
  * [x] Inscription aux tournois (déduction wallet)
  * [x] Calcul automatique du nombre de tours
  * [x] Génération d'appariements (système Suisse)
  * [x] Gestion des rondes
  * [x] Saisie des résultats avec screenshots
  * [x] Classement du tournoi
  * [x] Distribution automatique des gains
* [x] **Emails**
  * [x] Confirmation d'inscription
  * [x] Notification organisateur
  * [x] Bienvenue organisateur
* [x] **Tests**
  * [x] 38 tests (Feature + Unit)
* [x] **Docker**
  * [x] Docker Compose avec PostgreSQL + Redis

## 🚀 Phase 2 : Économie Complète

* [ ] Recharge de pièces (Mobile Money / Carte bancaire)
* [ ] Retrait de fonds vers Mobile Money
* [ ] Dashboard financier pour organisateurs
* [ ] Commissions plateforme

## 📊 Phase 3 : Fonctionnalités Avancées

* [ ] Système de litiges avec arbitrage
* [ ] GPA Rank (ELO) - Classement global
* [ ] Statistiques joueur détaillées
* [ ] Autres formats de tournois (K.O., Round Robin, Champions League)

## 💬 Phase 4 : Social & Communication

* [ ] Notifications push (Firebase)
* [ ] Chat intégré par tournoi
* [ ] Système de réputation
* [ ] Partage sur réseaux sociaux

## 🏆 Phase 5 : Divisions Automatiques

* [ ] Système de divisions (Ligue 1, 2, 3...)
* [ ] Promotion/Relégation automatique
* [ ] Tournois récurrents par division

## 🔧 Phase 6 : Production & Qualité

* [ ] Tests unitaires & intégration (80%+)
* [ ] Documentation API (Swagger)
* [ ] CI/CD Pipeline
* [ ] Monitoring & Analytics
* [ ] Déploiement production

---

# 📄 Licence

**MIT** — libre d'utilisation, modification et distribution.

---

# 📬 Contact & Support

* **Issues** : GitHub Issues
* **Discussions** : GitHub Discussions
* **Email** : contact@tourno.app

---

# ❤️ Fait avec passion pour les joueurs de football mobile.

---
