# Système Complet de Gestion des Deadlines de Matchs

## Vue d'Ensemble

Le système de gestion des deadlines a été entièrement implémenté avec les 3 améliorations demandées:

1. ✅ **Prolongation automatique des finales** (24h supplémentaires)
2. ✅ **Email d'avertissement avant disqualification**
3. ✅ **Notifications push 1h avant deadline**

---

## Architecture du Système

### Jobs Planifiés

| Job | Fréquence | Fonction |
|-----|-----------|----------|
| **CheckMatchDeadlinesJob** | Toutes les 15 min | Gère les matchs expirés selon les règles |
| **SendMatchDeadlineWarningsJob** | Toutes les 15 min | Envoie avertissements 1h avant deadline |

---

## 1. Prolongation Automatique des Finales ⏰

### Fonctionnement

Lorsqu'une finale expire sans aucune soumission:

**Première Expiration:**
```
CheckMatchDeadlinesJob détecte finale expirée
└─> Vérifier deadline_extended = false
    └─> Prolonger de 24h
    └─> Marquer deadline_extended = true
    └─> Envoyer emails aux 2 finalistes + organisateur
    └─> Log: "FINAL Match {id} deadline extended by 24h"
```

**Deuxième Expiration (après prolongation):**
```
CheckMatchDeadlinesJob détecte finale expirée ENCORE
└─> Vérifier deadline_extended = true
    └─> Annuler le tournoi
    └─> Disqualifier les 2 finalistes
    └─> Marquer tournoi status = 'cancelled'
    └─> Log CRITICAL: "Tournament {id} CANCELLED"
```

### Fichiers Modifiés

#### app/Jobs/CheckMatchDeadlinesJob.php
```php
private function handleFinalNoSubmission(TournamentMatch $match): void
{
    if (!$match->deadline_extended) {
        // Première expiration → Prolonger de 24h
        $this->extendFinalDeadline($match);
    } else {
        // Deuxième expiration → Annuler le tournoi
        $this->cancelTournamentDueToFinalExpiry($match);
    }
}

private function extendFinalDeadline(TournamentMatch $match): void
{
    $newDeadline = now()->addHours(24);

    $match->update([
        'deadline_at' => $newDeadline,
        'deadline_extended' => true,
    ]);

    // Emails aux finalistes + organisateur
    Mail::to($match->player1)->send(
        new FinalMatchDeadlineExtendedMail(...)
    );
    // ...
}
```

### Email de Prolongation

**Fichier:** `app/Mail/FinalMatchDeadlineExtendedMail.php`
**Template:** `resources/views/emails/matches/final-deadline-extended.blade.php`

**Contenu:**
- 🚨 Alerte urgente en rouge
- Tableau comparatif ancienne vs nouvelle deadline
- Avertissement sur les conséquences
- CTA "Soumettre le Résultat MAINTENANT"

---

## 2. Emails d'Avertissement 📧

### Fonctionnement

Le job `SendMatchDeadlineWarningsJob` s'exécute toutes les 15 minutes et:

1. Cherche les matchs dont la deadline est dans **55 à 65 minutes** (fenêtre de 10 min)
2. Vérifie si `deadline_warning_sent_at` est `null` (pas déjà envoyé)
3. Pour chaque match trouvé:
   - Vérifie quels joueurs ont déjà soumis
   - Envoie email **UNIQUEMENT** aux joueurs n'ayant PAS soumis
   - Marque `deadline_warning_sent_at = now()`

### Code du Job

#### app/Jobs/SendMatchDeadlineWarningsJob.php
```php
public function handle(): void
{
    $upcomingMatches = TournamentMatch::whereNotNull('deadline_at')
        ->whereNull('deadline_warning_sent_at')
        ->where('deadline_at', '>', now()->addMinutes(55))
        ->where('deadline_at', '<=', now()->addMinutes(65))
        ->whereNotIn('status', ['completed', 'disputed', 'expired'])
        ->with(['tournament', 'round', 'player1', 'player2', 'matchResults'])
        ->get();

    foreach ($upcomingMatches as $match) {
        $this->sendWarningEmails($match);
    }
}

private function sendWarningEmails(TournamentMatch $match): void
{
    $hoursRemaining = 1;

    // Vérifier quels joueurs ont soumis
    $player1Submitted = $match->matchResults
        ->where('submitted_by', $match->player1_id)->isNotEmpty();
    $player2Submitted = $match->matchResults
        ->where('submitted_by', $match->player2_id)->isNotEmpty();

    // Email + Notification push seulement si PAS soumis
    if (!$player1Submitted) {
        Mail::to($match->player1)->send(
            new MatchDeadlineWarningMail(...)
        );
        $match->player1->notify(
            new MatchDeadlineWarningNotification(...)
        );
    }
    // Même logique pour player2

    $match->update(['deadline_warning_sent_at' => now()]);
}
```

