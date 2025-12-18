# 🚀 PLAN DE DÉVELOPPEMENT - MVP

## 📋 Vue d'ensemble

Ce document décrit le plan de développement étape par étape pour le MVP de Mobile League Manager.

---

## ✅ Phase 1 : Configuration & Migrations (Jour 1-2)

### Étape 1.1 : Configuration Laravel
- [x] Projet Laravel 11.x initialisé
- [ ] Configuration `.env`
- [ ] Installation packages :
  - `laravel/sanctum` - Authentification API
  - `laravel/socialite` - OAuth Social
  - `intervention/image` (optionnel) - Manipulation d'images

### Étape 1.2 : Création des Migrations
Ordre de création :

1. ✅ `create_users_table` - Table users (modifiée sans password)
2. ✅ `create_oauth_providers_table` - OAuth providers
3. ✅ `create_login_tokens_table` - Magic links
4. ✅ `create_profiles_table` - Profils utilisateurs
5. ✅ `create_game_accounts_table` - Comptes de jeu
6. ✅ `create_wallets_table` - Portefeuilles
7. ✅ `create_tournaments_table` - Tournois
8. ✅ `create_tournament_registrations_table` - Inscriptions
9. ✅ `create_transactions_table` - Transactions
10. ✅ `create_rounds_table` - Rondes
11. ✅ `create_matches_table` - Matchs
12. ✅ `create_match_results_table` - Résultats

**Commandes** :
```bash
php artisan make:migration create_users_table
php artisan make:migration create_oauth_providers_table
php artisan make:migration create_login_tokens_table
php artisan make:migration create_profiles_table
php artisan make:migration create_game_accounts_table
php artisan make:migration create_wallets_table
php artisan make:migration create_tournaments_table
php artisan make:migration create_tournament_registrations_table
php artisan make:migration create_transactions_table
php artisan make:migration create_rounds_table
php artisan make:migration create_matches_table
php artisan make:migration create_match_results_table
```

Puis exécuter :
```bash
php artisan migrate
```

---

## ✅ Phase 2 : Modèles Eloquent (Jour 2-3)

### Étape 2.1 : Création des Modèles

```bash
php artisan make:model User
php artisan make:model OAuthProvider
php artisan make:model LoginToken
php artisan make:model Profile
php artisan make:model GameAccount
php artisan make:model Wallet
php artisan make:model Transaction
php artisan make:model Tournament
php artisan make:model TournamentRegistration
php artisan make:model Round
php artisan make:model Match
php artisan make:model MatchResult
```

### Étape 2.2 : Configuration des Relations
Pour chaque modèle, définir :
- Relations Eloquent (`hasOne`, `hasMany`, `belongsTo`)
- Fillable/Guarded
- Casts
- Accessors/Mutators

---

## ✅ Phase 3 : Authentification (Jour 3-5)

