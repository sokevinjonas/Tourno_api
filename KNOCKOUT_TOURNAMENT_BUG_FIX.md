# Bug Fix: Tournament Stats & Prize Distribution

## 📋 Table des matières

1. [Knockout Tournaments](#knockout-tournaments)
2. [Swiss Tournaments](#swiss-tournaments)
3. [Commandes de correction](#commandes-de-correction)

---

## Knockout Tournaments

### Bug #1: Stats non comptabilisées (RÉSOLU ✅)

**Symptôme:**
Un joueur qui a gagné 4 rounds (4 matchs) dans un tournoi knockout affiche:
- **Attendu:** 12 points (4 victoires × 3 points), 4 wins
- **Réel:** 10 points ou moins, 0 wins

**Cause racine:**
Dans `KnockoutFormatService::updateMatchResult()`, les statistiques de victoires/défaites n'étaient JAMAIS mises à jour dans `tournament_registrations`.

Seuls les champs suivants étaient mis à jour:
- `eliminated` = true/false
- `eliminated_round` = numéro du round

Les champs `wins`, `losses`, `tournament_points` restaient à 0.

**Impact:**
- À la fin du tournoi, `UserStatsService::updateStatsAfterTournament()` utilisait `$registration->wins` (qui valait 0) pour calculer les stats finales
- Les joueurs n'avaient pas leurs victoires comptabilisées
- Les points de tournoi étaient incorrects

**Solution implémentée:**
Ajout de deux nouvelles méthodes dans `KnockoutFormatService.php`:

```php
/**
 * Update player stats (wins/losses/points) in tournament registration
 */
protected function updatePlayerStats(int $userId, int $tournamentId, string $result): void
{
    $registration = TournamentRegistration::where('user_id', $userId)
        ->where('tournament_id', $tournamentId)
        ->first();

    if (!$registration) {
        \Log::warning("Registration not found for user {$userId} in tournament {$tournamentId}");
        return;
    }

    $updates = [];

    if ($result === 'win') {
        $updates['wins'] = $registration->wins + 1;
        $updates['tournament_points'] = $registration->tournament_points + 3;
    } elseif ($result === 'loss') {
        $updates['losses'] = $registration->losses + 1;
    }

    $registration->update($updates);
}

/**
 * Revert player stats (for score corrections)
 */
protected function revertPlayerStats(int $userId, int $tournamentId, string $result): void
{
    $registration = TournamentRegistration::where('user_id', $userId)
        ->where('tournament_id', $tournamentId)
        ->first();

    if (!$registration) {
        \Log::warning("Registration not found for user {$userId} in tournament {$tournamentId}");
        return;
    }

    $updates = [];

    if ($result === 'win') {
        $updates['wins'] = max(0, $registration->wins - 1);
        $updates['tournament_points'] = max(0, $registration->tournament_points - 3);
    } elseif ($result === 'loss') {
        $updates['losses'] = max(0, $registration->losses - 1);
    }

    $registration->update($updates);
}
```

**Modifications dans `updateMatchResult()`:**
```php
// Update tournament registration stats for both players
$this->updatePlayerStats($winnerId, $match->tournament_id, 'win');
$this->updatePlayerStats($loserId, $match->tournament_id, 'loss');
```

**Modifications dans `updateMatchScore()`:**
```php
// Revert old stats and apply new ones if winner changed
if ($winnerChanged) {
    $this->revertPlayerStats($oldWinnerId, $match->tournament_id, 'win');
    $this->revertPlayerStats($oldLoserId, $match->tournament_id, 'loss');
    $this->updatePlayerStats($newWinnerId, $match->tournament_id, 'win');
    $this->updatePlayerStats($newLoserId, $match->tournament_id, 'loss');
}
```

---

### Bug #2: Récompenses non distribuées (À VÉRIFIER ⚠️)

**Symptôme:**
Le joueur qui a gagné le tournoi affiche **0 GPA** au lieu de recevoir les 20 pièces attendues.

**Causes possibles:**

#### Possibilité 1: Dernier match non complété
Si le match de la finale n'a pas été marqué comme `completed`, le tournoi ne peut pas être clôturé correctement.

**Vérification:**
```sql
SELECT
    m.id,
    r.round_name,
    m.player1_id,
    m.player2_id,
    m.player1_score,
    m.player2_score,
    m.winner_id,
    m.status
FROM tournament_matches m
JOIN rounds r ON m.round_id = r.id
WHERE m.tournament_id = [TOURNAMENT_ID]
ORDER BY r.round_number DESC;
```

**Attendu:** Tous les matchs doivent avoir `status = 'completed'`

#### Possibilité 2: Prize Distribution mal configurée
La distribution des prix doit être stockée au format JSON dans `tournaments.prize_distribution`.

**Vérification:**
```sql
SELECT prize_distribution, entry_fee, total_prize_pool
FROM tournaments
WHERE uuid = '[TOURNAMENT_UUID]';
```

**Format attendu:**
```json
{
  "1st": 20,
  "2nd": 10,
  "3rd": 5
}
```

OU

```json
{
  "1": 20,
  "2": 10,
  "3": 5
}
```

Les deux formats sont supportés grâce à la méthode `getRankKey()` dans `KnockoutFormatService.php`.

#### Possibilité 3: Erreur lors de completeTournament()
Si une exception est levée pendant `completeTournament()`, la transaction est annulée et les paiements ne sont pas effectués.

**Vérification dans les logs Laravel:**
```bash
tail -f storage/logs/laravel.log | grep -i "tournament\|prize\|payout"
```

**Chercher:**
- Erreurs pendant `processPayouts()`
- Erreurs pendant `releaseFunds()`
- Messages "Tournament completion emails queued"

#### Possibilité 4: Fonds de l'organisateur insuffisants
Si l'organisateur n'avait pas assez de fonds dans son wallet, le `lockFundsForTournament()` aurait dû échouer au démarrage.

**Vérification:**
```sql
SELECT
    u.name,
    u.id,
    w.balance,
    t.total_prize_pool,
    t.status
FROM tournaments t
JOIN users u ON t.organizer_id = u.id
JOIN wallets w ON u.id = w.user_id
WHERE t.uuid = '[TOURNAMENT_UUID]';
```

**Attendu:** `wallet.balance >= total_prize_pool` au moment du démarrage

---

## 🔍 Diagnostic du problème actuel

Pour diagnostiquer le problème de récompenses, exécutez ces requêtes SQL:

### 1. Vérifier l'état du tournoi
```sql
SELECT
    t.uuid,
    t.name,
    t.status,
    t.format,
    t.prize_distribution,
    t.total_prize_pool,
    t.entry_fee,
    t.created_at,
    t.updated_at,
    u.name as organizer_name,
    w.balance as organizer_balance
FROM tournaments t
JOIN users u ON t.organizer_id = u.id
JOIN wallets w ON u.id = w.user_id
WHERE t.id = [TOURNAMENT_ID];
```

### 2. Vérifier les matchs
```sql
SELECT
    r.round_number,
    r.round_name,
    r.status as round_status,
    m.id as match_id,
    m.status as match_status,
    p1.name as player1,
    p2.name as player2,
    m.player1_score,
    m.player2_score,
    winner.name as winner,
    m.completed_at
FROM rounds r
LEFT JOIN tournament_matches m ON r.id = m.round_id
LEFT JOIN users p1 ON m.player1_id = p1.id
LEFT JOIN users p2 ON m.player2_id = p2.id
LEFT JOIN users winner ON m.winner_id = winner.id
WHERE r.tournament_id = [TOURNAMENT_ID]
ORDER BY r.round_number, m.bracket_position;
```

### 3. Vérifier les registrations et classement final
```sql
SELECT
    u.name,
    u.email,
    tr.wins,
    tr.losses,
    tr.tournament_points,
    tr.final_rank,
    tr.prize_won,
    tr.eliminated,
    tr.eliminated_round,
    w.balance as current_balance
FROM tournament_registrations tr
JOIN users u ON tr.user_id = u.id
JOIN wallets w ON u.id = w.user_id
WHERE tr.tournament_id = [TOURNAMENT_ID]
ORDER BY tr.final_rank ASC NULLS LAST;
```

### 4. Vérifier les transactions wallet
```sql
SELECT
    wt.id,
    wt.type,
    wt.amount,
    wt.description,
    wt.created_at,
    u.name as user_name
FROM wallet_transactions wt
JOIN users u ON wt.user_id = u.id
WHERE wt.description LIKE '%[TOURNAMENT_UUID]%'
OR wt.description LIKE '%[TOURNAMENT_NAME]%'
ORDER BY wt.created_at DESC;
```

---

## 🔧 Comment corriger un tournoi déjà affecté

Si le tournoi a déjà été complété AVANT le fix:

### Option 1: Recalculer et redistribuer (RECOMMANDÉ)

1. **Recalculer les stats manuellement:**
```sql
-- Pour chaque participant, compter ses victoires
UPDATE tournament_registrations tr
SET
    wins = (
        SELECT COUNT(*)
        FROM tournament_matches m
        WHERE m.tournament_id = tr.tournament_id
        AND m.winner_id = tr.user_id
        AND m.status = 'completed'
    ),
    losses = (
        SELECT COUNT(*)
        FROM tournament_matches m
        WHERE m.tournament_id = tr.tournament_id
        AND ((m.player1_id = tr.user_id AND m.winner_id != tr.user_id)
             OR (m.player2_id = tr.user_id AND m.winner_id != tr.user_id))
        AND m.status = 'completed'
    ),
    tournament_points = (
        SELECT COUNT(*) * 3
        FROM tournament_matches m
        WHERE m.tournament_id = tr.tournament_id
        AND m.winner_id = tr.user_id
        AND m.status = 'completed'
    )
WHERE tr.tournament_id = [TOURNAMENT_ID];
```

2. **Mettre à jour les stats globales:**
```php
// Via Tinker
php artisan tinker

$tournament = \App\Models\Tournament::find([TOURNAMENT_ID]);
$registrations = $tournament->registrations;
$userStatsService = app(\App\Services\UserStatsService::class);

foreach ($registrations as $registration) {
    $userStatsService->updateStatsAfterTournament(
        $registration->user,
        $tournament,
        $registration
    );
}
```

3. **Redistribuer les prix (si pas déjà fait):**
```php
// Via Tinker
$tournament = \App\Models\Tournament::find([TOURNAMENT_ID]);
$walletService = app(\App\Services\WalletService::class);
$knockoutService = app(\App\Services\KnockoutFormatService::class);

// Appeler completeTournament (qui va distribuer les prix)
$knockoutService->completeTournament($tournament, $walletService);
```

### Option 2: Correction manuelle des récompenses

Si les prix n'ont pas été distribués:

```sql
-- Vérifier qui devrait recevoir quoi
SELECT
    tr.user_id,
    u.name,
    tr.final_rank,
    tr.prize_won,
    t.prize_distribution
FROM tournament_registrations tr
JOIN users u ON tr.user_id = u.id
JOIN tournaments t ON tr.tournament_id = t.id
WHERE tr.tournament_id = [TOURNAMENT_ID]
AND tr.final_rank <= 3
ORDER BY tr.final_rank;
```

Puis distribuer manuellement via l'endpoint admin:
```bash
POST /api/wallet/add-funds
{
    "user_id": [USER_ID],
    "amount": 20,
    "description": "Prix du tournoi [TOURNAMENT_NAME] - 1ère place"
}
```

---

## ✅ Validation du fix

Après avoir appliqué le fix, pour vérifier qu'un nouveau tournoi fonctionne correctement:

### 1. Pendant le tournoi
Après chaque match complété, vérifier:
```sql
SELECT
    u.name,
    tr.wins,
    tr.losses,
    tr.tournament_points,
    tr.eliminated
FROM tournament_registrations tr
JOIN users u ON tr.user_id = u.id
WHERE tr.tournament_id = [TOURNAMENT_ID]
ORDER BY tr.tournament_points DESC;
```

**Attendu:** `wins`, `losses`, et `tournament_points` doivent s'incrémenter après chaque match.

### 2. À la fin du tournoi
Après `completeTournament()`, vérifier:

```sql
-- Les classements finaux
SELECT
    u.name,
    tr.final_rank,
    tr.prize_won,
    tr.wins,
    tr.losses,
    tr.tournament_points
FROM tournament_registrations tr
JOIN users u ON tr.user_id = u.id
WHERE tr.tournament_id = [TOURNAMENT_ID]
ORDER BY tr.final_rank;
```

```sql
-- Les transactions wallet
SELECT
    wt.id,
    u.name,
    wt.type,
    wt.amount,
    wt.description,
    wt.created_at
FROM wallet_transactions wt
JOIN users u ON wt.user_id = u.id
WHERE wt.description LIKE '%[TOURNAMENT_UUID]%'
ORDER BY wt.created_at;
```

**Attendu:**
- Les 3 premiers joueurs ont `prize_won > 0`
- Des transactions `prize_won` existent pour les gagnants
- Le total des transactions = `total_prize_pool`

---

### Bug #2: Prix non distribués (RÉSOLU ✅)

**Symptôme:**
Les joueurs ne reçoivent pas leurs récompenses même si le tournoi est complété avec succès.

**Cause racine:**
Dans `completeTournament()` (ligne 531), le code n'essayait qu'un seul format de clé:

```php
$rankKey = $this->getRankKey($rank);  // Retourne "1st", "2nd", "3rd"
$prizeAmount = $prizeDistribution[$rankKey] ?? null;  // ❌ Pas de fallback
```

Si le `prize_distribution` est au format numérique `{"1":20,"2":10,"3":5}`, le code cherche `"1st"` qui n'existe pas, trouve `null`, et ne distribue aucun prix.

**Solution implémentée:**
```php
$prizeAmount = $prizeDistribution[$rankKey] ?? $prizeDistribution[(string)$rank] ?? null;
```

Maintenant le code essaie:
1. Le format texte: `"1st"`, `"2nd"`, `"3rd"`
2. Le format numérique: `"1"`, `"2"`, `"3"` (fallback)
3. `null` si aucun des deux n'existe

**Impact:**
- Affecte **tous les tournois** (Knockout ET Swiss)
- Les prix n'étaient pas distribués si le format de clé ne correspondait pas
- Les `tournament_registrations.prize_won` restaient à 0

**Fichiers modifiés:**
- `app/Services/KnockoutFormatService.php` (ligne 475)
- `app/Services/SwissFormatService.php` (ligne 531)

---

## Swiss Tournaments

### Statut: FONCTIONNEL ✅

Le `SwissFormatService` dispose déjà des méthodes `updatePlayerStats()` et `revertPlayerStats()` qui gèrent correctement:
- Victoires: +3 points
- Matchs nuls: +1 point
- Défaites: 0 point

**Code existant dans SwissFormatService.php (lignes 446-469):**
```php
protected function updatePlayerStats(int $userId, int $tournamentId, string $result): void
{
    $registration = TournamentRegistration::where('user_id', $userId)
        ->where('tournament_id', $tournamentId)
        ->first();

    if (!$registration) {
        return;
    }

    $updates = [];

    if ($result === 'win') {
        $updates['wins'] = $registration->wins + 1;
        $updates['tournament_points'] = $registration->tournament_points + 3;
    } elseif ($result === 'draw') {
        $updates['draws'] = $registration->draws + 1;
        $updates['tournament_points'] = $registration->tournament_points + 1;
    } elseif ($result === 'loss') {
        $updates['losses'] = $registration->losses + 1;
    }

    $registration->update($updates);
}
```

Ces méthodes sont appelées dans:
- `updateMatchResult()` (ligne 285-289)
- `updateMatchScore()` (ligne 341-365) avec revert des anciennes stats
- `assignBye()` (ligne 254) pour les byes automatiques

### Correction des tournois Swiss anciens

Si un tournoi Swiss a été complété avant l'ajout de ces méthodes, utilisez:

```bash
php artisan tournament:fix-swiss-stats [tournament_id]
```

Sans ID, la commande trouve automatiquement le dernier tournoi Swiss complété.

---

## Commandes de correction

### Pour les tournois Knockout

```bash
# Avec ID spécifique
php artisan tournament:fix-knockout-stats 5

# Sans ID (trouve le dernier eFootball knockout avec 16 participants)
php artisan tournament:fix-knockout-stats
```

**Ce que fait la commande:**
1. Recalcule wins/losses/tournament_points depuis les matchs réels
2. Redistribue les prix si prize_won = 0
3. Met à jour les stats globales des joueurs (UserGameStat + UserGlobalStat)

**Validations:**
- Format doit être `single_elimination`
- Statut doit être `completed`
- Refuse les tournois Swiss ou Round Robin

### Pour les tournois Swiss

```bash
# Avec ID spécifique
php artisan tournament:fix-swiss-stats 1

# Sans ID (trouve le dernier Swiss complété)
php artisan tournament:fix-swiss-stats
```

**Ce que fait la commande:**
1. Recalcule wins/losses/draws/tournament_points depuis les matchs réels
2. Propose de recalculer les final_rank si non définis
3. Redistribue les prix si prize_won = 0
4. Met à jour les stats globales des joueurs

**Validations:**
- Format doit être `swiss`
- Refuse les tournois knockout ou Round Robin

### Exemple de sortie

```
=== TOURNOI ===
Nom: Tournoi E-football Décembre 2025
ID: 1
Format: swiss
Statut: completed

=== ÉTAPE 1: RECALCUL DES STATS ===
Player 1: 1→1W, 2→2L, 0→0D, 3→3pts
Player 4: 3→3W, 0→0L, 0→0D, 9→9pts
...

=== ÉTAPE 2: DISTRIBUTION DES PRIX ===
Distribution: {"1st":20,"2nd":10,"3rd":2}
  Player 4 (Rang 1): Déjà reçu 20.00 pièces - IGNORÉ
  Player 7 (Rang 2): Déjà reçu 10.00 pièces - IGNORÉ
...

=== ÉTAPE 3: STATS GLOBALES ===
  ✅ Player 1
  ✅ Player 4
...

✅ CORRECTION TERMINÉE
```

---

## 📝 Notes pour les futurs tournois

1. **TOUJOURS vérifier que tous les matchs sont completed avant de clôturer**
2. **Vérifier le prize_distribution JSON avant de démarrer le tournoi**
3. **S'assurer que l'organisateur a les fonds nécessaires**
4. **Monitorer les logs Laravel pendant completeTournament()**

---

## 📦 Résumé des modifications

**Date du fix:** 2026-01-05

**Fichiers modifiés:**

1. `app/Services/KnockoutFormatService.php`
   - Ligne 193-195: Ajout de `updatePlayerStats()` dans `updateMatchResult()`
   - Ligne 282-288: Ajout de revert/update stats dans `updateMatchScore()`
   - Ligne 354-404: Nouvelles méthodes `updatePlayerStats()` et `revertPlayerStats()`

2. `app/Services/SwissFormatService.php`
   - Déjà fonctionnel (méthodes existantes lignes 446-469 et 418-441)

3. `app/Services/UserStatsService.php`
   - Ligne 41-49: Support des `final_rank` null pour tournois annulés

4. `app/Console/Commands/FixKnockoutTournamentStats.php`
   - Nouvelle commande de correction pour tournois knockout

5. `app/Console/Commands/FixSwissTournamentStats.php`
   - Nouvelle commande de correction pour tournois Swiss
