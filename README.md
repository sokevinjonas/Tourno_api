
---

# ⚽ Mobile League Manager (MLM) - API

**La plateforme de référence pour l'organisation et la gestion de tournois de jeux de simulation de football mobile.**

<p align="center">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg" />
  <img src="https://img.shields.io/badge/Laravel-11.x-red.svg" />
  <img src="https://img.shields.io/badge/Open%20Source-%E2%9D%A4-brightgreen.svg" />
</p>

---

# 📘 Sommaire

* [📘 Sommaire](#-sommaire)
* [📌 À propos du projet](#-à-propos-du-projet)
* [🎯 Objectifs du MVP](#-objectifs-du-mvp)
* [✨ Fonctionnalités principales (MVP)](#-fonctionnalités-principales-mvp)
* [🧰 Stack technique](#-stack-technique)
* [⚙️ Installation](#️-installation)
* [📚 Documentation](#-documentation)
* [🤝 Contribution](#-contribution)
* [🗺️ Roadmap](#️-roadmap)
  * [Phase 1 : MVP](#-phase-1--mvp-minimum-viable-product)
  * [Phase 2 : Économie Complète](#-phase-2--économie-complète)
  * [Phase 3 : Fonctionnalités Avancées](#-phase-3--fonctionnalités-avancées)
  * [Phase 4 : Social & Communication](#-phase-4--social--communication)
  * [Phase 5 : Divisions Automatiques](#-phase-5--divisions-automatiques)
  * [Phase 6 : Production & Qualité](#-phase-6--production--qualité)
* [📄 Licence](#-licence)
* [📬 Contact & Support](#-contact--support)
* [🙏 Remerciements](#-remerciements)

---

# 📌 À propos du projet

**Mobile League Manager (MLM)** est une API REST développée avec **Laravel** permettant aux joueurs de jeux mobiles de football (Dream League Soccer, E-football, FC Mobile…) d’organiser et gérer des compétitions automatiquement.

---

# 🎯 Objectifs du MVP

* **Simplicité** : Inscription et création de tournoi en quelques clics
* **Automatisation** : Système Suisse avec appariements automatiques
* **Validation** : Modération des profils avant participation
* **Économie simple** : 10 pièces gratuites pour démarrer (1 pièce = 500 FCFA)
* **Multi-jeux** : Support E-football, FC Mobile, Dream League Soccer

---

# ✨ Fonctionnalités principales (MVP)

## 👥 Gestion des utilisateurs & Rôles

* 🔐 **Authentification sécurisée** (Laravel Sanctum)
* 👤 **4 rôles** : Admin, Modérateur, Organisateur, Joueur
* 📝 **Profil joueur complet** :
  * Informations personnelles (WhatsApp, Pays, Ville)
  * Multi-sélection de jeux (E-football, FC Mobile, Dream League Soccer)
  * Pour chaque jeu : Pseudo + Screenshot de l'équipe
* ✅ **Validation de profil** : Les modérateurs valident les profils avant participation
* 🎁 **10 pièces offertes** après validation du profil (1 pièce = 500 FCFA)

## 🎮 Tournois Format Suisse

* 🏆 **Création de tournois** par les Organisateurs
* 💰 **Frais d'inscription en pièces** MLM
* 📊 **Calcul automatique des tours** : N = ⌈log₂(P)⌉ où P = nombre de participants
* 🎯 **Appariement intelligent** : Joueurs avec même score s'affrontent
* ♻️ **Aucune élimination** : Tout le monde joue toutes les rondes
* 🏅 **Classement final** basé sur les points accumulés
* 📸 **Saisie des résultats** avec screenshots
* 💸 **Distribution automatique des gains** aux gagnants

## 💰 Économie Simplifiée (MVP)

* 💳 **Système de pièces MLM** : 1 pièce = 500 FCFA
* 🎁 **10 pièces gratuites** à l'inscription (après validation du profil)
* 🏆 **Gains automatiques** : Les gains vont dans le solde du joueur
* 🎮 **Inscription aux tournois** : Déduction automatique des pièces
* ⚠️ **Pas de recharge/retrait** dans le MVP (Phase 2)

---

# 🧰 Stack technique

* **Backend :** Laravel 11.x
* **Base de données :** MySQL / PostgreSQL
* **Authentification :** Laravel Sanctum
* **Queues :** Redis (optionnel)
* **Tests :** PHPUnit / Pest
* **Documentation :** Swagger / OpenAPI

---

# ⚙️ Installation

### 🔧 Prérequis

* PHP 8.2+
* Composer
* MySQL 8.0+ ou PostgreSQL 14+
* Redis (optionnel)

---

### 🏗️ Étapes d'installation

#### 1️⃣ Cloner le dépôt

```bash
git clone https://github.com/votre-username/mlm-api.git
cd mlm-api
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
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mlm
DB_USERNAME=root
DB_PASSWORD=
```

#### 4️⃣ Migrations & seeders

```bash
php artisan migrate
php artisan db:seed
```

#### 5️⃣ Lancer le serveur

```bash
php artisan serve
```

L’API sera disponible sur :
👉 [http://localhost:8000](http://localhost:8000)

---

# 📚 Documentation

* **Cahier des charges complet** : `./cahier_de_charge.md`
* **Documentation API (Swagger)** : *(à venir)*
* **Guide de contribution** : *(à venir)*

---

# 🤝 Contribution

Toutes les contributions sont les bienvenues !

### Comment contribuer ?

1. 🐞 **Signaler un bug** → Issues GitHub

2. 💡 **Proposer une fonctionnalité** → Discussions

3. 🧩 **Soumettre du code** :

   * Fork
   * Branch :

     ```bash
     git checkout -b feature/AmazingFeature
     ```
   * Commit :

     ```bash
     git commit -m "Add AmazingFeature"
     ```
   * Push & Pull Request

4. 📝 Améliorer la documentation

5. 🧪 Tester l'application

---

### Domaines où tu peux aider

* Tests unitaires / intégration
* App mobile (Ionic)
* UI/UX design
* Traductions
* Optimisation du système ELO
* Sécurité & audits

---

# 🗺️ Roadmap

## 🎯 Phase 1 : MVP (Minimum Viable Product)

* [x] Architecture Laravel
* [ ] **Auth & Rôles**
  * [ ] Authentification Laravel Sanctum
  * [ ] Système de rôles (Admin, Modérateur, Organisateur, Joueur)
  * [ ] Gestion des permissions
* [ ] **Profil Joueur**
  * [ ] Modèles & migrations (User, Profile, GameAccount)
  * [ ] Multi-sélection de jeux (E-football, FC Mobile, DLS)
  * [ ] Upload de screenshots par jeu
  * [ ] Workflow de validation par modérateurs
* [ ] **Wallet Simplifié**
  * [ ] Système de pièces MLM (1 pièce = 500 FCFA)
  * [ ] Attribution de 10 pièces après validation du profil
  * [ ] Historique des transactions
* [ ] **Tournois Format Suisse**
  * [ ] CRUD Tournois (création par Organisateurs)
  * [ ] Inscription aux tournois (déduction de pièces)
  * [ ] Calcul automatique du nombre de tours : N = ⌈log₂(P)⌉
  * [ ] Génération d'appariements (système Suisse)
  * [ ] Gestion des rondes
  * [ ] Saisie des résultats avec screenshots
  * [ ] Classement du tournoi
  * [ ] Distribution automatique des gains

## 🚀 Phase 2 : Économie Complète

* [ ] Recharge de pièces (Mobile Money / Carte bancaire)
* [ ] Retrait de fonds vers Mobile Money
* [ ] Historique complet des transactions
* [ ] Dashboard financier pour organisateurs

## 📊 Phase 3 : Fonctionnalités Avancées

* [ ] Système de litiges avec arbitrage
* [ ] MLM Rank (ELO) - Classement global
* [ ] Statistiques joueur détaillées
* [ ] Autres formats de tournois (K.O., Round Robin)

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

---

# 📄 Licence

**MIT** — libre d’utilisation, modification et distribution.

---

# 📬 Contact & Support

* **Issues** : GitHub Issues
* **Discussions** : GitHub Discussions
* **Email** : [contact@mlm-api.com](mailto:contact@mlm-api.com) *(à définir)*

---

# 🙏 Remerciements

Merci à tous les contributeurs !
Un grand merci à la communauté Laravel ❤️

---

# ❤️ Fait avec amour par la communauté pour les joueurs de football mobile.

---