### Email d'Avertissement

**Fichier:** `app/Mail/MatchDeadlineWarningMail.php`
**Template:** `resources/views/emails/matches/deadline-warning.blade.php`

**Contenu:**
- ⏰ Header orange avec countdown
- Informations du match (tournoi, round, adversaire)
- Deadline en gros et gras
- Instructions étape par étape
- Conséquences selon le format (Swiss vs Knockout)
- CTA "Soumettre le Résultat"

**Personnalisation selon Format:**
```blade
@if($match->tournament->format === 'swiss')
    <li>Le match sera compté comme un match nul (0-0)</li>
    <li>Vous recevrez 1 point au lieu de 3</li>
@else
    <li>Vous risquez d'être disqualifié</li>
    <li>Votre adversaire pourrait gagner par forfait</li>
@endif
```

---

## 3. Notifications Push 📱

### Architecture

Le système utilise les **Notifications Laravel** avec 2 canaux:

1. **Database** - Stockage dans la table `notifications`
2. **Broadcast** - Envoi temps réel via websocket (Laravel Echo + Pusher/Soketi)

### Implémentation

#### app/Notifications/MatchDeadlineWarningNotification.php

```php
class MatchDeadlineWarningNotification extends Notification implements ShouldQueue
{
    public function __construct(
        public TournamentMatch $match,
        public int $hoursRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'match_deadline_warning',
            'match_id' => $this->match->id,
            'tournament_name' => $this->match->tournament->name,
            'hours_remaining' => $this->hoursRemaining,
            'deadline_at' => $this->match->deadline_at->toIso8601String(),
            'message' => "Votre match expire dans {$this->hoursRemaining} heure(s)!",
            'action_url' => "/matches/{$this->match->id}",
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'match_deadline_warning',
            'match_id' => $this->match->id,
            'tournament_name' => $this->match->tournament->name,
            'hours_remaining' => $this->hoursRemaining,
            'message' => "⏰ Votre match expire dans {$this->hoursRemaining}h!",
        ];
    }
}
```

### Intégration Frontend

#### 1. Configuration Laravel Echo (Frontend)

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.PUSHER_APP_KEY,
    cluster: process.env.PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
});
```

#### 2. Écouter les Notifications (React/Vue)

```javascript
// React Example
useEffect(() => {
    const userId = getCurrentUser().id;

    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            if (notification.type === 'match_deadline_warning') {
                showToast({
                    type: 'warning',
                    title: 'Deadline Approche!',
                    message: notification.message,
                    action: () => navigate(notification.action_url)
                });
            }
        });

    return () => {
        window.Echo.leave(`App.Models.User.${userId}`);
    };
}, []);
```

#### 3. Récupérer les Notifications Stockées

**Endpoint:** `GET /api/notifications`

```javascript
// Récupérer les notifications non lues
fetch('/api/notifications?unread=true')
    .then(res => res.json())
    .then(data => {
        setNotifications(data);
    });

// Marquer comme lue
fetch(`/api/notifications/${notificationId}/read`, { method: 'POST' });
```

### Extension Future: Firebase Cloud Messaging (FCM)

Pour ajouter le support des notifications mobiles:

```php
// Dans via()
public function via(object $notifiable): array
{
    return ['database', 'broadcast', 'fcm'];
}

