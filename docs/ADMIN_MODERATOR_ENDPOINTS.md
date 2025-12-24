# 👨‍💼 Guide Admin & Modérateur - Tourno API

## 📋 Table des Matières

- [Rôles et Permissions](#rôles-et-permissions)
- [Endpoints Modérateur](#endpoints-modérateur)
- [Endpoints Admin](#endpoints-admin)
- [Dashboard Recommandations](#dashboard-recommandations)

---

## 🔐 Rôles et Permissions

### Hiérarchie des Rôles

```
Admin
  ├── Toutes les permissions modérateur
  ├── Gestion des wallets (ajout de fonds)
  ├── Gestion complète des tournois
  └── Accès à toutes les fonctionnalités

Modérateur
  ├── Validation des profils utilisateurs
  ├── Gestion des matchs disputés
  ├── Validation des vérifications d'organisateurs
  └── Lecture seule sur la plupart des données

Organisateur
  ├── Création et gestion de leurs tournois
  ├── Entrée manuelle des scores
  └── Gestion des participants
```

---

## 🛡️ Endpoints Modérateur

### 1. Validation des Profils Utilisateurs

#### Obtenir les profils en attente

```http
GET /api/profiles/pending
Authorization: Bearer {moderator_token}
```

**Réponse (200) :**
```json
{
  "profiles": [
    {
      "id": 1,
      "user_id": 10,
      "user": {
        "id": 10,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "whatsapp_number": "+1234567890",
      "country": "France",
      "city": "Paris",
      "status": "pending",
      "created_at": "2025-12-23T10:00:00.000000Z"
    }
  ]
}
```

---

#### Valider un profil

```http
POST /api/profiles/{id}/validate
Authorization: Bearer {moderator_token}
```

**Réponse (200) :**
```json
{
  "message": "Profile validated successfully",
  "profile": {
    "id": 1,
    "status": "validated",
    "validated_by": 5,
    "validated_at": "2025-12-23T14:30:00.000000Z"
  }
}
```

**Impact :**
- ✅ L'utilisateur peut maintenant s'inscrire aux tournois
- ✉️ Email envoyé à l'utilisateur pour confirmer la validation

---

#### Rejeter un profil

```http
POST /api/profiles/{id}/reject
Authorization: Bearer {moderator_token}
Content-Type: application/json

{
  "rejection_reason": "Informations incomplètes ou incorrectes"
}
```

**Paramètres :**
- `rejection_reason` (string, requis) - Raison du rejet

**Réponse (200) :**
```json
{
  "message": "Profile rejected successfully",
  "profile": {
    "id": 1,
    "status": "rejected",
    "rejection_reason": "Informations incomplètes ou incorrectes",
    "validated_by": 5,
    "validated_at": "2025-12-23T14:30:00.000000Z"
  }
}
```

**Impact :**
- ❌ L'utilisateur ne peut pas s'inscrire aux tournois
- ✉️ Email envoyé à l'utilisateur avec la raison du rejet

---

### 2. Gestion des Matchs Disputés

#### Obtenir tous les matchs disputés

```http
GET /api/matches/disputed/all
Authorization: Bearer {moderator_token}
```

**Réponse (200) :**
```json
{
  "matches": [
    {
      "id": 15,
      "tournament_id": 1,
      "round_id": 2,
      "player1_id": 10,
      "player2_id": 20,
      "player1": {
        "id": 10,
        "name": "Player 1"
      },
      "player2": {
        "id": 20,
        "name": "Player 2"
      },
      "player1_score": 2,
      "player2_score": 1,
      "status": "disputed",
      "dispute_reason": "Screenshot invalide",
      "created_at": "2025-12-23T10:00:00.000000Z"
    }
  ]
}
```

---

#### Valider le résultat d'un match

```http
POST /api/matches/{id}/validate
Authorization: Bearer {moderator_token}
Content-Type: application/json

{
  "winner_id": 10,
  "player1_score": 2,
  "player2_score": 1
}
```

**Paramètres :**
- `winner_id` (integer, requis) - ID du gagnant
- `player1_score` (integer, requis) - Score du joueur 1
- `player2_score` (integer, requis) - Score du joueur 2

**Réponse (200) :**
```json
{
  "message": "Match result validated successfully",
  "match": {
    "id": 15,
    "status": "completed",
    "winner_id": 10,
    "player1_score": 2,
    "player2_score": 1,
    "validated_by": 5,
    "validated_at": "2025-12-23T14:30:00.000000Z"
  }
}
```

**Impact :**
- ✅ Le résultat est confirmé définitivement
- 📊 Les points du tournoi sont mis à jour
- ✉️ Notifications envoyées aux deux joueurs

---

### 3. Validation des Vérifications d'Organisateurs

#### Obtenir les vérifications en attente

```http
GET /api/organizers/verification/pending
Authorization: Bearer {moderator_token}
```

**Réponse (200) :**
```json
{
  "verifications": [
    {
      "id": 1,
      "user_id": 30,
      "user": {
        "id": 30,
        "name": "Potential Organizer",
        "email": "organizer@example.com"
      },
      "verification_document": "url_to_document",
      "status": "pending",
      "created_at": "2025-12-23T10:00:00.000000Z"
    }
  ]
}
```

---

#### Valider une vérification d'organisateur

```http
POST /api/organizers/verification/{id}/validate
Authorization: Bearer {moderator_token}
```

**Réponse (200) :**
```json
{
  "message": "Organizer verification validated successfully",
  "user": {
    "id": 30,
    "role": "organizer",
    "verified": true
  }
}
```

**Impact :**
- ✅ L'utilisateur devient organisateur vérifié
- 🎯 Peut créer des tournois officiels
- 🔵 Badge vérifié affiché sur son profil
- ✉️ Email de confirmation envoyé

---

#### Rejeter une vérification d'organisateur

```http
POST /api/organizers/verification/{id}/reject
Authorization: Bearer {moderator_token}
Content-Type: application/json

{
  "rejection_reason": "Documents insuffisants"
}
```

**Paramètres :**
- `rejection_reason` (string, requis) - Raison du rejet

**Réponse (200) :**
```json
{
  "message": "Organizer verification rejected successfully"
}
```

**Impact :**
- ❌ L'utilisateur reste avec son rôle actuel
- ✉️ Email envoyé avec la raison du rejet

---

## 👑 Endpoints Admin

**Note :** Les admins ont accès à TOUS les endpoints modérateur + les suivants :

### 1. Gestion des Wallets

#### Ajouter des fonds à un utilisateur

```http
POST /api/wallet/add-funds
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "user_id": 10,
  "amount": 50.00,
  "description": "Compensation pour un bug"
}
```

**Paramètres :**
- `user_id` (integer, requis) - ID de l'utilisateur
- `amount` (number, requis, min: 0.01) - Montant à ajouter
- `description` (string, optionnel) - Raison de l'ajout

**Réponse (200) :**
```json
{
  "message": "Funds added successfully",
  "transaction": {
    "id": 100,
    "type": "credit",
    "amount": "50.00",
    "reason": "admin_adjustment",
    "description": "Compensation pour un bug"
  },
  "new_balance": "150.00"
}
```

**Impact :**
- 💰 Le wallet de l'utilisateur est crédité
- 📝 Transaction enregistrée avec la raison
- ✉️ Email de notification envoyé à l'utilisateur

---

### 2. Gestion Complète des Tournois

**Note :** Les admins peuvent gérer N'IMPORTE QUEL tournoi, pas seulement les leurs.

#### Créer un tournoi (Admin/Organisateur)

```http
POST /api/tournaments
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Championnat eFootball 2025",
  "description": "Tournoi officiel",
  "game": "efootball",
  "format": "swiss",
  "max_participants": 32,
  "entry_fee": 5.00,
  "prize_distribution": {
    "1st": 100.00,
    "2nd": 60.00,
    "3rd": 40.00
  },
  "start_date": "2025-12-30 18:00:00",
  "tournament_duration_days": 7,
  "time_slot": "evening",
  "match_deadline_minutes": 90
}
```

---

#### Modifier un tournoi (Admin)

```http
PUT /api/tournaments/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Nouveau nom",
  "max_participants": 64
}
```

**Note :** Les admins peuvent modifier n'importe quel tournoi. Les organisateurs ne peuvent modifier que leurs tournois.

---

#### Supprimer un tournoi (Admin)

```http
DELETE /api/tournaments/{id}
Authorization: Bearer {admin_token}
```

**Conditions :**
- Le tournoi ne doit pas être `in_progress` ou `completed`

**Réponse (200) :**
```json
{
  "message": "Tournament deleted successfully"
}
```

**Impact :**
- 🗑️ Le tournoi est supprimé
- 💰 Tous les participants sont automatiquement remboursés

---

#### Changer le statut d'un tournoi (Admin/Organisateur)

```http
POST /api/tournaments/{id}/status
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "status": "cancelled"
}
```

**Statuts possibles :**
- `draft` - Brouillon
- `open` - Ouvert aux inscriptions
- `in_progress` - En cours
- `completed` - Terminé
- `cancelled` - Annulé

---

### 3. Gestion des Rounds (Admin/Organisateur)

#### Démarrer un tournoi

```http
POST /api/tournaments/{id}/start
Authorization: Bearer {admin_token}
```

**Actions automatiques :**
1. 🔒 Blocage des fonds de l'organisateur
2. 🎲 Génération du premier round
3. 📧 Emails envoyés à tous les participants
4. 📊 Création de tous les matchs

---

#### Générer le round suivant

```http
POST /api/tournaments/{id}/next-round
Authorization: Bearer {admin_token}
```

**Conditions :**
- Tous les matchs du round actuel doivent être terminés

---

#### Terminer un tournoi

```http
POST /api/tournaments/{id}/complete
Authorization: Bearer {admin_token}
```

**Actions automatiques :**
1. 💸 Distribution des prix aux gagnants
2. 🔓 Libération des fonds restants à l'organisateur
3. 📊 Calcul des statistiques finales
4. 📧 Emails de félicitations aux gagnants

---

## 📊 Dashboard Recommandations

### Dashboard Modérateur

**Sections recommandées :**

1. **Profils en Attente**
   - Liste des profils à valider
   - Badge avec nombre en attente
   - Filtres : date, pays, statut

2. **Matchs Disputés**
   - Liste des matchs en dispute
   - Filtres : tournoi, date
   - Accès rapide aux preuves (screenshots)

3. **Vérifications Organisateurs**
   - Demandes de vérification en attente
   - Documents à consulter
   - Historique des validations

4. **Statistiques**
   - Nombre de profils validés aujourd'hui
   - Nombre de disputes résolues
   - Temps moyen de traitement

---

### Dashboard Admin

**Sections recommandées :**

1. **Vue d'ensemble**
   - Utilisateurs actifs
   - Tournois en cours
   - Volume de transactions

2. **Gestion des Wallets**
   - Recherche d'utilisateur
   - Ajout rapide de fonds
   - Historique des ajustements

3. **Gestion des Tournois**
   - Tous les tournois (filtrables)
   - Actions rapides : annuler, modifier, supprimer
   - Monitoring des tournois en cours

4. **Modération** (toutes les sections modérateur)
   - Profils en attente
   - Matchs disputés
   - Vérifications organisateurs

5. **Statistiques Avancées**
   - Revenus plateforme
   - Utilisateurs par pays
   - Tournois par jeu
   - Taux de complétion des tournois

---

## 🎨 Composants UI Recommandés

### Carte de Profil en Attente

```jsx
<ProfileCard>
  <UserInfo>
    <Avatar src={profile.user.avatar} />
    <Name>{profile.user.name}</Name>
    <Email>{profile.user.email}</Email>
  </UserInfo>

  <Details>
    <InfoRow>
      <Icon>📱</Icon>
      <Value>{profile.whatsapp_number}</Value>
    </InfoRow>
    <InfoRow>
      <Icon>🌍</Icon>
      <Value>{profile.country}, {profile.city}</Value>
    </InfoRow>
    <InfoRow>
      <Icon>📅</Icon>
      <Value>{formatDate(profile.created_at)}</Value>
    </InfoRow>
  </Details>

  <Actions>
    <Button onClick={validateProfile} variant="success">
      ✅ Valider
    </Button>
    <Button onClick={rejectProfile} variant="danger">
      ❌ Rejeter
    </Button>
  </Actions>
</ProfileCard>
```

---

### Carte de Match Disputé

```jsx
<DisputedMatchCard>
  <MatchHeader>
    <TournamentName>{match.tournament.name}</TournamentName>
    <RoundInfo>Round {match.round.round_number}</RoundInfo>
  </MatchHeader>

  <Players>
    <Player winner={match.winner_id === match.player1_id}>
      <Avatar src={match.player1.avatar} />
      <Name>{match.player1.name}</Name>
      <Score>{match.player1_score}</Score>
    </Player>
    <VS>VS</VS>
    <Player winner={match.winner_id === match.player2_id}>
      <Avatar src={match.player2.avatar} />
      <Name>{match.player2.name}</Name>
      <Score>{match.player2_score}</Score>
    </Player>
  </Players>

  <DisputeInfo>
    <Icon>⚠️</Icon>
    <Reason>{match.dispute_reason}</Reason>
  </DisputeInfo>

  <Evidence>
    <Button onClick={viewEvidence}>
      📸 Voir les preuves
    </Button>
  </Evidence>

  <ValidateForm>
    <Select label="Vainqueur">
      <Option value={match.player1_id}>{match.player1.name}</Option>
      <Option value={match.player2_id}>{match.player2.name}</Option>
    </Select>
    <Input label="Score J1" type="number" />
    <Input label="Score J2" type="number" />
    <Button type="submit" variant="primary">
      ✅ Valider le résultat
    </Button>
  </ValidateForm>
</DisputedMatchCard>
```

---

## 🔒 Sécurité

### Vérification des Permissions

Toutes les routes admin/modérateur vérifient les permissions dans les contrôleurs :

```php
// Exemple dans ProfileController
if ($request->user()->role !== 'admin' && $request->user()->role !== 'moderator') {
    return response()->json(['message' => 'Unauthorized'], 403);
}
```

### Recommandations Frontend

1. **Masquer les routes** non autorisées dans le menu
2. **Vérifier le rôle** avant d'afficher les actions
3. **Gérer les erreurs 403** gracieusement
4. **Logger les actions** admin/modérateur pour audit

---

## 📞 Support

Pour toute question sur les endpoints admin/modérateur, contactez l'équipe backend.

**Date de dernière mise à jour :** 2025-12-23
