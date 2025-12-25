# Guide: Système de Gestion des Deadlines de Matchs

## Vue d'ensemble

Le système `CheckMatchDeadlinesJob` s'exécute **toutes les 15 minutes** pour vérifier les matchs dont la deadline est dépassée et appliquer automatiquement les règles appropriées.

---

## Comportement selon les Scénarios

### 1️⃣ Un Seul Joueur Soumet à Temps ✅

**Résultat:** Le joueur qui a soumis **GAGNE par forfait**

```
Exemple:
- Joueur A: Soumet son résultat ✅
- Joueur B: Ne soumet rien ❌
- Deadline expire

→ Joueur A gagne avec son score
→ Joueur B obtient un score de 0 (forfait)
→ Match marqué comme 'completed'
→ Le gagnant avance au prochain round (Knockout) ou gagne des points (Swiss)
```

**Logs:**
```
Match {id} - One submission → Winner by forfeit: User {winnerId}
```

---

### 2️⃣ Aucun Joueur ne Soumet (Format Suisse) ⚖️

**Résultat:** Match nul 0-0

```
Exemple:
- Joueur A: Ne soumet rien ❌
- Joueur B: Ne soumet rien ❌
- Deadline expire
- Tournoi: Format Swiss

→ Match nul avec score 0-0
→ Chaque joueur reçoit 1 point (draw)
→ Match marqué comme 'completed'
→ Les deux joueurs continuent dans le tournoi
```

**Logs:**
```
Match {id} (Swiss) - No submissions → Draw 0-0
```

---

### 3️⃣ Aucun Joueur ne Soumet (Format Knockout - Hors Finale) 🚫

**Résultat:** Les DEUX joueurs sont **DISQUALIFIÉS**

```
Exemple:
- Joueur A: Ne soumet rien ❌
- Joueur B: Ne soumet rien ❌
- Deadline expire
- Tournoi: Format Knockout (Round 1, Semi-finale, etc.)

→ Les deux joueurs sont disqualifiés
→ Leurs registrations passent en statut 'disqualified'
→ Match marqué comme 'expired'
→ Les rounds suivants auront un "bye" (passage automatique)
```

**Modifications en DB:**
```php
TournamentRegistration:
  - status: 'disqualified'
  - eliminated: true
  - eliminated_round: "Semi-Finals" (ou nom du round)
  - eliminated_at: timestamp

TournamentMatch:
  - status: 'expired'
  - winner_id: null
  - player1_score: 0
  - player2_score: 0
  - completed_at: timestamp
```

**Logs:**
```
Match {id} (Knockout) - No submissions → Both players disqualified
```

---

### 4️⃣ Aucun Joueur ne Soumet (Finale) ⚠️

**Résultat:** Cas spécial - Intervention manuelle requise

```
Exemple:
- Match de FINALE
- Joueur A: Ne soumet rien ❌
- Joueur B: Ne soumet rien ❌
- Deadline expire

→ Match marqué comme 'expired'
→ Log CRITIQUE envoyé
→ Intervention manuelle de l'organisateur/admin nécessaire
```

**Logs:**
```
CRITICAL: FINAL Match {id} expired with no submissions! Tournament {tournament_id} may need manual intervention.
```

**TODO futur:**
- Implémenter prolongation automatique de 24h
- Envoyer email urgent aux deux finalistes
- Si toujours aucune soumission → Annuler le tournoi

---

## Flux Technique

### Fichier Modifié
**app/Jobs/CheckMatchDeadlinesJob.php**

### Méthodes Principales

#### 1. `handle()`
Point d'entrée du job. Récupère tous les matchs expirés.

```php
$expiredMatches = TournamentMatch::whereNotNull('deadline_at')
    ->where('deadline_at', '<=', now())
    ->whereNotIn('status', ['completed', 'disputed', 'expired'])
    ->with(['tournament', 'round', 'matchResults'])
    ->get();
```

#### 2. `handleExpiredMatch(TournamentMatch $match)`
Analyse le nombre de soumissions et route vers le bon traitement.

```php
$submissionsCount = $match->matchResults->count();

if ($submissionsCount === 0) {
    $this->handleNoSubmissions($match);
} elseif ($submissionsCount === 1) {
    $this->handleOneSubmission($match);
}
```

#### 3. `handleNoSubmissions(TournamentMatch $match)`
Gère le cas où aucun joueur n'a soumis.

Décisions basées sur:
- Format du tournoi (swiss vs knockout)
- Si c'est la finale ou non

#### 4. `handleOneSubmission(TournamentMatch $match)`
Le joueur qui a soumis gagne par forfait.

```php
$winnerId = $submission->submitted_by;

if ($winnerId === $match->player1_id) {
    $player1Score = $submission->own_score;
    $player2Score = 0; // Forfait
} else {
    $player1Score = 0; // Forfait
    $player2Score = $submission->own_score;
}

$this->updateMatchResultViaService($match, $player1Score, $player2Score);
```

#### 5. `disqualifyBothPlayers(TournamentMatch $match)`
Disqualifie les deux joueurs (Knockout uniquement).

```php
TournamentRegistration::where('tournament_id', $match->tournament_id)
    ->whereIn('user_id', [$match->player1_id, $match->player2_id])
    ->update([
        'status' => 'disqualified',
        'eliminated' => true,
        'eliminated_round' => $roundName,
        'eliminated_at' => now(),
    ]);
```

#### 6. `isFinalMatch(TournamentMatch $match)`
Détecte si un match est une finale.