// Ajouter toFcm()
public function toFcm(object $notifiable): array
{
    return [
        'title' => '⏰ Deadline de Match',
        'body' => "Votre match expire dans {$this->hoursRemaining}h!",
        'click_action' => "MATCH_{$this->match->id}",
    ];
}
```

---

## Nouvelle Structure de Base de Données

### Migration: `add_deadline_extended_to_matches_table`

```php
Schema::table('matches', function (Blueprint $table) {
    $table->boolean('deadline_extended')->default(false)->after('deadline_at');
    $table->timestamp('deadline_warning_sent_at')->nullable()->after('deadline_extended');
});
```

### Colonnes Ajoutées

| Colonne | Type | Usage |
|---------|------|-------|
| `deadline_extended` | boolean | `true` si finale déjà prolongée une fois |
| `deadline_warning_sent_at` | timestamp | Date d'envoi de l'avertissement 1h avant |

---

## Flux Complet: Timeline d'un Match

```
T-0:      Match créé avec deadline à T+24h
T+22h:    RIEN (match en cours)
T+23h:    SendMatchDeadlineWarningsJob détecte match
          └─> Envoie email + notification push aux joueurs n'ayant pas soumis
          └─> deadline_warning_sent_at = now()
T+23h30:  Joueur A soumet son résultat
T+24h:    CheckMatchDeadlinesJob s'exécute
          └─> 1 soumission détectée
          └─> Joueur A gagne par forfait (son score vs 0)
          └─> Joueur B perd par forfait
```

**Cas Finale Sans Soumissions:**
```
T+24h:    CheckMatchDeadlinesJob (FINALE)
          └─> Aucune soumission
          └─> deadline_extended = false → PROLONGER
          └─> Nouvelle deadline: T+48h
          └─> Emails urgents aux finalistes + organisateur
T+47h:    Avertissement envoyé aux 2 joueurs (si toujours rien)
T+48h:    CheckMatchDeadlinesJob (FINALE - 2ème fois)
          └─> Aucune soumission
          └─> deadline_extended = true → ANNULER TOURNOI
          └─> Les 2 finalistes disqualifiés
          └─> Tournament status = 'cancelled'
```

---

## Configuration du Scheduler

### routes/console.php

```php
use App\Jobs\CheckMatchDeadlinesJob;
use App\Jobs\SendMatchDeadlineWarningsJob;

