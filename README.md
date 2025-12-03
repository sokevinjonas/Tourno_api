
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
* [🎯 Objectifs](#-objectifs)
* [✨ Fonctionnalités principales](#-fonctionnalités-principales)
* [🧰 Stack technique](#-stack-technique)
* [⚙️ Installation](#️-installation)
* [📚 Documentation](#-documentation)
* [🤝 Contribution](#-contribution)
* [🗺️ Roadmap](#️-roadmap)
* [📄 Licence](#-licence)
* [📬 Contact & Support](#-contact--support)
* [🙏 Remerciements](#-remerciements)

---

# 📌 À propos du projet

**Mobile League Manager (MLM)** est une API REST développée avec **Laravel** permettant aux joueurs de jeux mobiles de football (Dream League Soccer, E-football, FC Mobile…) d’organiser et gérer des compétitions automatiquement.

---

# 🎯 Objectifs

* **Simplicité** : Créer un tournoi en quelques clics
* **Automatisation** : Génération automatique de brackets, validation des scores
* **Temps réel** : Notifications push, mises à jour instantanées
* **Fair-play** : Gestion des litiges intégrée
* **Performance** : Classement ELO (MLM Rank)

---

# ✨ Fonctionnalités principales

* ⚔️ **Tournois à élimination directe (K.O.)** : Brackets automatiques (8, 16, 32 joueurs)
* 🏆 **Ligues (Round Robin)** : Classement par points
* 📸 **Validation automatique des scores**
* ⚖️ **Système de litiges** avec arbitrage
* 📊 **MLM Rank (ELO)**
* 💬 **Chat intégré**
* 🔔 **Notifications push**
* 👤 **Profils joueurs & statistiques**

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

* [x] Architecture Laravel
* [ ] Modèles & migrations
* [ ] Auth Sanctum
* [ ] CRUD Tournois
* [ ] Génération de brackets
* [ ] Validation automatique des scores
* [ ] Gestion des litiges
* [ ] Calcul du MLM Rank
* [ ] Notifications
* [ ] Chat intégré
* [ ] Tests (80%+)
* [ ] Documentation API
* [ ] CI/CD

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
