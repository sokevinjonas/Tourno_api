# 📖 User Stories - Mobile League Manager (MLM)

**Version** : 1.0
**Date** : Décembre 2024

---

## Table des Matières

1. [Introduction](#introduction)
2. [Personas](#personas)
3. [Parcours Utilisateur - Tournoi K.O.](#parcours-utilisateur---tournoi-ko)
4. [Parcours Utilisateur - Ligue](#parcours-utilisateur---ligue)
5. [Scénarios Détaillés](#scénarios-détaillés)
6. [User Stories par Fonctionnalité](#user-stories-par-fonctionnalité)
7. [Flux d'Erreurs et Cas Limites](#flux-derreurs-et-cas-limites)

---

## Introduction

Ce document décrit les **parcours utilisateurs** (user journeys) et **user stories** du projet Mobile League Manager. Chaque scénario est détaillé étape par étape pour faciliter la compréhension des besoins et la conception de l'interface.

---

## Personas

### 🎮 Persona 1 : **Karim - Le Joueur Occasionnel**

**Profil** :
- 22 ans, étudiant
- Joue à E-football avec ses amis pendant les pauses
- Pas très compétitif, veut juste s'amuser
- Utilise principalement son smartphone

**Besoins** :
- Interface simple et rapide
- Pas de configuration compliquée
- Pouvoir rejoindre un tournoi en 2 clics
- Recevoir des notifications claires

**Quote** : *"Je veux juste jouer avec mes potes sans me prendre la tête"*

---

### 🏆 Persona 2 : **Amadou - L'Organisateur de Clan**

**Profil** :
- 28 ans, manager de communauté gaming
- Organise des tournois réguliers (chaque week-end)
- 50+ membres dans son clan Discord
- Cherche à automatiser la gestion

**Besoins** :
- Créer des tournois rapidement
- Système de validation automatique des scores
- Pouvoir arbitrer les litiges facilement
- Statistiques et historique

**Quote** : *"J'ai besoin d'un outil qui me fait gagner du temps et réduit les disputes"*

---

### ⚡ Persona 3 : **Sarah - La Joueuse Compétitive**

**Profil** :
- 25 ans, joueuse semi-professionnelle
- Participe à 5-10 tournois par mois
- Très attachée à son classement
- Active sur les réseaux sociaux gaming

**Besoins** :
- Système de classement fiable (ELO)
- Preuves de résultats (captures d'écran)
- Historique détaillé de ses performances
- Tournois de qualité avec bons joueurs

**Quote** : *"Mon MLM Rank représente mon niveau, il doit être juste et transparent"*

---

### 🛡️ Persona 4 : **Moussa - L'Arbitre Neutre**

**Profil** :
- 35 ans, ancien joueur devenu arbitre
- Respecté dans la communauté
- Aide plusieurs organisateurs
- Impartial et rigoureux

**Besoins** :
- Accès rapide aux preuves (captures d'écran)
- Interface claire pour trancher les litiges
- Historique des décisions
- Communication avec les joueurs

**Quote** : *"Je dois pouvoir trancher rapidement avec toutes les informations sous les yeux"*

---

## Parcours Utilisateur - Tournoi K.O.

### 🎯 Parcours Complet : **De l'inscription à la victoire**

#### Scénario : **Karim participe à son premier tournoi**

---

### **Étape 1 : Découverte du Tournoi**

**Contexte** : Amadou (l'organisateur) a créé un tournoi et partagé le lien sur WhatsApp.

**Actions de Karim** :

1. **Reçoit le lien** via WhatsApp
   ```
   "🏆 Tournoi E-football ce week-end !
   Inscription : https://mlm.app/t/abc123"
   ```

2. **Clique sur le lien**
   - L'app s'ouvre (ou navigateur mobile)
   - Voit la page du tournoi avec :
     - Nom : "Tournoi Week-end Clan Warriors"
     - Jeu : E-football 2024
     - Format : Élimination directe (8 joueurs)
     - Inscriptions : 5/8 joueurs
     - Date limite : Samedi 15h

3. **Décide de s'inscrire**

**Interface** :
```
┌─────────────────────────────────────┐
│  🏆 Tournoi Week-end Clan Warriors  │
├─────────────────────────────────────┤
│  🎮 Jeu : E-football 2024           │
│  ⚔️  Format : Knockout (8 joueurs)  │
│  👥 Inscriptions : 5/8              │
│  📅 Début : Sam 15h                 │
│                                     │
│  Participants actuels :             │
│  • Amadou ⭐ (organisateur)         │
│  • Sarah 🔥 (MLM: 1450)             │
│  • Youssef (MLM: 1200)              │
│  • Malik (MLM: 1100)                │
│  • Kevin (MLM: 1050)                │
│                                     │
│  [🔓 S'INSCRIRE]                    │
└─────────────────────────────────────┘
```

---

### **Étape 2 : Inscription**

**Actions de Karim** :

1. **Clique sur "S'INSCRIRE"**

2. **Si pas connecté** :
   - Redirection vers page de login/register
   - Création rapide de compte :
     ```
     Username : karim_dls
     Email : karim@email.com
     Password : ••••••••

     [CRÉER MON COMPTE]
     ```

3. **Confirmation d'inscription**
   - Message de succès : "✅ Vous êtes inscrit au tournoi !"
   - Notification : "Vous recevrez une alerte quand le tournoi démarrera"

4. **Retour à la page du tournoi**
   - Karim voit maintenant son nom dans la liste (6/8)

**Notification reçue** (après inscription) :
```
🔔 MLM - Tournoi Week-end
Vous êtes inscrit !
Dès que 8 joueurs seront présents, le tournoi commencera.
```

---

### **Étape 3 : Attente du Démarrage**

**Contexte** : 2 autres joueurs s'inscrivent. Le tournoi est maintenant complet (8/8).

**Notifications automatiques** :

1. **À tous les participants** :
   ```
   🔔 Tournoi complet !
   Les 8 joueurs sont inscrits.
   L'organisateur va bientôt lancer le tournoi.
   ```

2. **À l'organisateur (Amadou)** :
   ```
   ⚡ Votre tournoi est prêt !
   8/8 joueurs inscrits.
   [DÉMARRER LE TOURNOI]
   ```

**Amadou clique sur "Démarrer"** → Le bracket est généré automatiquement.

---

### **Étape 4 : Consultation du Bracket**

**Actions de Karim** :

1. **Reçoit une notification** :
   ```
   🏁 Le tournoi a démarré !
   Votre premier adversaire : Sarah
   Round 1 - Match 3
   Date limite : Dimanche 15h
   ```

2. **Ouvre l'app et va sur la page du tournoi**

3. **Voit le bracket complet** :

**Interface du Bracket** :
```
┌─────────────────────────────────────────────┐
│         🏆 BRACKET - QUARTS DE FINALE       │
├─────────────────────────────────────────────┤
│                                             │
│  Match 1                    Match 5         │
│  Amadou ⭐     ┐           ┌─────┐          │
│         vs     ├─────────> │  ?  │─────┐    │
│  Youssef       ┘           └─────┘     │    │
│                                        │    │
│  Match 2                               │    │
│  Kevin         ┐           ┌─────┐     │    │
│         vs     ├─────────> │  ?  │     │    │
│  Malik         ┘           └─────┘     │    │
│                                        │    │
│  Match 3  [EN COURS 🔴]                │    │
│  Sarah 🔥      ┐           ┌─────┐     │    │
│         vs     ├─────────> │  ?  │────>│ 🏆 │
│  Karim (VOUS)  ┘           └─────┘     │    │
│                                        │    │
│  Match 4                               │    │
│  Moussa        ┐           ┌─────┐     │    │
│         vs     ├─────────> │  ?  │     │    │
│  Fatou         ┘           └─────┘     │    │
│                                        │    │
│  [VOIR MON MATCH]                           │
└─────────────────────────────────────────────┘
```

---

### **Étape 5 : Jouer le Match**

**Actions de Karim** :

1. **Clique sur "VOIR MON MATCH"**

2. **Voit les détails du match** :
   ```
   ┌───────────────────────────────────┐
   │  Match 3 - Quarts de Finale       │
   ├───────────────────────────────────┤
   │                                   │
   │      Sarah 🔥     VS    Karim     │
   │    MLM: 1450           MLM: 1000  │
   │                                   │
   │  📅 Date limite : Dim 15h         │
   │  ⏱️  Temps restant : 23h 45min    │
   │                                   │
   │  💬 Chat avec Sarah :             │
   │  Sarah : "Dispo ce soir 20h ?"    │
   │                                   │
   │  [ENVOYER MESSAGE]                │
   │  [DÉCLARER LE RÉSULTAT]           │
   └───────────────────────────────────┘
   ```

3. **Envoie un message** :
   ```
   Karim : "Ok pour 20h ✅"
   ```

4. **Le soir à 20h** : Karim et Sarah jouent leur match sur E-football
   - Résultat : Sarah 3 - 1 Karim
   - Sarah gagne

---

### **Étape 6 : Déclaration du Score**

**Actions de Karim** (après le match) :

1. **Prend une capture d'écran** du résultat final sur E-football

2. **Retourne sur l'app MLM**

3. **Clique sur "DÉCLARER LE RÉSULTAT"**

4. **Remplit le formulaire** :
   ```
   ┌───────────────────────────────────┐
   │  📊 DÉCLARER LE SCORE             │
   ├───────────────────────────────────┤
   │                                   │
   │  Score de Sarah :  [3] ⚽         │
   │  Score de Karim :  [1] ⚽         │
   │                                   │
   │  📸 Preuve (capture d'écran) :    │
   │  [📁 CHOISIR UNE IMAGE]           │
   │                                   │
   │  ⚠️  Assurez-vous que les noms    │
   │     des joueurs sont visibles !   │
   │                                   │
   │  [ANNULER]  [VALIDER ✅]          │
   └───────────────────────────────────┘
   ```

5. **Upload la capture d'écran**

6. **Clique sur "VALIDER"**

7. **Reçoit une confirmation** :
   ```
   ✅ Score déclaré !
   En attente de la confirmation de Sarah.
   ```

**Notification envoyée à Sarah** :
```
🔔 Karim a déclaré un score
Match 3 : Sarah 3-1 Karim
Confirmez le résultat.
```

---

### **Étape 7 : Validation Automatique**

**Actions de Sarah** (quelques minutes plus tard) :

1. **Déclare aussi le score** : Sarah 3 - 1 Karim (identique)

2. **Le système compare les 2 déclarations** :
   - ✅ Scores identiques
   - ✅ Validation automatique

**Notifications envoyées** :

**À Karim et Sarah** :
```
✅ Match validé !
Sarah 3-1 Karim
Sarah est qualifiée pour les demi-finales.
```

**À Karim** :
```
❌ Vous avez été éliminé
Merci d'avoir participé !
Classement final : 5-8ème place
MLM Rank : 1000 → 995 (-5 points)
```

---

### **Étape 8 : Consultation des Résultats**

**Actions de Karim** :

1. **Consulte le bracket mis à jour** :
   ```
   Match 3 ✅
   Sarah 3-1 Karim
   → Sarah qualifiée pour le Match 5
   ```

2. **Consulte son profil** :
   - Total tournois : 1
   - Victoires : 0
   - Défaites : 1
   - MLM Rank : 995

3. **Consulte le chat du tournoi** :
   - Peut continuer à suivre le tournoi
   - Voir qui remporte la finale

---

### **Étape 9 : Fin du Tournoi**

**Contexte** : Sarah remporte la finale contre Amadou (2-1).

**Notification finale à tous** :
```
🏆 TOURNOI TERMINÉ !
Championne : Sarah 🥇
Finaliste : Amadou 🥈

Classement final :
1. Sarah (+45 pts MLM)
2. Amadou (+20 pts MLM)
3-4. Youssef, Kevin
5-8. Karim, Malik, Moussa, Fatou

Merci à tous les participants !
```

---

## Parcours Utilisateur - Ligue

### 🏆 Parcours : **Amadou organise une ligue mensuelle**

---

### **Étape 1 : Création de la Ligue**

**Contexte** : Amadou veut organiser une ligue de 6 joueurs qui s'affrontent tous en matches aller simple.

**Actions d'Amadou** :

1. **Ouvre l'app MLM**

2. **Clique sur "CRÉER UN TOURNOI"**

3. **Remplit le formulaire** :
   ```
   ┌─────────────────────────────────────┐
   │  ➕ CRÉER UN TOURNOI                │
   ├─────────────────────────────────────┤
   │                                     │
   │  Nom du tournoi :                   │
   │  [Ligue Mensuelle Janvier]          │
   │                                     │
   │  Jeu :                              │
   │  [E-football 2024 ▼]                │
   │                                     │
   │  Type de compétition :              │
   │  ( ) Élimination directe            │
   │  (•) Ligue (Round Robin)            │
   │                                     │
   │  Nombre de joueurs :                │
   │  [6 ▼]                              │
   │                                     │
   │  Format :                           │
   │  (•) Aller simple                   │
   │  ( ) Aller-retour                   │
   │                                     │
   │  Matchs nuls autorisés :            │
   │  [✓] Oui                            │
   │                                     │
   │  Délai par journée :                │
   │  [48] heures                        │
   │                                     │
   │  Description :                      │
   │  [Ligue réservée aux membres        │
   │   actifs du clan Warriors]          │
   │                                     │
   │  Visibilité :                       │
   │  ( ) Public                         │
   │  (•) Privé (code requis)            │
   │                                     │
   │  [ANNULER]  [CRÉER 🎯]              │
   └─────────────────────────────────────┘
   ```

4. **Clique sur "CRÉER"**

5. **Reçoit un code d'invitation** :
   ```
   ✅ Ligue créée !
   Code d'invitation : WARS-JAN-2024
   Lien : https://mlm.app/l/wars-jan-2024

   Partagez ce lien avec vos joueurs.
   ```

---

### **Étape 2 : Inscriptions et Démarrage**

**Actions d'Amadou** :

1. **Partage le lien** sur le Discord du clan

2. **Les 6 joueurs s'inscrivent**

3. **Amadou clique sur "DÉMARRER LA LIGUE"**

4. **Le calendrier est généré automatiquement** :
   ```
   ┌─────────────────────────────────────┐
   │  📅 CALENDRIER - LIGUE JANVIER      │
   ├─────────────────────────────────────┤
   │                                     │
   │  Journée 1 (5-7 Jan)                │
   │  • Match 1 : Amadou vs Sarah        │
   │  • Match 2 : Karim vs Youssef       │
   │  • Match 3 : Malik vs Kevin         │
   │                                     │
   │  Journée 2 (8-10 Jan)               │
   │  • Match 4 : Amadou vs Karim        │
   │  • Match 5 : Sarah vs Malik         │
   │  • Match 6 : Youssef vs Kevin       │
   │                                     │
   │  Journée 3 (11-13 Jan)              │
   │  • Match 7 : Amadou vs Youssef      │
   │  • Match 8 : Sarah vs Kevin         │
   │  • Match 9 : Karim vs Malik         │
   │                                     │
   │  ... (5 journées au total)          │
   │                                     │
   └─────────────────────────────────────┘
   ```

---

### **Étape 3 : Suivi du Classement**

**Actions de Karim** :

1. **Consulte le classement en temps réel** :
   ```
   ┌──────────────────────────────────────────────┐
   │  📊 CLASSEMENT - LIGUE JANVIER               │
   ├──────────────────────────────────────────────┤
   │ Pos │ Joueur   │ J │ V │ N │ D │ Bp │ Bc │Diff│ Pts │
   ├─────┼──────────┼───┼───┼───┼───┼────┼────┼────┼─────┤
   │ 🥇  │ Sarah    │ 3 │ 3 │ 0 │ 0 │ 8  │ 2  │ +6 │  9  │
   │ 🥈  │ Amadou   │ 3 │ 2 │ 1 │ 0 │ 7  │ 3  │ +4 │  7  │
   │ 🥉  │ Youssef  │ 3 │ 2 │ 0 │ 1 │ 6  │ 4  │ +2 │  6  │
   │  4  │ Karim    │ 3 │ 1 │ 1 │ 1 │ 4  │ 4  │  0 │  4  │
   │  5  │ Malik    │ 3 │ 0 │ 1 │ 2 │ 2  │ 6  │ -4 │  1  │
   │  6  │ Kevin    │ 3 │ 0 │ 1 │ 2 │ 3  │ 11 │ -8 │  1  │
   └─────┴──────────┴───┴───┴───┴───┴────┴────┴────┴─────┘

   Légende : J=Joués, V=Victoires, N=Nuls, D=Défaites
            Bp=Buts pour, Bc=Buts contre, Diff=Différence
   ```

2. **Voit ses prochains matchs** :
   ```
   🎮 Vos prochains matchs :

   Journée 4 (14-16 Jan)
   • Karim vs Kevin - [JOUER]

   Journée 5 (17-19 Jan)
   • Karim vs Sarah - [À venir]
   ```

---

### **Étape 4 : Fin de Ligue**

**Contexte** : Tous les matchs sont joués.

**Classement final** :
```
🏆 LIGUE JANVIER - TERMINÉE

1. 🥇 Sarah (15 pts) - Championne !
2. 🥈 Amadou (12 pts)
3. 🥉 Youssef (10 pts)
4. Karim (7 pts)
5. Malik (4 pts)
6. Kevin (2 pts)

Meilleur buteur : Sarah (12 buts)

Points ELO mis à jour !
```

---

## Scénarios Détaillés

### 📍 Scénario 1 : **Litige et Arbitrage**

**Contexte** : Match entre Karim et Malik. Ils déclarent des scores différents.

---

**Étape 1 : Déclarations contradictoires**

**Karim déclare** :
- Karim 2 - 1 Malik
- Upload capture d'écran A

**Malik déclare** (5 minutes plus tard) :
- Karim 1 - 2 Malik
- Upload capture d'écran B

---

**Étape 2 : Détection automatique du litige**

**Le système compare** :
- ❌ Scores différents
- 🚨 **Litige créé**

**Notifications** :

**À Karim et Malik** :
```
⚠️ LITIGE DÉTECTÉ
Vos déclarations ne correspondent pas.
L'organisateur va examiner les preuves.
```

**À Amadou (organisateur)** :
```
🚨 ARBITRAGE REQUIS
Match Karim vs Malik - Round 1
Scores déclarés différents.
[VOIR LE LITIGE]
```

---

**Étape 3 : Interface d'arbitrage**

**Actions d'Amadou** :

1. **Clique sur "VOIR LE LITIGE"**

2. **Voit l'interface d'arbitrage** :
   ```
   ┌──────────────────────────────────────────┐
   │  ⚖️  ARBITRAGE - Match Karim vs Malik    │
   ├──────────────────────────────────────────┤
   │                                          │
   │  📸 Preuve de Karim :                    │
   │  ┌────────────────────┐                  │
   │  │                    │                  │
   │  │  [Capture écran A] │                  │
   │  │  Score : 2-1       │                  │
   │  │                    │                  │
   │  └────────────────────┘                  │
   │  Déclaré le : 5 Jan 20h32               │
   │                                          │
   │  📸 Preuve de Malik :                    │
   │  ┌────────────────────┐                  │
   │  │                    │                  │
   │  │  [Capture écran B] │                  │
   │  │  Score : 1-2       │                  │
   │  │                    │                  │
   │  └────────────────────┘                  │
   │  Déclaré le : 5 Jan 20h37               │
   │                                          │
   │  💬 Chat des joueurs :                   │
   │  Karim : "J'ai gagné 2-1 mec"           │
   │  Malik : "Non c'est moi, regarde !"     │
   │                                          │
   │  ⚖️  VOTRE DÉCISION :                    │
   │  [VALIDER KARIM (2-1)]                  │
   │  [VALIDER MALIK (1-2)]                  │
   │  [ANNULER LE MATCH]                     │
   │  [DEMANDER REPLAY]                      │
   │                                          │
   └──────────────────────────────────────────┘
   ```

3. **Examine les 2 captures d'écran**

4. **Constate** : La capture de Karim montre clairement le score final 2-1, celle de Malik est floue

5. **Clique sur "VALIDER KARIM (2-1)"**

6. **Ajoute une note** :
   ```
   Note d'arbitrage (optionnel) :
   [La capture de Karim est claire, celle de Malik est floue et semble être en cours de match.]

   [CONFIRMER LA DÉCISION]
   ```

---

**Étape 4 : Résolution du litige**

**Notifications** :

**À Karim** :
```
✅ Litige résolu en votre faveur
Score validé : Karim 2-1 Malik
Vous êtes qualifié pour le prochain tour !
```

**À Malik** :
```
❌ Litige résolu
Score validé : Karim 2-1 Malik
Note de l'arbitre : "La capture de Karim est claire..."
Vous êtes éliminé.
```

**Match mis à jour** :
- Status : `completed`
- Score final : Karim 2-1 Malik
- Winner : Karim
- Karim promu au tour suivant

---

### 📍 Scénario 2 : **Forfait par Timeout**

**Contexte** : Sarah ne déclare pas son score dans les délais.

---

**Étape 1 : Un seul joueur déclare**

**Karim déclare** : Karim 3-0 Sarah (après 24h, Sarah n'a pas déclaré)

**Deadline du match** : Dimanche 15h

---

**Étape 2 : Notifications de rappel à Sarah**

**Samedi 15h (24h avant deadline)** :
```
⏰ RAPPEL
Votre match contre Karim
Karim a déclaré le score : 3-0
Confirmez avant Dimanche 15h.
```

**Dimanche 9h (6h avant deadline)** :
```
🚨 URGENT
Plus que 6h pour confirmer le score !
Match Karim vs Sarah
```

**Dimanche 14h (1h avant deadline)** :
```
⚠️ DERNIÈRE HEURE
Le match sera validé automatiquement si vous ne déclarez pas.
```

---

**Étape 3 : Deadline dépassée**

**Dimanche 15h** : Sarah n'a toujours pas déclaré.

**Notification à Amadou (organisateur)** :
```
⏱️ DEADLINE DÉPASSÉE
Match Karim vs Sarah
Seul Karim a déclaré : 3-0
Sarah n'a pas répondu.

Options :
[VALIDER SCORE DE KARIM]
[DÉCLARER SARAH FORFAIT]
[PROLONGER DÉLAI (6h)]
```

**Actions d'Amadou** :

Option 1 : **Valider le score de Karim**
- Match validé : 3-0
- Karim qualifié

Option 2 : **Forfait de Sarah**
- Match annulé
- Victoire automatique de Karim (3-0 par défaut)
- Pénalité ELO pour Sarah (-20 pts)

Option 3 : **Prolonger le délai**
- Nouveau délai : Dimanche 21h
- Sarah reçoit une dernière notification

---

### 📍 Scénario 3 : **Tournoi Annulé**

**Contexte** : Amadou doit annuler le tournoi avant qu'il ne démarre.

---

**Actions d'Amadou** :

1. **Va sur la page du tournoi**

2. **Clique sur "⚙️ GÉRER LE TOURNOI"**

3. **Clique sur "ANNULER LE TOURNOI"**

4. **Confirmation** :
   ```
   ⚠️ ANNULER LE TOURNOI ?

   Cette action est irréversible.
   Tous les participants seront notifiés.

   Raison (optionnel) :
   [Pas assez de disponibilité ce week-end]

   [RETOUR]  [CONFIRMER L'ANNULATION]
   ```

5. **Confirme**

---

**Notifications à tous les participants** :
```
❌ TOURNOI ANNULÉ
"Tournoi Week-end Clan Warriors"

Raison de l'organisateur :
"Pas assez de disponibilité ce week-end"

Aucun point ELO n'a été affecté.
```

---

### 📍 Scénario 4 : **Consultation de l'Historique**

**Contexte** : Sarah veut voir son historique de tournois.

---

**Actions de Sarah** :

1. **Va sur son profil**

2. **Clique sur "HISTORIQUE"**

3. **Voit la liste de tous ses tournois** :
   ```
   ┌─────────────────────────────────────────┐
   │  📊 HISTORIQUE - SARAH                  │
   ├─────────────────────────────────────────┤
   │                                         │
   │  Statistiques globales :                │
   │  MLM Rank : 1450 (+150 ce mois)        │
   │  Tournois joués : 23                    │
   │  Victoires : 8 (35%)                    │
   │  Finaliste : 6 (26%)                    │
   │  Taux de victoire : 65% (matchs)        │
   │                                         │
   │  ──────────────────────────────────     │
   │                                         │
   │  🏆 Tournoi Week-end Clan Warriors      │
   │  5 Jan 2024 - Knockout 8 joueurs        │
   │  🥇 Championne                          │
   │  Matchs : 3V - 0D                       │
   │  ELO : +45 pts (1405 → 1450)           │
   │  [VOIR DÉTAILS]                         │
   │                                         │
   │  🏆 Ligue Décembre                      │
   │  1-31 Déc 2023 - Ligue 10 joueurs       │
   │  🥉 3ème place                          │
   │  Matchs : 6V - 2N - 1D                  │
   │  ELO : +12 pts (1393 → 1405)           │
   │  [VOIR DÉTAILS]                         │
   │                                         │
   │  🏆 Tournoi Noël                        │
   │  25 Déc 2023 - Knockout 16 joueurs      │
   │  🥈 Finaliste                           │
   │  Matchs : 3V - 1D (finale)              │
   │  ELO : +20 pts (1373 → 1393)           │
   │  [VOIR DÉTAILS]                         │
   │                                         │
   │  ... (20 tournois précédents)           │
   │                                         │
   └─────────────────────────────────────────┘
   ```

4. **Clique sur "VOIR DÉTAILS" d'un tournoi** :
   ```
   ┌─────────────────────────────────────────┐
   │  🏆 TOURNOI WEEK-END CLAN WARRIORS      │
   ├─────────────────────────────────────────┤
   │                                         │
   │  📅 5-7 Janvier 2024                    │
   │  🎮 Jeu : E-football 2024               │
   │  ⚔️  Format : Knockout 8 joueurs        │
   │  👤 Organisateur : Amadou               │
   │                                         │
   │  VOS PERFORMANCES :                     │
   │  🏆 Résultat : Championne 🥇            │
   │  📊 MLM : 1405 → 1450 (+45)            │
   │                                         │
   │  VOS MATCHS :                           │
   │  ✅ Quarts  : Sarah 3-1 Karim           │
   │  ✅ Demi    : Sarah 2-0 Youssef         │
   │  ✅ Finale  : Sarah 2-1 Amadou          │
   │                                         │
   │  CLASSEMENT FINAL :                     │
   │  1. Sarah 🥇 (+45 MLM)                  │
   │  2. Amadou 🥈 (+20 MLM)                 │
   │  3-4. Youssef, Kevin                    │
   │  5-8. Karim, Malik, Moussa, Fatou       │
   │                                         │
   │  [VOIR LE BRACKET COMPLET]              │
   │  [PARTAGER SUR RÉSEAUX SOCIAUX]         │
   │                                         │
   └─────────────────────────────────────────┘
   ```

---

## User Stories par Fonctionnalité

### 🔐 Authentification

**US-001 : Inscription**
```
EN TANT QUE nouveau joueur
JE VEUX créer un compte rapidement
AFIN DE pouvoir participer aux tournois

Critères d'acceptation :
- Username unique (3-20 caractères)
- Email valide et unique
- Mot de passe sécurisé (min 8 caractères)
- Compte créé avec MLM Rank initial = 1000
- Possibilité d'upload un avatar
```

**US-002 : Connexion**
```
EN TANT QUE joueur inscrit
JE VEUX me connecter
AFIN D'accéder à mes tournois

Critères d'acceptation :
- Connexion par email + mot de passe
- Option "Se souvenir de moi"
- Génération d'un token d'authentification (Sanctum)
- Redirection vers le dashboard
```

**US-003 : Profil**
```
EN TANT QUE joueur
JE VEUX voir mon profil
AFIN DE consulter mes statistiques

Critères d'acceptation :
- Affichage du MLM Rank
- Statistiques : tournois joués, victoires, défaites
- Historique des tournois
- Possibilité de modifier avatar, email, pseudo
```

---

### 🏆 Gestion des Tournois

**US-004 : Créer un Tournoi Knockout**
```
EN TANT QU'organisateur
JE VEUX créer un tournoi à élimination directe
AFIN D'organiser une compétition

Critères d'acceptation :
- Formulaire avec : nom, jeu, format (8/16/32)
- Choix : public ou privé (avec code)
- Configuration du délai de déclaration
- Génération automatique du lien d'invitation
```

**US-005 : Créer une Ligue**
```
EN TANT QU'organisateur
JE VEUX créer une ligue round-robin
AFIN QUE tous les joueurs s'affrontent

Critères d'acceptation :
- Formulaire avec : nom, jeu, nombre de joueurs
- Choix : aller simple ou aller-retour
- Option : autoriser les nuls
- Génération automatique du calendrier
```

**US-006 : S'inscrire à un Tournoi**
```
EN TANT QUE joueur
JE VEUX m'inscrire à un tournoi
AFIN DE participer

Critères d'acceptation :
- Clic sur "S'inscrire" depuis la page du tournoi
- Vérification : pas déjà inscrit
- Snapshot du MLM Rank actuel
- Notification de confirmation
```

**US-007 : Démarrer un Tournoi**
```
EN TANT QU'organisateur
JE VEUX démarrer le tournoi
AFIN DE générer le bracket

Critères d'acceptation :
- Bouton visible seulement si max_players atteint
- Génération automatique du bracket (seeding par MLM Rank)
- Création de tous les rounds et matchs
- Notification à tous les participants
```

**US-008 : Consulter le Bracket**
```
EN TANT QUE joueur ou spectateur
JE VEUX voir le bracket
AFIN DE suivre l'avancement du tournoi

Critères d'acceptation :
- Affichage visuel du bracket (arbre)
- Mise en évidence de mon match actuel
- Scores des matchs terminés
- Temps restant pour les matchs en cours
```

---

### ⚽ Gestion des Matchs

**US-009 : Déclarer un Score**
```
EN TANT QUE joueur
JE VEUX déclarer le score de mon match
AFIN DE valider le résultat

Critères d'acceptation :
- Formulaire : score joueur 1, score joueur 2
- Upload obligatoire de capture d'écran
- Validation : je suis bien un des 2 joueurs
- Impossible de modifier après validation du match
```

**US-010 : Validation Automatique**
```
EN TANT QUE système
JE VEUX valider automatiquement les scores
AFIN DE réduire le travail de l'organisateur

Critères d'acceptation :
- Comparaison des 2 déclarations
- Si identiques : validation automatique
- Promotion du vainqueur au tour suivant
- Notifications aux 2 joueurs
```

**US-011 : Créer un Litige**
```
EN TANT QUE système
JE VEUX créer un litige si scores différents
AFIN DE permettre l'arbitrage

Critères d'acceptation :
- Détection automatique si déclarations contradictoires
- Création d'une entrée dans table disputes
- Match bloqué (status = disputed)
- Notification à l'organisateur
```

**US-012 : Arbitrer un Litige**
```
EN TANT QU'organisateur
JE VEUX résoudre un litige
AFIN DE débloquer le tournoi

Critères d'acceptation :
- Vue côte-à-côte des 2 captures d'écran
- Boutons : valider score A, valider score B, annuler match
- Possibilité d'ajouter une note
- Validation du match après arbitrage
- Notifications aux 2 joueurs
```

**US-013 : Gérer les Forfaits**
```
EN TANT QU'organisateur
JE VEUX gérer les forfaits
AFIN DE maintenir le tournoi actif

Critères d'acceptation :
- Si deadline dépassée et 1 seul joueur déclaré : notification organisateur
- Options : valider score déclaré, forfait, prolonger délai
- Pénalité ELO pour joueur forfait
```

---

### 💬 Communication

**US-014 : Chat de Tournoi**
```
EN TANT QUE participant
JE VEUX échanger avec les autres joueurs
AFIN D'organiser mes matchs

Critères d'acceptation :
- Chat global visible par tous les participants
- Messages en temps réel
- Possibilité d'envoyer des emojis
- Historique des messages conservé
```

**US-015 : Chat de Match**
```
EN TANT QUE joueur
JE VEUX discuter avec mon adversaire
AFIN DE fixer l'heure du match

Critères d'acceptation :
- Chat privé visible seulement par les 2 joueurs
- Notifications si nouveau message
- Interface accessible depuis la page du match
```

---

### 🔔 Notifications

**US-016 : Notification Match Prêt**
```
EN TANT QUE joueur
JE VEUX être notifié quand mon match est prêt
AFIN DE ne pas manquer mon tour

Critères d'acceptation :
- Notification push + in-app
- Contenu : adversaire, round, deadline
- Lien direct vers la page du match
```

**US-017 : Notification Deadline**
```
EN TANT QUE joueur
JE VEUX être rappelé avant la deadline
AFIN DE ne pas être forfait

Critères d'acceptation :
- Rappels : 24h, 12h, 6h, 1h avant deadline
- Uniquement si je n'ai pas encore déclaré
- Bouton "Déclarer maintenant" dans la notification
```

**US-018 : Notification Résultat**
```
EN TANT QUE joueur
JE VEUX être notifié du résultat de mon match
AFIN DE connaître la suite

Critères d'acceptation :
- Notification après validation du match
- Contenu : score final, vainqueur
- Si victoire : "Vous êtes qualifié pour le tour X"
- Si défaite : "Vous êtes éliminé, classement final : X"
```

---

### 📊 Classement et Statistiques

**US-019 : Consulter le MLM Rank Global**
```
EN TANT QUE joueur
JE VEUX voir le classement global
AFIN DE me comparer aux autres

Critères d'acceptation :
- Top 100 joueurs
- Tri par MLM Rank décroissant
- Affichage : position, pseudo, MLM Rank, tournois joués
- Possibilité de filtrer par jeu
```

**US-020 : Consulter mon Historique**
```
EN TANT QUE joueur
JE VEUX voir mon historique de tournois
AFIN D'analyser mes performances

Critères d'acceptation :
- Liste de tous mes tournois (du plus récent au plus ancien)
- Pour chaque tournoi : résultat, matchs, ELO change
- Graphique d'évolution du MLM Rank
- Export en PDF
```

**US-021 : Voir le Profil Public d'un Joueur**
```
EN TANT QUE joueur
JE VEUX voir le profil d'un autre joueur
AFIN DE connaître son niveau

Critères d'acceptation :
- Affichage : pseudo, avatar, MLM Rank
- Statistiques : W/L ratio, tournois gagnés
- Historique récent (5 derniers tournois)
- Option "Défier ce joueur" (future feature)
```

---

### 🏅 Système ELO

**US-022 : Calcul Automatique du MLM Rank**
```
EN TANT QUE système
JE VEUX calculer automatiquement les points ELO
AFIN DE maintenir un classement juste

Critères d'acceptation :
- Calcul en fin de tournoi (pas en temps réel pendant le tournoi)
- Formule ELO avec pondération par taille du tournoi
- Bonus de tour (demi-finale, finale)
- Mise à jour du profil joueur
```

**US-023 : Transparence du Calcul ELO**
```
EN TANT QUE joueur
JE VEUX comprendre comment mon MLM Rank est calculé
AFIN DE savoir comment progresser

Critères d'acceptation :
- Détail du calcul affiché dans l'historique
- Exemple : "Match vs Sarah (1450): -12 pts (attendu: 0.76)"
- Page d'aide "Comment fonctionne le MLM Rank ?"
```

---

## Flux d'Erreurs et Cas Limites

### ❌ Erreur 1 : **Inscription à un tournoi complet**

**Scénario** :
- Karim essaie de s'inscrire à un tournoi
- Entre-temps, le 8ème joueur vient de s'inscrire

**Comportement** :
```
❌ Inscription impossible
Ce tournoi est désormais complet (8/8).
Consultez les autres tournois disponibles.

[VOIR D'AUTRES TOURNOIS]
```

---

### ❌ Erreur 2 : **Déclaration de score invalide**

**Scénario** :
- Karim essaie de déclarer un score mais oublie la capture d'écran

**Comportement** :
```
⚠️ Capture d'écran requise
Vous devez fournir une preuve du résultat.

[RETOUR]
```

**Autre cas** : Scores négatifs
```
⚠️ Score invalide
Les scores doivent être des nombres positifs.
```

---

### ❌ Erreur 3 : **Tentative de modification après validation**

**Scénario** :
- Sarah a déclaré un score
- Elle essaie de le modifier après que le match a été validé

**Comportement** :
```
🔒 Modification impossible
Ce match a déjà été validé.
Contactez l'organisateur si vous pensez qu'il y a une erreur.
```

---

### ❌ Erreur 4 : **Organisateur essaie de démarrer un tournoi incomplet**

**Scénario** :
- Amadou essaie de démarrer un tournoi avec seulement 5/8 joueurs

**Comportement** :
```
⚠️ Tournoi incomplet
Impossible de démarrer : seulement 5/8 joueurs inscrits.

Options :
[ATTENDRE D'AUTRES INSCRIPTIONS]
[RÉDUIRE À 4 JOUEURS] (supprime 1 joueur)
[ANNULER LE TOURNOI]
```

---

### ❌ Erreur 5 : **Double inscription**

**Scénario** :
- Karim est déjà inscrit et clique à nouveau sur "S'inscrire"

**Comportement** :
```
✅ Vous êtes déjà inscrit
Vous participez à ce tournoi.

[VOIR LE TOURNOI]
[SE DÉSINSCRIRE]
```

---

### ❌ Erreur 6 : **Upload de fichier trop lourd**

**Scénario** :
- Sarah essaie d'upload une vidéo de 50MB au lieu d'une image

**Comportement** :
```
❌ Fichier trop volumineux
Taille max : 5MB
Format accepté : JPG, PNG

[CHOISIR UN AUTRE FICHIER]
```

---

### ❌ Erreur 7 : **Organisateur supprime un tournoi en cours**

**Scénario** :
- Amadou essaie de supprimer un tournoi qui a déjà démarré

**Comportement** :
```
🚫 Suppression impossible
Le tournoi a déjà démarré.
Vous pouvez uniquement l'annuler (tous les participants seront notifiés).

[ANNULER LE TOURNOI]
[RETOUR]
```

---

### ⚠️ Cas Limite 1 : **Joueur se désinscrit à la dernière minute**

**Scénario** :
- Le tournoi est complet (8/8)
- 1 joueur se désinscrit juste avant le démarrage

**Comportement** :
- Tournoi repasse en statut `registration` (7/8)
- Notification à tous : "Un joueur s'est désinscrit, en attente du 8ème joueur"
- Possibilité pour l'organisateur de :
  - Attendre un 8ème joueur
  - Démarrer avec 7 joueurs (génère un bracket déséquilibré ou refuse)

---

### ⚠️ Cas Limite 2 : **Aucune déclaration après deadline**

**Scénario** :
- Karim et Malik ne déclarent ni l'un ni l'autre

**Comportement** :
- Notification à l'organisateur : "Aucun joueur n'a déclaré"
- Options :
  - Prolonger le délai
  - Annuler le match (double forfait, les 2 sont éliminés)
  - Désigner manuellement un vainqueur

---

### ⚠️ Cas Limite 3 : **Organisateur abandonne le tournoi**

**Scénario** :
- Amadou ne répond plus, tournoi bloqué avec 2 litiges non résolus

**Comportement** :
- Après 7 jours d'inactivité de l'organisateur :
  - Notification aux participants : "L'organisateur est inactif"
  - Possibilité de nommer un nouvel organisateur (vote ?)
  - Ou annulation automatique du tournoi

---

## Conclusion

Ce document décrit les parcours utilisateurs complets pour le projet MLM. Il couvre :

- ✅ **4 personas** représentatifs
- ✅ **Parcours complets** pour tournois K.O. et Ligues
- ✅ **Scénarios détaillés** (litiges, forfaits, annulations)
- ✅ **23 user stories** organisées par fonctionnalité
- ✅ **Gestion des erreurs** et cas limites

**Prochaines étapes** :
1. Valider ces parcours avec l'équipe
2. Concevoir les wireframes/maquettes
3. Prioriser les user stories pour le MVP
4. Commencer le développement

---

**Document vivant** : Les user stories seront affinées au fur et à mesure des retours utilisateurs et des tests.