Schedule::job(new CheckMatchDeadlinesJob)->everyFifteenMinutes();
Schedule::job(new SendMatchDeadlineWarningsJob)->everyFifteenMinutes();
```

### Démarrage du Scheduler

**Développement:**
```bash
php artisan schedule:work
```

**Production (Crontab):**
```bash
* * * * * cd /chemin/projet && php artisan schedule:run >> /dev/null 2>&1
```

**Docker:**
```bash
docker exec -d tourno_app php artisan schedule:work
```

---

## Tests Recommandés

### Test 1: Avertissement 1h Avant

```php
/** @test */
public function it_sends_warning_email_1h_before_deadline()
{
    $match = TournamentMatch::factory()->create([
        'deadline_at' => now()->addHour(),
        'deadline_warning_sent_at' => null,
    ]);

    $this->artisan('schedule:run');

    // Vérifier email envoyé
    Mail::assertSent(MatchDeadlineWarningMail::class);

    // Vérifier notification créée
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $match->player1_id,
        'type' => MatchDeadlineWarningNotification::class,
    ]);

    // Vérifier timestamp mis à jour
    $this->assertNotNull($match->fresh()->deadline_warning_sent_at);
}
```

### Test 2: Prolongation de Finale

```php
/** @test */
public function it_extends_final_deadline_on_first_expiry()
{
    $final = TournamentMatch::factory()->final()->create([
        'deadline_at' => now()->subMinute(),
        'deadline_extended' => false,
    ]);

    (new CheckMatchDeadlinesJob)->handle();

    $final = $final->fresh();

    $this->assertTrue($final->deadline_extended);
    $this->assertEquals(
        now()->addHours(24)->format('Y-m-d H:i'),
        $final->deadline_at->format('Y-m-d H:i')
    );

    Mail::assertSent(FinalMatchDeadlineExtendedMail::class, 3); // 2 joueurs + organisateur
}
```

### Test 3: Annulation Après 2ème Expiration

```php
/** @test */
public function it_cancels_tournament_on_second_final_expiry()
{
    $final = TournamentMatch::factory()->final()->create([
        'deadline_at' => now()->subMinute(),
        'deadline_extended' => true, // Déjà prolongé
    ]);

    (new CheckMatchDeadlinesJob)->handle();

    $this->assertEquals('expired', $final->fresh()->status);
    $this->assertEquals('cancelled', $final->tournament->fresh()->status);

    // Vérifier disqualification
    $this->assertDatabaseHas('tournament_registrations', [
        'user_id' => $final->player1_id,
        'status' => 'disqualified',
    ]);
}
```

---

## Logs et Monitoring

### Niveaux de Log

**INFO** - Opérations normales:
```
Deadline warning (email + push) sent to Player 1 (User {id}) for match {id}
FINAL Match {id} deadline extended by 24h. New deadline: {date}
```

**WARNING** - Situations inhabituelles:
```
Match {id} (Knockout) - No submissions → Both players disqualified
```

**CRITICAL** - Nécessite attention:
```
Tournament {id} CANCELLED - Final match {id} expired twice without submissions
```

---

## Fichiers Créés/Modifiés

### Créés ✨

| Fichier | Type | Description |
|---------|------|-------------|
| `app/Mail/MatchDeadlineWarningMail.php` | Mailable | Email d'avertissement 1h avant |
| `app/Mail/FinalMatchDeadlineExtendedMail.php` | Mailable | Email de prolongation finale |
| `app/Jobs/SendMatchDeadlineWarningsJob.php` | Job | Envoi avertissements planifié |
| `app/Notifications/MatchDeadlineWarningNotification.php` | Notification | Notification push |
| `resources/views/emails/matches/deadline-warning.blade.php` | Template | Template email avertissement |
| `resources/views/emails/matches/final-deadline-extended.blade.php` | Template | Template email prolongation |
| `database/migrations/..._add_deadline_extended_to_matches_table.php` | Migration | Nouvelles colonnes |

### Modifiés 🔧

| Fichier | Modifications |
|---------|--------------|
| `app/Jobs/CheckMatchDeadlinesJob.php` | Prolongation automatique finale |
| `app/Models/TournamentMatch.php` | Ajout fillable + casts pour nouvelles colonnes |
| `routes/console.php` | Ajout SendMatchDeadlineWarningsJob au scheduler |

---

## Support et Extension

### Ajouter un Nouveau Canal de Notification

1. Créer le canal (exemple: SMS via Twilio)
```php
// app/Notifications/Channels/SmsChannel.php
public function send($notifiable, Notification $notification)
{
    $message = $notification->toSms($notifiable);
    // Logique Twilio
}
```

2. Ajouter dans `via()`
```php
public function via(object $notifiable): array
{
    return ['database', 'broadcast', 'sms'];
}
```

3. Implémenter `toSms()`
```php
public function toSms(object $notifiable): string
{
    return "Match deadline in {$this->hoursRemaining}h! Submit your result.";
}
```

---

## FAQ

**Q: Les notifications push nécessitent-elles une configuration supplémentaire?**
R: Oui, vous devez configurer Laravel Broadcasting (Pusher/Soketi) et Laravel Echo côté frontend.

**Q: Que se passe-t-il si un joueur soumet APRÈS avoir reçu l'avertissement?**
R: Le système vérifie toujours l'état actuel au moment de l'expiration. Si un joueur a soumis entre temps, il gagnera par forfait.

**Q: Peut-on personnaliser le délai d'avertissement (autre que 1h)?**
R: Oui, modifiez la fenêtre de temps dans `SendMatchDeadlineWarningsJob`:
```php
->where('deadline_at', '>', now()->addMinutes(115)) // 2h avant
->where('deadline_at', '<=', now()->addMinutes(125))
```

**Q: Les emails sont-ils envoyés en synchrone ou asynchrone?**
R: Asynchrone via la queue Laravel (`implements ShouldQueue`).

---

## Conclusion

Le système complet de gestion des deadlines est maintenant opérationnel avec:

✅ Prolongation automatique des finales (24h)
✅ Emails d'avertissement 1h avant deadline
✅ Notifications push temps réel
✅ Gestion intelligente des soumissions partielles
✅ Logs détaillés pour monitoring
✅ Tests recommandés fournis

Le système est prêt pour la production!