### Étape 3.1 : Installation & Configuration
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
composer require laravel/socialite
```

### Étape 3.2 : OAuth Social
- [ ] `OAuthController` - Gestion OAuth
- [ ] `OAuthService` - Logique OAuth
- [ ] Routes OAuth (`/auth/oauth/{provider}/redirect`, `/callback`)
- [ ] Configuration providers (Google, Apple, Facebook) dans `config/services.php`

### Étape 3.3 : Magic Link
- [ ] `MagicLinkController` - Gestion Magic Link
- [ ] `MagicLinkService` - Génération et vérification tokens
- [ ] `MagicLinkMail` - Email avec le lien
- [ ] Routes Magic Link (`/auth/magic-link/send`, `/verify`)

### Étape 3.4 : Tests Auth
- [ ] Test OAuth Google
- [ ] Test OAuth Apple
- [ ] Test OAuth Facebook
- [ ] Test Magic Link envoi
- [ ] Test Magic Link vérification
- [ ] Test expiration token

---

## ✅ Phase 4 : Profils & Validation (Jour 5-7)

### Étape 4.1 : Gestion des Profils
- [ ] `ProfileController` - CRUD profils
- [ ] `ProfileService` - Logique métier
- [ ] `CompleteProfileRequest` - Validation formulaire
- [ ] Upload screenshots (Storage)
- [ ] Routes profils

### Étape 4.2 : Modération
- [ ] `ProfileValidationController` - Validation par modérateurs
- [ ] Middleware `EnsureProfileValidated`
- [ ] Event `ProfileValidated`
- [ ] Listener `CreateWalletForValidatedProfile` - Créer wallet + 10 pièces

### Étape 4.3 : Tests Profils
- [ ] Test complétion profil
- [ ] Test upload screenshots
- [ ] Test validation modérateur
- [ ] Test rejet profil
- [ ] Test attribution 10 pièces

---

## ✅ Phase 5 : Wallet & Transactions (Jour 7-8)

### Étape 5.1 : Système de Wallet
- [ ] `WalletController` - Affichage solde
- [ ] `WalletService` - Crédit/Débit/Transactions
- [ ] `TransactionController` - Historique
- [ ] Exception `InsufficientBalanceException`

### Étape 5.2 : Tests Wallet
- [ ] Test création wallet
- [ ] Test débit
- [ ] Test crédit
- [ ] Test solde insuffisant
- [ ] Test historique transactions

---

## ✅ Phase 6 : Tournois Format Suisse (Jour 8-12)

### Étape 6.1 : CRUD Tournois
- [ ] `TournamentController` - CRUD complet
- [ ] `TournamentService` - Création, démarrage
- [ ] `CreateTournamentRequest` - Validation
- [ ] `TournamentResource` - Transformation JSON
- [ ] Routes tournois

### Étape 6.2 : Inscriptions
- [ ] `TournamentRegistrationController` - Inscription/Désinscription
- [ ] Vérification profil validé
- [ ] Vérification solde suffisant
- [ ] Déduction pièces

### Étape 6.3 : Système Suisse
- [ ] `SwissSystemService` - Algorithme d'appariement
- [ ] Calcul nombre de tours : N = ⌈log₂(P)⌉
- [ ] Génération rondes
- [ ] Génération matchs
- [ ] Appariement Round 1 (aléatoire)
- [ ] Appariement Rounds suivants (par score)

### Étape 6.4 : Tests Tournois
- [ ] Test création tournoi
- [ ] Test inscription
- [ ] Test désinscription
- [ ] Test démarrage tournoi
- [ ] Test génération rondes
- [ ] Test appariements

---

## ✅ Phase 7 : Matchs & Résultats (Jour 12-15)

### Étape 7.1 : Gestion des Matchs
- [ ] `MatchController` - Affichage matchs
- [ ] `MatchResultController` - Soumission résultats
- [ ] `MatchService` - Validation résultats
- [ ] Upload screenshots résultats

### Étape 7.2 : Validation Automatique
- [ ] Comparer les 2 résultats soumis
- [ ] Si identiques → Validation auto
- [ ] Si différents → Status "disputed"
- [ ] Organisateur tranche les litiges

### Étape 7.3 : Mise à jour Stats
- [ ] Update `tournament_registrations` (points, wins, draws, losses)
- [ ] 3 points victoire, 1 nul, 0 défaite
- [ ] Calcul classement

### Étape 7.4 : Tests Matchs
- [ ] Test soumission résultat
- [ ] Test validation automatique
- [ ] Test litige
- [ ] Test résolution litige
- [ ] Test mise à jour stats

---

## ✅ Phase 8 : Distribution des Gains (Jour 15-16)

### Étape 8.1 : Fin de Tournoi
- [ ] `PrizeDistributionService` - Distribution gains
- [ ] Calcul prize pool
- [ ] Application prize_distribution JSON
- [ ] Création transactions "tournament_prize"
- [ ] Crédit wallets gagnants

### Étape 8.2 : Tests Distribution
- [ ] Test calcul prize pool
- [ ] Test distribution 50/30/20
- [ ] Test distribution custom
- [ ] Test crédit wallets

---

## ✅ Phase 9 : Modération & Admin (Jour 16-18)

### Étape 9.1 : Dashboard Modérateur
- [ ] `ProfileValidationController` - Liste profils en attente
- [ ] Middleware `CheckRole`
- [ ] Routes modération

### Étape 9.2 : Dashboard Admin
- [ ] `AdminUserController` - Gestion users
- [ ] `AdminTournamentController` - Supervision tournois
- [ ] `FinanceController` - Vue finances
- [ ] Routes admin

### Étape 9.3 : Policies
- [ ] `TournamentPolicy` - Autorisations tournois
- [ ] `MatchPolicy` - Autorisations matchs
- [ ] `ProfilePolicy` - Autorisations profils

---

## ✅ Phase 10 : API Resources & Documentation (Jour 18-19)

### Étape 10.1 : API Resources
- [ ] `UserResource`
- [ ] `ProfileResource`
- [ ] `TournamentResource`
- [ ] `TournamentDetailResource`
- [ ] `MatchResource`
- [ ] `WalletResource`
- [ ] `TransactionResource`

### Étape 10.2 : Documentation
- [ ] Installer Swagger / OpenAPI
- [ ] Documenter tous les endpoints
- [ ] Exemples de requêtes/réponses

---

## ✅ Phase 11 : Tests & Qualité (Jour 19-21)

### Étape 11.1 : Tests Unitaires
- [ ] Tests Services (80%+)
- [ ] Tests Modèles

### Étape 11.2 : Tests d'Intégration
- [ ] Tests Controllers
- [ ] Tests Workflows complets

### Étape 11.3 : Tests E2E
- [ ] Scénario complet : Inscription → Profil → Tournoi → Match → Gain

---

## ✅ Phase 12 : Déploiement (Jour 21-22)

### Étape 12.1 : Configuration Production
- [ ] Variables d'environnement
- [ ] Configuration base de données
- [ ] Configuration storage (S3 ou équivalent)
- [ ] Configuration email (SMTP)

### Étape 12.2 : Optimisations
- [ ] Cache config : `php artisan config:cache`
- [ ] Cache routes : `php artisan route:cache`
- [ ] Optimisation Composer : `composer install --optimize-autoloader --no-dev`

### Étape 12.3 : Monitoring
- [ ] Logs (Laravel Telescope optionnel)
- [ ] Error tracking (Sentry optionnel)

---

## 📊 Suivi de Progression

| Phase | Statut | Durée Estimée |
|-------|--------|---------------|
| Phase 1 : Migrations | 🔄 En cours | 1-2 jours |
| Phase 2 : Modèles | ⏳ À faire | 1-2 jours |
| Phase 3 : Auth | ⏳ À faire | 2-3 jours |
| Phase 4 : Profils | ⏳ À faire | 2-3 jours |
| Phase 5 : Wallet | ⏳ À faire | 1-2 jours |
| Phase 6 : Tournois | ⏳ À faire | 3-4 jours |
| Phase 7 : Matchs | ⏳ À faire | 3-4 jours |
| Phase 8 : Distribution | ⏳ À faire | 1-2 jours |
| Phase 9 : Admin | ⏳ À faire | 2-3 jours |
| Phase 10 : Resources | ⏳ À faire | 1-2 jours |
| Phase 11 : Tests | ⏳ À faire | 2-3 jours |
| Phase 12 : Déploiement | ⏳ À faire | 1-2 jours |

**Total estimé : 20-30 jours de développement**

---

## 🎯 Prochaines Actions Immédiates

1. ✅ Créer les 12 migrations Laravel
2. ✅ Exécuter `php artisan migrate`
3. ✅ Créer les 12 modèles Eloquent
4. ✅ Configurer les relations
5. ✅ Commencer l'authentification OAuth + Magic Link

---

**Dernière mise à jour** : 18/12/2024