```php
$roundName = strtolower($match->round->round_name ?? '');
return str_contains($roundName, 'final') && !str_contains($roundName, 'semi');
```

---

## Planification du Job

**Fichier:** routes/console.php

```php
Schedule::job(new CheckMatchDeadlinesJob)->everyFifteenMinutes();
```

**Fréquence:** Toutes les 15 minutes (00:00, 00:15, 00:30, 00:45, etc.)

**Démarrage en dev:**
```bash
php artisan schedule:work
```

**Configuration en prod:**
```bash
* * * * * cd /chemin/projet && php artisan schedule:run >> /dev/null 2>&1
```

---

## Tableau Récapitulatif

| Soumissions | Format Swiss | Format Knockout (Normal) | Format Knockout (Finale) |
|-------------|--------------|--------------------------|--------------------------|
| **0** | Match nul 0-0<br>1 point chacun | ⛔ Les deux disqualifiés<br>Pas de gagnant | ⚠️ Log critique<br>Intervention manuelle |
| **1** | 🏆 Gagne par forfait<br>1 point vs 0 | 🏆 Gagne par forfait<br>Avance au prochain round | 🏆 Gagne par forfait<br>Champion du tournoi |
| **2 (scores correspondent)** | ✅ Auto-validé | ✅ Auto-validé | ✅ Auto-validé |
| **2 (scores différents)** | 🔴 Disputé<br>Modérateur requis | 🔴 Disputé<br>Modérateur requis | 🔴 Disputé<br>Modérateur requis |

---

## Impact sur les Rounds Suivants (Knockout)

### Scénario: Disqualification de 2 Joueurs

```
Tournoi à 8 joueurs - Knockout

Round 1 (Quart de finale):
  Match 1: A vs B → A gagne
  Match 2: C vs D → C gagne
  Match 3: E vs F → Aucun ne soumet → Les deux disqualifiés ⛔
  Match 4: G vs H → G gagne

Round 2 (Demi-finale):
  Match 5: A vs C
  Match 6: Bye (car E et F disqualifiés) → G passe automatiquement

Finale:
  Match 7: Gagnant de Match 5 vs G
```

---

## Logs et Monitoring

### Niveaux de Log

**INFO** - Opérations normales:
```
Match {id} (Swiss) - No submissions → Draw 0-0
Match {id} - One submission → Winner by forfeit: User {userId}
```

**WARNING** - Situations anormales mais gérées:
```
Match {id} (Knockout) - No submissions → Both players disqualified
Match {id} expired with {count} submissions (unexpected)
```

**ERROR** - Échecs de traitement:
```
Failed to handle expired match {id}: {error message}
```

**CRITICAL** - Nécessite intervention:
```
FINAL Match {id} expired with no submissions! Tournament {id} may need manual intervention.
```

---

## Tests Recommandés

### Test 1: Un joueur soumet à temps
```php
// Créer un match avec deadline dans le passé
// Faire soumettre seulement player1
// Exécuter le job
// Vérifier que player1 a gagné par forfait
```

### Test 2: Format Suisse - Aucune soumission
```php
// Créer un match Swiss avec deadline expirée
// Ne pas soumettre de résultats
// Exécuter le job
// Vérifier: match complété, score 0-0, 1 point chacun
```

### Test 3: Format Knockout - Aucune soumission
```php
// Créer un match Knockout (non-finale) avec deadline expirée
// Ne pas soumettre de résultats
// Exécuter le job
// Vérifier: match expired, les deux joueurs disqualifiés
```

### Test 4: Finale sans soumissions
```php
// Créer un match finale avec deadline expirée
// Ne pas soumettre de résultats
// Exécuter le job
// Vérifier: log critique généré
```

---

## Améliorations Futures

### 1. Prolongation Automatique pour Finales
```php
private function handleFinalNoSubmission(TournamentMatch $match): void
{
    // Vérifier si c'est la première expiration
    if (!$match->deadline_extended) {
        // Prolonger de 24h
        $match->update([
            'deadline_at' => now()->addHours(24),
            'deadline_extended' => true,
        ]);

        // Envoyer emails urgents aux finalistes
        Mail::to($match->player1)->send(new FinalDeadlineExtendedMail($match));
        Mail::to($match->player2)->send(new FinalDeadlineExtendedMail($match));

        return;
    }

    // Si toujours rien après prolongation → Annuler tournoi
    $this->cancelTournamentDueToFinalExpiry($match);
}
```

### 2. Notifications Push
Envoyer des notifications push aux joueurs 1h avant la deadline.

### 3. Système d'Avertissements
Avant disqualification, envoyer 2-3 rappels par email/SMS.

---

## Questions Fréquentes

**Q: Que se passe-t-il si un joueur soumet après la deadline?**
R: La soumission est toujours enregistrée dans `match_results`, mais le job aura déjà traité le match. Il faudra une intervention manuelle du modérateur.

**Q: Un joueur peut-il contester sa disqualification?**
R: Oui, il devra contacter un modérateur qui pourra réouvrir le match manuellement.

**Q: Combien de temps après la deadline le job s'exécute-t-il?**
R: Maximum 15 minutes (intervalle du job). Par exemple, si deadline = 14:00, le job s'exécutera à 14:00 ou 14:15.

**Q: Les joueurs disqualifiés sont-ils remboursés?**
R: Non, la disqualification pour inactivité n'entraîne pas de remboursement des frais d'inscription.

---

## Support

Pour toute question sur ce système, contactez l'équipe de développement.
