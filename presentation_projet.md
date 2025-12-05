# 🎮 MOBILE LEAGUE MANAGER (MLM)

## La Plateforme Web de Tournois et Compétitions de Jeux Mobiles pour l'Afrique

---

**Document de Présentation du Projet**

**Version 1.0**

**Décembre 2025**

---

### 📄 Informations du Document

| Élément | Détail |
|---------|--------|
| **Titre du Projet** | Mobile League Manager (MLM) |
| **Type de Plateforme** | Application Web (Desktop & Mobile Responsive) |
| **Secteur d'Activité** | E-sports & Gaming Compétitif Mobile |
| **Marché Cible** | Afrique Francophone (Phase 1) |
| **Date de Création** | Décembre 2024 |
| **Statut** | Phase de Conception |

---

### 👥 Équipe du Projet

#### **Direction & Développement**

| Rôle | Nom | Responsabilités |
|------|-----|-----------------|
| **Chef de Projet & Développeur Lead** | Jonas (Vous) | Architecture technique, développement backend/frontend, vision produit |
| **Collaborateur Technique** | [À définir] | Développement frontend, intégration API |
| **Designer UI/UX** | [À définir] | Design de l'interface, expérience utilisateur |

#### **Partenaires & Conseillers**

| Rôle | Nom/Organisation | Domaine |
|------|------------------|---------|
| **Partenaire Financier** | [À définir] | Financement initial, stratégie économique |
| **Partenaire Mobile Money** | Orange Money / MTN Mobile Money / Moov Money | Intégration paiements mobiles |
| **Conseiller E-sports** | [À définir] | Validation du modèle compétitif |
| **Conseiller Juridique** | [À définir] | Conformité réglementaire, CGU/CGV |

#### **Équipe Opérationnelle (à recruter)**

| Rôle | Nombre | Missions |
|------|--------|----------|
| **Administrateurs** | 2-3 | Gestion globale de la plateforme, supervision |
| **Modérateurs** | 5-8 | Modération, gestion des plaintes, validation organisateurs |
| **Arbitres** | 10-15 | Résolution des litiges, validation des résultats |
| **Community Managers** | 2-3 | Animation communauté, support utilisateurs |

---

### 📋 TABLE DES MATIÈRES

1. [Résumé Exécutif](#-résumé-exécutif)
2. [Le Marché : L'Afrique, le Continent Oublié du Gaming](#-le-marché--lafrique-le-continent-oublié-du-gaming)
   - 2.1 [Les Chiffres Qui Parlent](#-les-chiffres-qui-parlent)
   - 2.2 [Le Public Cible](#-le-public-cible--qui-sont-ces-joueurs-)
3. [Le Concept MLM : Comment Ça Marche ?](#-le-concept-mlm--comment-ça-marche-)
   - 3.1 [Vue d'Ensemble](#vue-densemble)
   - 3.2 [Cas d'Usage Concret](#-cas-dusage-concret--le-tournoi-damadou)
4. [Les Fonctionnalités Uniques de MLM](#-les-fonctionnalités-uniques-de-mlm)
   - 4.1 [Divisions Automatiques](#1-divisions-automatiques-système-déditions-compétitives)
   - 4.2 [MLM Rank](#2-mlm-rank-le-classement-elo)
   - 4.3 [Système d'Équipes et Bannissement](#-système-déquipes-pseudos-de-jeu)
5. [Plateforme Web & Interactions](#-plateforme-web--interactions)
   - 5.1 [Architecture Technique](#architecture-technique)
   - 5.2 [Système de Communication](#système-de-communication)
   - 5.3 [Gestion des Preuves](#gestion-des-preuves-screenshots)
6. [Rôles et Gouvernance](#-rôles-et-gouvernance-sur-la-plateforme)
   - 6.1 [Administrateurs](#1-administrateurs-admin)
   - 6.2 [Arbitres](#2-arbitres)
   - 6.3 [Modérateurs](#3-modérateurs)
   - 6.4 [Utilisateurs](#4-utilisateurs)
7. [Système d'Organisation de Compétitions](#-système-dorganisation-de-compétitions)
   - 7.1 [Types de Compétitions](#types-de-compétitions)
   - 7.2 [Certification des Organisateurs](#certification-des-organisateurs-système-de-badges)
   - 7.3 [Conditions et Privilèges](#conditions-et-privilèges-par-niveau)
8. [Modèle Économique](#-le-modèle-économique)
   - 8.1 [Système de Monnaie MLM Coins](#système-de-monnaie-mlm-coins)
   - 8.2 [Revenus de la Plateforme](#revenus-de-la-plateforme)
9. [Avantages Compétitifs](#-pourquoi-mlm-va-gagner)
10. [Roadmap & Prochaines Étapes](#-roadmap--2025-2026)

---

<div style="page-break-after: always;"></div>

---

## 📋 Résumé Exécutif

**Mobile League Manager (MLM)** est une plateforme mobile qui permet à **n'importe qui** d'organiser et de participer à des tournois de jeux de football mobile (E-football, FC Mobile, Dream League Soccer) en quelques clics.

### En 3 points :

1. **🎯 Le Problème** : Des millions de joueurs africains de jeux mobiles n'ont **aucun moyen simple** d'organiser des compétitions et de gagner de l'argent avec leur passion.

2. **💡 La Solution** : Une application mobile qui automatise **tout** : création de tournois, validation des scores, paiements, classements, et permet aux joueurs de **gagner de l'argent réel**.

3. **💰 Le Modèle** : Commission de 15% sur les tournois payants + 50% pour les organisateurs (frais d'inscription: 50,000 FCFA) + publicités. Pas d'investissement initial pour les joueurs.

---

## 🌍 Le Marché : L'Afrique, le Continent Oublié du Gaming

### 📊 Les Chiffres Qui Parlent

L'industrie du gaming mobile en Afrique est **massive mais invisible** :

- **📱 500+ millions** de smartphones en Afrique (2024) [Source: GSMA Intelligence, 2024]
- **🎮 77%** des joueurs africains jouent exclusivement sur mobile [Source: Newzoo Global Games Market Report, 2023]
- **💸 $1.9 milliards** de revenus gaming en Afrique (2023) - en croissance de **12% par an** [Source: Newzoo, PwC Entertainment & Media Outlook 2023]
- **🏆 E-sports** : Marché africain estimé à **$180 millions** en 2024 [Source: Statista E-sports Market Report, 2024]

### 🎯 Le Public Cible : Qui Sont Ces Joueurs ?

#### Profil Type : **Amadou, 23 ans, Dakar (Sénégal)**

*"Je joue à E-football tous les jours avec mes potes. On fait des mini-tournois sur WhatsApp mais c'est le bordel : les gens trichent, on perd les scores, et il n'y a aucune récompense. J'aimerais pouvoir prouver que je suis le meilleur et gagner un peu d'argent."*

**Caractéristiques** :
- Âge : 18-35 ans
- Possède un smartphone Android (70%) ou iOS (30%)
- Joue **2-4h par jour** (transport, pauses, soirées)
- Revenus modestes : 50,000 - 200,000 FCFA/mois
- Très actif sur WhatsApp, Facebook, TikTok
- Passionné de football (réel et virtuel)
- Membre de groupes/communautés gaming

#### Les Segments

| Segment | Description | Taille | Potentiel |
|---------|-------------|--------|-----------|
| **Joueurs Occasionnels** | Jouent entre amis, tournois gratuits | 60% | Engagement, publicité |
| **Joueurs Réguliers** | Participent 2-3 tournois/semaine | 30% | Tournois payants (5-20 coins) |
| **Compétiteurs** | Cherchent classement et gains | 8% | Gros tournois (50+ coins) |
| **Organisateurs** | Gèrent des communautés/clans | 2% | Commission organisateur |

**Estimation** : Sur **50 millions de joueurs mobiles** en Afrique de l'Ouest [Source: Newzoo, estimation basée sur la pénétration mobile 2024], **5 millions** jouent régulièrement à des jeux de foot mobile.

---

## 🚨 Le Problème : Pourquoi le Gaming Mobile Africain Est Bloqué

### 1️⃣ **Aucune Infrastructure de Compétition**

**Aujourd'hui, comment ça se passe ?**

Amadou veut organiser un tournoi avec ses 15 amis :

```
❌ Jour 1 : Il crée un groupe WhatsApp
❌ Jour 2 : Il écrit le tableau sur papier
❌ Jour 3 : Karim et Moussa disent "j'ai gagné 3-1" / "Non c'est moi 2-1"
❌ Jour 4 : La moitié des joueurs ont abandonné
❌ Jour 5 : Le tournoi meurt sans vainqueur
```

**Les problèmes concrets** :
- ⏰ **Gestion manuelle** : Créer le tableau, suivre les scores, gérer les litiges = 5-10h de travail
- 🤥 **Tricherie** : Impossible de vérifier les résultats
- 💸 **Pas de gains** : Même si on collecte de l'argent, difficile de redistribuer équitablement
- 📉 **Abandon** : 80% des tournois informels ne se terminent jamais

### 2️⃣ **Pas de Monétisation pour les Joueurs**

Les joueurs africains sont **excellents** mais **invisibles** :
- Les plateformes internationales (ESL, Faceit) ne supportent pas les paiements mobiles africains
- Les tournois locaux n'ont pas de prizes pools
- Pas de classement reconnu pour prouver son niveau

**Résultat** : Amadou a **2000 heures de jeu** mais **0 FCFA gagné**. Il est meilleur que beaucoup de joueurs européens mais personne ne le sait.

### 3️⃣ **Mobile Money Ignoré**

En Afrique, **90% des transactions** se font via mobile money (Orange Money, MTN Money, Moov Money) [Source: GSMA State of the Industry Report on Mobile Money, 2023].

**Les plateformes gaming internationales** :
- Acceptent seulement cartes bancaires (que 15% des Africains possèdent) [Source: Banque Mondiale, Global Findex Database 2021]
- Pas d'intégration Orange Money / MTN Money
- Conversion USD → FCFA compliquée

**Exemple concret** : Pour participer à un tournoi Faceit, Amadou doit :
1. Avoir une carte bancaire (il n'en a pas)
2. Payer en USD (frais de conversion élevés)
3. Retirer ses gains vers PayPal (impossible sans compte bancaire)

**C'est une barrière insurmontable.**

---

## 💡 La Solution : Mobile League Manager

### Comment MLM Résout Tous Ces Problèmes

#### ✅ **Problème 1 : Gestion Manuelle** → **Automatisation Complète**

**Avant (WhatsApp)** :
```
Amadou : "Ok les gars, tournoi samedi !"
[30 minutes de messages pour savoir qui participe]
Amadou : [Dessine le tableau sur papier]
[3 jours de rappels pour que les gens jouent]
Karim : "J'ai gagné 3-1"
Moussa : "Non c'est moi"
Amadou : [Passe 2h à résoudre le litige]
```

**Avec MLM** :
```
1. Amadou crée le tournoi en 2 clics
2. Partage le lien WhatsApp
3. Les 8 joueurs s'inscrivent
4. Le bracket est généré automatiquement
5. Après chaque match, les 2 joueurs uploadent la capture d'écran
6. Si les scores correspondent → Validation automatique
7. Le vainqueur est automatiquement qualifié pour le tour suivant
8. En fin de tournoi → Gains crédités automatiquement
```

**Temps économisé** : **95%** (de 10h à 30 minutes)

---

#### ✅ **Problème 2 : Tricherie** → **Preuves & Arbitrage**

**Le système de validation** :

1. **Déclaration obligatoire** : Chaque joueur déclare le score + upload capture d'écran
2. **Comparaison automatique** :
   - Si les 2 déclarations correspondent → ✅ Validé
   - Si différentes → 🚨 Litige
3. **Arbitrage rapide** : L'organisateur voit les 2 captures côte-à-côte et tranche en 30 secondes

**Résultat** : **Zéro tricherie possible** car tout est tracé.

---

#### ✅ **Problème 3 : Pas de Gains** → **Économie Intégrée**

**Le Wallet MLM Coins** :

```
10 MLM Coins = 500 FCFA
```

**Comment ça marche ?**

**Pour un Joueur** :
1. Je recharge **100 coins** (50,000 FCFA) via **Orange Money** en 30 secondes
2. Je m'inscris à un tournoi payant : **4 coins** (2,000 FCFA)
3. Si je gagne le tournoi → Je reçois **13.6 coins** (6,800 FCFA) dans mon solde
4. Je peux retirer mes gains vers mon **Orange Money** à tout moment

**Pour un Organisateur** :

**Prérequis** : Frais d'inscription unique de **50,000 FCFA** pour devenir organisateur

1. Je crée un tournoi payant : 8 joueurs × 4 coins = **32 coins collectés** (16,000 FCFA)
2. La plateforme prend **15%** (4.8 coins = 2,400 FCFA) pour les frais
3. Je reçois **50%** (16 coins = 8,000 FCFA) pour l'organisation
4. Les joueurs se partagent **au minimum 35%** (11.2 coins = 5,600 FCFA)

**Transparence totale** : Tout est calculé automatiquement, pas de manipulation possible.

---

#### ✅ **Problème 4 : Paiements Compliqués** → **Mobile Money Natif**

**Intégrations natives** :
- ✅ Orange Money (Sénégal, Côte d'Ivoire, Mali...)
- ✅ MTN Money (Ghana, Nigeria, Cameroun...)
- ✅ Moov Money (Bénin, Togo...)
- ✅ Cartes bancaires (Visa, Mastercard)

**Rapidité** :
- Recharge : **30 secondes** (scan QR code ou USSD)
- Retrait : **1-24 heures** (traité par l'admin)

**Pas besoin de** :
- ❌ Compte bancaire
- ❌ PayPal
- ❌ Conversion de devises

---

### 🎯 L'Expérience Utilisateur en 1 Minute

**Karim veut participer à son premier tournoi payant :**

```
1. Reçoit un lien WhatsApp : "🏆 Tournoi E-football ce soir - 20 coins"
   [Il clique] → 5 secondes

2. Télécharge l'app MLM → 30 secondes

3. S'inscrit (email + mot de passe) → 20 secondes

4. Recharge 10 coins via Orange Money → 30 secondes
   [Scan QR → Confirme sur téléphone → Solde crédité]

5. S'inscrit au tournoi → 5 secondes
   [Clique "S'inscrire" → -4 coins → Confirmé]

✅ TOTAL : 1 minute 30 secondes

Le soir même :
6. Joue son match contre Sarah
7. Prend une capture d'écran du résultat
8. Upload sur MLM + déclare "Sarah 3-1 Karim"
9. Sarah fait la même chose → Match validé automatiquement

Karim perd en demi-finale mais reçoit 2 coins (3-4ème place) dans son solde.
```

**C'est aussi simple que payer un taxi avec Orange Money.**

---

## 🏅 Les Fonctionnalités Uniques de MLM

### 1. **Divisions Automatiques** (Système d'Éditions Compétitives)

MLM crée un **système de divisions par éditions** avec qualification et réinitialisation à chaque édition, inspiré des ligues de football professionnelles :

#### **🏁 Système d'Accès aux Divisions par Édition**

**⚙️ Principe de base** : Chaque édition utilise le **MÊME processus** de qualification et répartition.

**📋 Processus d'Inscription avec Liste d'Attente**

MLM utilise un système intelligent pour gérer les inscriptions :

**Étape 1 : Inscriptions ouvertes**
```
Objectif : 80 équipes minimum (20 par division × 4)
Maximum : 120 équipes (20 groupes de qualification)

Phase d'inscription :
├─ 0-79 inscrits : Inscriptions ouvertes
├─ 80 inscrits atteints : Blocage temporaire des inscriptions
└─ Nouveaux intéressés → Liste d'attente
```

**Étape 2 : Gestion de la liste d'attente**
```
Si liste d'attente = 20 à 40 personnes :
→ Prolongation des inscriptions jusqu'à 120 équipes max

Si liste d'attente > 40 personnes :
→ Blocage définitif à 120 équipes
→ Les autres reçoivent priorité pour l'Édition suivante
```

**Étape 3 : Lancement de l'édition**
```
Cas A : < 80 inscrits
→ Formation directe en 4 divisions (pas de qualification)
→ Répartition équitable entre D4, D3, D2, D1

Cas B : 80 à 120 inscrits
→ Phase de qualification obligatoire
→ Formation de 14 à 20 groupes de 6 équipes
```

**💡 Pourquoi ce système ?**
- ✅ **Flexibilité** : Accepter plus de joueurs si forte demande
- ✅ **Équité** : Liste d'attente avec priorité pour édition suivante
- ✅ **Gestion optimale** : Maximum 120 équipes = 20 groupes (facile à gérer)

---

#### **🎯 Phase de Qualification (si ≥80 inscrits)**

**Format** : Groupes de 6 équipes

**Déroulement** :
```
Semaines 1-2 : Phase de qualification (2 semaines)
├─ Chaque équipe affronte les 5 autres de son groupe
├─ 5 matchs au total par équipe
└─ Classement basé sur points (Victoire = 3pts, Nul = 1pt, Défaite = 0pt)

Semaines 3-12 : Édition régulière (10 semaines)
├─ Les qualifiés jouent dans leur division respective
└─ Compétition pour les titres de champion

Semaine 13 : Finales et Récompenses
```

**🏆 Répartition après qualification (Groupes de 6)** :
```
🥇 1er du groupe → Division 4 (D4) - Elite (Meilleure division)
🥈 2ème du groupe → Division 3 (D3) - Excellence
🥉 3ème du groupe → Division 2 (D2) - Confirmé
⚪ 4ème du groupe → Division 1 (D1) - Standard (Plus basse division)
❌ 5ème du groupe → ÉLIMINÉ (remboursé 75%)
❌ 6ème du groupe → ÉLIMINÉ (remboursé 75%)
```

**Note importante** : L'ordre des divisions est **D4 > D3 > D2 > D1** (D4 étant la division d'élite, D1 la division standard).

**💡 Exemple concret : 120 équipes inscrites**
```
120 équipes ÷ 6 = 20 groupes de qualification

Phase de qualification (2 semaines) :
├─ Groupe 1 : 6 équipes jouent 5 matchs chacune
├─ Groupe 2 : 6 équipes jouent 5 matchs chacune
├─ ...
└─ Groupe 20 : 6 équipes jouent 5 matchs chacune

Résultats après qualification :
├─ 20 équipes → D4 (les 1ers de chaque groupe)
├─ 20 équipes → D3 (les 2èmes de chaque groupe)
├─ 20 équipes → D2 (les 3èmes de chaque groupe)
├─ 20 équipes → D1 (les 4èmes de chaque groupe)
└─ 40 équipes → Éliminées et remboursées à 75%

Édition régulière (10 semaines) :
├─ D4 : 20 équipes (4 groupes de 5)
├─ D3 : 20 équipes (4 groupes de 5)
├─ D2 : 20 équipes (4 groupes de 5)
└─ D1 : 20 équipes (4 groupes de 5)
```

---

#### **💰 Frais d'Inscription Progressifs**

Les frais augmentent chaque édition pour créer de l'exclusivité :

```
Édition 1 : 4 MLC = 2,000 FCFA
Édition 2 : 6 MLC = 3,000 FCFA
Édition 3 : 8 MLC = 4,000 FCFA
Édition 4 : 10 MLC = 5,000 FCFA
Édition 5+ : Paramétrable (peut continuer à augmenter)
```

**Pourquoi cette progression ?**
- 🎯 **Accessibilité Édition 1** : Prix bas pour attirer les premiers joueurs
- 📈 **Valeur croissante** : Plus la plateforme grandit, plus elle a de valeur
- 💎 **Exclusivité** : Les prix élevés filtrent les joueurs occasionnels
- 🏆 **Récompense la fidélité** : Les anciens joueurs ont profité des prix bas

---

#### **💵 Répartition des Revenus**

**Modèle économique par édition** :

```
Revenus totaux = Nombre d'inscrits × Frais d'inscription

Distribution :
├─ 55% → Prize Pools (récompenses pour les 4 champions)
└─ 45% → Plateforme MLM (fonctionnement, support, imprévus)

Répartition du Prize Pool entre les champions :
├─ 1er D4 (Elite) : 45.5% du prize pool
├─ 1er D3 (Excellence) : 27.3% du prize pool
├─ 1er D2 (Confirmé) : 18.2% du prize pool
└─ 1er D1 (Standard) : 9.0% du prize pool
Total : 100% du prize pool
```

**Exemple Édition 1 : 100 équipes × 4 MLC**
```
Revenus totaux : 100 × 4 MLC = 400 MLC (200,000 FCFA)

Distribution :
├─ 220 MLC (55%) → Prize Pools
│   ├─ 1er D4 : 220 × 45.5% = 100 MLC (50,000 FCFA)
│   ├─ 1er D3 : 220 × 27.3% = 60 MLC (30,000 FCFA)
│   ├─ 1er D2 : 220 × 18.2% = 40 MLC (20,000 FCFA)
│   └─ 1er D1 : 220 × 9.0% = 20 MLC (10,000 FCFA)
└─ 180 MLC (45%) → Plateforme MLM
```

**Exemple Édition 2 : 100 équipes × 6 MLC**
```
Revenus totaux : 100 × 6 MLC = 600 MLC (300,000 FCFA)

Distribution :
├─ 330 MLC (55%) → Prize Pools
│   ├─ 1er D4 : 330 × 45.5% = 150 MLC (75,000 FCFA)
│   ├─ 1er D3 : 330 × 27.3% = 90 MLC (45,000 FCFA)
│   ├─ 1er D2 : 330 × 18.2% = 60 MLC (30,000 FCFA)
│   └─ 1er D1 : 330 × 9.0% = 30 MLC (15,000 FCFA)
└─ 270 MLC (45%) → Plateforme MLM
```

**💡 Avantage du système en pourcentage** :
- ✅ Les récompenses évoluent automatiquement avec les frais d'inscription
- ✅ Plus il y a de participants, plus les gains augmentent
- ✅ Transparence totale sur la répartition
- ✅ Scalable sans modification du système

---

#### **🏆 Distribution des Prize Pools par Division**

**Système de récompense du champion uniquement** :

Seuls les **1ers de chaque division** reçoivent des récompenses, calculées en **pourcentage du prize pool total** :

```
Répartition du Prize Pool (55% des revenus totaux) :

🏆 Division 4 (D4) - Elite : 45.5% du prize pool
   → Champion (1er place) : Gagne le plus (meilleure division)
   → Autres positions : 0 MLC

💎 Division 3 (D3) - Excellence : 27.3% du prize pool
   → Champion (1er place) : Deuxième plus grosse récompense
   → Autres positions : 0 MLC

🥈 Division 2 (D2) - Confirmé : 18.2% du prize pool
   → Champion (1er place) : Troisième récompense
   → Autres positions : 0 MLC

⚽ Division 1 (D1) - Standard : 9.0% du prize pool
   → Champion (1er place) : Plus petite récompense (division la plus basse)
   → Autres positions : 0 MLC

TOTAL : 100% du prize pool réparti entre les 4 champions
```

**💡 Exemple concret Édition 1 (100 joueurs × 4 MLC = 400 MLC)** :
```
Prize Pool = 400 × 55% = 220 MLC

├─ Champion D4 : 220 × 45.5% = 100 MLC (50,000 FCFA) 💰
├─ Champion D3 : 220 × 27.3% = 60 MLC (30,000 FCFA) 💰
├─ Champion D2 : 220 × 18.2% = 40 MLC (20,000 FCFA) 💰
└─ Champion D1 : 220 × 9.0% = 20 MLC (10,000 FCFA) 💰
```

**💡 Pourquoi seuls les champions ?**
- 🎯 **Compétition intense** : Il faut être le meilleur pour gagner
- 🏆 **Récompense l'excellence** : Seule la victoire compte
- ⚖️ **Simplicité** : Distribution claire et transparente
- 💪 **Motivation** : Objectif unique et clair pour chaque division
- 📈 **Scalable** : Les montants évoluent automatiquement avec les inscriptions

---

#### **🔄 Règles de Qualification et Élimination**

**Système d'auto-qualification et d'élimination temporaire** :

**🏆 Auto-qualification des Champions** :
```
Les 1ers de chaque division (D1, D2, D3, D4) sont AUTOMATIQUEMENT qualifiés
pour l'édition suivante et seront TOUS placés en Division 4 (D4) - Elite
pour cette prochaine édition.

Exemple Édition 1 :
├─ 1er D1 → Qualifié automatiquement pour Édition 2 en D4 (Elite)
├─ 1er D2 → Qualifié automatiquement pour Édition 2 en D4 (Elite)
├─ 1er D3 → Qualifié automatiquement pour Édition 2 en D4 (Elite)
└─ 1er D4 → Qualifié automatiquement pour Édition 2 en D4 (Elite)

Ces 4 champions ne paient PAS de frais d'inscription pour l'Édition 2.
Ils jouent directement dans la division d'élite (D4).
```

**❌ Élimination Temporaire des Derniers** :
```
Les DERNIERS de chaque division (D1, D2, D3, D4) sont ÉLIMINÉS de l'édition
suivante mais pourront jouer l'édition d'après.

Exemple :
Les derniers de D1, D2, D3, D4 de l'Édition 1 :
├─ ❌ NE PEUVENT PAS jouer l'Édition 2
└─ ✅ PEUVENT jouer l'Édition 3 (après avoir sauté une édition)

Édition 1 → Dernier place → ❌ Édition 2 (éliminé) → ✅ Édition 3 (retour possible)
```

**Fin d'Édition N** :
```
1. 📊 Calcul des classements finaux dans chaque division
2. 🏆 Distribution des prize pools aux 4 champions uniquement
3. 🎫 Auto-qualification des champions pour Édition N+1 en D4 (Elite)
4. ❌ Élimination temporaire des derniers (skip Édition N+1)
5. 📜 Enregistrement dans l'historique MLM (Hall of Fame)
```

**Début d'Édition N+1** :
```
1. 🆕 Inscriptions ouvertes à TOUS (sauf derniers d'Édition N)
   ├─ Champions Édition N : Qualifiés automatiquement en D4 (gratuit)
   └─ Autres joueurs : Doivent s'inscrire et payer les frais

2. 📋 Processus d'inscription :
   ├─ Objectif : 80 équipes minimum
   └─ Maximum : 120 équipes

3. 🎯 Phase de qualification (si ≥80 inscrits) :
   ├─ Groupes de 6 équipes
   ├─ 5 matchs par équipe
   └─ Répartition : 1er→D4, 2ème→D3, 3ème→D2, 4ème→D1

4. 🏁 Édition régulière lance avec 4 champions pré-qualifiés en D4
```

**💡 Exemple concret** :
```
Édition 1 (Juillet-Septembre 2024) - 4 MLC
├─ Karim termine 1er D1 → 🏆 Récompense + Auto-qualifié Édition 2 en D4 (gratuit)
├─ Amadou termine 5ème D2 → Rien
├─ Sarah termine DERNIER D3 → ❌ Éliminée pour Édition 2
├─ Moussa termine 1er D4 → 🏆 Récompense + Auto-qualifié Édition 2 en D4 (gratuit)
└─ Ibrahim termine 1er D3 → 🏆 Récompense + Auto-qualifié Édition 2 en D4 (gratuit)

Édition 2 (Octobre-Décembre 2024) - 6 MLC
├─ Karim : Joue directement en D4 (auto-qualifié, pas de frais)
├─ Moussa : Joue directement en D4 (auto-qualifié, pas de frais)
├─ Ibrahim : Joue directement en D4 (auto-qualifié, pas de frais)
├─ + 1 autre champion : Joue directement en D4 (auto-qualifié, pas de frais)
├─ Amadou : Doit se réinscrire et payer 6 MLC + qualification
├─ Sarah : ❌ NE PEUT PAS jouer (éliminée temporairement)
└─ Nouveaux joueurs : S'inscrivent et passent qualification

Édition 3 (Janvier-Mars 2025) - 8 MLC
└─ Sarah : ✅ PEUT se réinscrire (élimination levée)
```

**Pourquoi ces règles ?**
- ✅ **Récompense les champions** : Auto-qualification + gratuit
- ✅ **Sanction les derniers** : Obligation de skip une édition
- ✅ **Motivation** : Ne pas finir dernier
- ✅ **Renouvellement** : Évite les joueurs inactifs/faibles
- ✅ **Équité** : Élimination temporaire, pas permanente

---

#### **📅 Calendrier d'Édition Détaillé**

**Durée d'une édition** : 3 mois (exemple : Juillet, Août, Septembre)

**Fréquence des journées** :
- **3 journées par semaine**
- **Exemple de planning** : Mercredi, Vendredi, Samedi
- **Total** : ~38 journées par édition

**Comment ça fonctionne ?** :
```
Semaine Type (12 semaines de compétition)
├── Mercredi : Journée 1
├── Vendredi : Journée 2
└── Samedi : Journée 3

Total : 12 semaines × 3 journées = 36-38 journées
```

Chaque journée, ton équipe doit jouer son match programmé. Si tu rates une journée, elle est comptabilisée comme **absence**.

#### **🎥 Déroulement des Matchs**

**Deux options pour jouer les matchs** :

```
Option 1 : Stream en Direct (Recommandé)
├─ Les joueurs lancent un stream sur la plateforme (Angular)
├─ Le match est diffusé en temps réel
├─ Enregistrement automatique pour vérification
└─ Transparence totale

Option 2 : Enregistrement + Soumission
├─ Chaque joueur enregistre son match (vidéo)
├─ Upload de la vidéo après le match
├─ Soumission du score + preuve vidéo
└─ Vérification manuelle si litige
```

**Pourquoi ces options ?**
- ✅ **Transparence** : Preuves vidéo de tous les matchs
- ✅ **Anti-triche** : Impossible de falsifier les résultats
- ✅ **Flexibilité** : Stream live OU enregistrement selon préférence
- ✅ **Engagement** : Les spectateurs peuvent regarder les matchs

#### **👥 Système d'Équipes (Pseudos de Jeu)**

**IMPORTANT** : Pour participer aux divisions, tu dois enregistrer ton pseudo de jeu.

**Règles** :
- ✅ Tu peux créer **2 équipes maximum par jeu**
  - Équipe 1 : Ton pseudo principal (ex: "KarimFCPro")
  - Équipe 2 : Ton pseudo secondaire (ex: "KarimTheKing")
- ✅ Chaque équipe participe **indépendamment** aux divisions
- ✅ Tu peux utiliser une équipe pour E-football, une autre pour FC Mobile, etc.

**Exemple** :
```
Amadou a :
  ├─ E-football
  │   ├─ Équipe 1: "AmadouElite" → Joue en D3
  │   └─ Équipe 2: "AmadouPro" → Joue en D1
  ├─ FC Mobile
  │   ├─ Équipe 1: "Amadou_24" → Joue en D2
  │   └─ Équipe 2: "AmadouMobile" → Joue en D4
```

#### **⚠️ Règles de Fair-Play et Bannissement**

**MLM applique des règles strictes pour garantir la régularité** :

**Règle 1 : Bannissement d'équipe (3 absences injustifiées)**
- Si ton équipe fait **3 absences injustifiées** au cours d'une édition → ❌ **Équipe bannie**
- L'équipe ne peut plus participer aux prochaines éditions de divisions
- **Mais** : Tu peux continuer avec ton autre équipe (si tu en as une)

**Règle 2 : Bannissement de compte (2 équipes bannies pour un même jeu)**
- Chaque utilisateur peut créer **2 équipes maximum par jeu** (Dream League, FC Mobile, E-football)
- Si tes **2 équipes d'un même jeu** sont bannies → ❌ **Ton compte utilisateur est banni définitivement**
- Tu ne peux plus jamais te réinscrire sur MLM
- Le système bloque ton compte pour empêcher toute nouvelle inscription

**Pourquoi ces règles ?**
- 🎯 **Garantir l'engagement** : Seuls les joueurs sérieux restent
- ⚖️ **Équité** : Pas de places perdues à cause de joueurs fantômes
- 🏆 **Compétition de qualité** : Tous les matchs se jouent réellement
- 💪 **Responsabilité** : 3 chances avant bannissement d'équipe

**Exemple concret** :
```
Karim a 2 équipes pour Dream League Soccer :
  ├─ "KarimFCPro" → 3 absences injustifiées Édition 1 → ❌ ÉQUIPE BANNIE
  └─ "KarimTheKing" → Continue normalement → ✅ OK

Karim peut continuer avec "KarimTheKing" uniquement.

Si "KarimTheKing" fait aussi 3 absences injustifiées :
  ├─ "KarimFCPro" → ❌ BANNIE (Dream League Soccer)
  └─ "KarimTheKing" → ❌ BANNIE (Dream League Soccer)

  → ❌ COMPTE UTILISATEUR KARIM BANNI DÉFINITIVEMENT

Note : Karim ne peut plus accéder à la plateforme MLM, même pour d'autres jeux.
```

**Absence justifiée vs injustifiée** :
```
✅ Absence justifiée (ne compte pas) :
├─ Problème technique prouvé (coupure internet, panne)
├─ Raison médicale avec justificatif
└─ Force majeure validée par modérateur

❌ Absence injustifiée (compte) :
├─ Aucune raison donnée
├─ "J'ai oublié"
└─ Raison non acceptée par modérateur
```

**Message clair** : **Joue sérieusement ou ne participe pas.** MLM n'est pas fait pour les joueurs fantômes.

#### **📜 Historique des Éditions et Palmarès**

**Conservation des données** :

Chaque édition, MLM conserve **l'historique complet** des performances et des champions :

**Ce qui est enregistré** :
- 🏆 **Vainqueurs de chaque division** (D1, D2, D3, D4)
- 📊 **Classements finaux** de tous les groupes
- 🎯 **Statistiques individuelles** :
  - Nombre de victoires, défaites, nuls
  - Buts marqués et encaissés
  - Points totaux de l'édition
- 🏅 **Meilleurs buteurs** par division
- 📈 **Évolution des équipes** : Auto-qualifications et éliminations

**Pourquoi c'est important ?**
- 📜 **Mémoire du jeu** : Voir l'évolution depuis l'Édition 1
- 🏆 **Hall of Fame** : Les champions de chaque édition sont immortalisés
- 📊 **Statistiques complètes** : Consulter l'historique de n'importe quelle équipe
- 💪 **Motivation** : Devenir champion d'une édition pour rester dans l'histoire

**Exemple d'historique** :
```
Édition 1 - Juillet à Septembre 2024 (Dream League Soccer)
100 joueurs × 4 MLC = Prize Pool de 220 MLC

├─ D4 (Elite)
│   └─ Champion : "AmadouMobile" - 44 points - 🏆 100 MLC (45.5%)
│       → Auto-qualifié Édition 2 en D4 (gratuit)
├─ D3 (Excellence)
│   └─ Champion : "BrahimaLion" - 46 points - 🏆 60 MLC (27.3%)
│       → Auto-qualifié Édition 2 en D4 (gratuit)
├─ D2 (Confirmé)
│   └─ Champion : "SarahPro" - 48 points - 🏆 40 MLC (18.2%)
│       → Auto-qualifié Édition 2 en D4 (gratuit)
└─ D1 (Standard)
    ├─ Champion : "KarimFCPro" - 45 points - 🏆 20 MLC (9.0%)
    │   → Auto-qualifié Édition 2 en D4 (gratuit)
    ├─ 2ème : "Moussa_24" - 38 points
    └─ Dernier : "Youssef_Pro" - 12 points (éliminé Édition 2)

Édition 2 - Octobre à Décembre 2024 (Dream League Soccer)
150 joueurs × 6 MLC = Prize Pool de 495 MLC

├─ D4 (Elite) - Contient les 4 champions Édition 1
│   └─ Champion : "KarimFCPro" - 50 points - 🏆 225 MLC (45.5%)
│       → Auto-qualifié Édition 3 en D4 (gratuit)
├─ D3 (Excellence)
│   └─ Champion : "Ibrahim24" - 45 points - 🏆 135 MLC (27.3%)
│       → Auto-qualifié Édition 3 en D4 (gratuit)
├─ D2 (Confirmé)
│   └─ Champion : "NewPlayer123" - 47 points - 🏆 90 MLC (18.2%)
│       → Auto-qualifié Édition 3 en D4 (gratuit)
└─ D1 (Standard)
    └─ Champion : "TeamRocket" - 42 points - 🏆 45 MLC (9.0%)
        → Auto-qualifié Édition 3 en D4 (gratuit)
```

**Consultation de l'historique** :
- ✅ Accessible depuis ton profil MLM
- ✅ Voir les classements de toutes les éditions passées
- ✅ Comparer ton évolution édition après édition
- ✅ Consulter le palmarès de n'importe quelle équipe

**Conservation permanente** :
- 🔄 À chaque nouvelle édition, nouveau processus de qualification
- 📊 MAIS l'historique des performances passées reste **permanent**
- 🏆 Les titres de champion restent acquis à vie

#### **Pourquoi c'est génial ?**
- 🎯 **Progression claire** : Objectif de devenir champion de sa division
- 🏆 **Récompense du mérite** : Seuls les champions gagnent des prix
- 💪 **Motivation constante** : Chaque match compte pour ne pas finir dernier
- 🤝 **Communauté par niveau** : Tu joues contre des joueurs de ton calibre
- 📈 **Sentiment d'accomplissement** : Devenir champion = fierté + récompense
- ⚡ **Fair-play garanti** : Système de bannissement pour joueurs inactifs (3 absences)
- 🎮 **Multi-jeux** : Éditions pour Dream League Soccer, FC Mobile, E-football
- 🎥 **Transparence** : Matchs streamés en direct ou enregistrés

---

### 2. **MLM Rank** (Le Classement ELO)

Chaque joueur a un **score de compétence** qui évolue :

```
Karim commence à 1000 points

Match 1 : Karim (1000) bat Moussa (950) → +15 points → 1015
Match 2 : Karim (1015) bat Sarah (1200) → +35 points → 1050
Match 3 : Karim (1050) perd vs Amadou (1400) → -8 points → 1042

Après 50 tournois → Karim atteint 1250 points → Ligue 2
```

**Avantages** :
- ✅ Classement **global** reconnu
- ✅ Prouve ton niveau (comme un CV de joueur)
- ✅ Matchmaking équilibré
- ✅ Objectif de progression claire

**Exemple réel** : Sarah (1450 MLM Rank) peut dire à un sponsor : *"Je suis Top 50 en Afrique de l'Ouest sur E-football, voici ma preuve."*

---

### 3. **Tournois Gratuits ET Payants**

**MLM n'est PAS "pay-to-play"** :

| Type | Description | Usage |
|------|-------------|-------|
| **Gratuit** | Aucun frais, pas de gains | 60% des tournois - Entre amis, pour s'amuser |
| **Micro-buy** | 1-4 coins (500-2,000 FCFA) | 30% des tournois - Compétition casual |
| **Premium** | 10+ coins (5,000+ FCFA) | 10% des tournois - Joueurs sérieux |

**L'organisateur choisit** :
- Tournoi gratuit pour son clan → 0 FCFA
- Tournoi payant pour pimenter → Il définit les frais et la répartition

**Flexibilité totale.**

---

## 💻 Plateforme Web & Interactions

### Architecture Technique

**Type de Plateforme** : Application Web Progressive (PWA)

```
Plateforme MLM = Application Web Responsive
├─ Desktop : Expérience complète (gestion tournois, administration)
├─ Mobile : Interface adaptée (participation, notifications)
└─ Tablette : Hybride (consultation + participation)
```

**Pourquoi Web et pas Mobile Native ?**
- ✅ **Accessibilité** : Pas besoin de télécharger une app (économie de data)
- ✅ **Mise à jour instantanée** : Pas besoin d'attendre les stores
- ✅ **Compatibilité** : Fonctionne sur tous les appareils (Android, iOS, Windows, Mac)
- ✅ **SEO** : Meilleure découvrabilité via Google
- ✅ **Coût** : Développement unique pour toutes les plateformes

**Technologies** :
```
Frontend : Vue.js 3 / React (responsive design)
Backend : Java Spring Boot 3.x (API REST)
Base de données : PostgreSQL
Cache : Redis
Messaging : RabbitMQ (queues asynchrones pour emails, notifications)
Sécurité : Spring Security + JWT
WebSocket : Spring WebSocket (chat temps réel)
Notifications : Email (JavaMail) + SMS via API
Paiements : API Mobile Money (Orange, MTN, Moov)
Hébergement : Cloud scalable (AWS / DigitalOcean / Heroku)
CI/CD : GitHub Actions / Jenkins
```

---

### Système de Communication

MLM intègre un **système de communication hybride** (chat + email) pour faciliter les échanges entre participants.

#### **💬 Chat Intégré avec Notifications Email**

**Comment ça fonctionne ?**

```
Scénario : Karim envoie un message à Amadou pour un match

1. Karim écrit dans le chat MLM :
   "Yo Amadou, on joue notre match ce soir à 20h ?"

2. Le système envoie AUTOMATIQUEMENT :
   ├─ Message dans le chat MLM (visible sur la plateforme)
   └─ Email à Amadou avec le contenu du message
       → Objet : "Nouveau message de Karim sur MLM"
       → Contenu : "Yo Amadou, on joue notre match ce soir à 20h ?"
       → Lien : Répondre sur MLM

3. Amadou peut :
   ├─ Répondre depuis la plateforme MLM
   └─ Ou répondre directement par email
       → Sa réponse apparaît automatiquement dans le chat MLM
```

**Avantages** :
- ✅ **Zéro message manqué** : Notification email garantit que le destinataire voit le message
- ✅ **Flexibilité** : Répond depuis la plateforme ou par email
- ✅ **Historique centralisé** : Tous les échanges sont sauvegardés sur MLM
- ✅ **Pas de WhatsApp requis** : Communication autonome

#### **Types de conversations** :

```
1. Chat de match (1v1)
   → Entre 2 joueurs d'un match spécifique
   → Organiser l'heure, confirmer résultat

2. Chat de tournoi (groupe)
   → Tous les participants d'un tournoi
   → Annonces de l'organisateur, discussions

3. Chat avec support (modération)
   → Utilisateur ↔ Modérateur/Arbitre
   → Signalement de problèmes, litiges
```

---

### Gestion des Preuves (Screenshots)

Pour éviter les litiges et garantir la transparence, MLM intègre un **système de soumission de captures d'écran**.

#### **📸 Comment ça marche ?**

**Scénario : Match entre Karim et Amadou**

```
1. Le match se joue sur E-football (sur console/mobile)

2. Après le match :
   ├─ Karim soumet le résultat : "J'ai gagné 3-2"
   └─ Karim uploade une capture d'écran du score final

3. Amadou reçoit une notification :
   "Karim a déclaré le résultat : 3-2. Confirmez-vous ?"

4. Amadou peut :
   ├─ ✅ Confirmer → Résultat validé automatiquement
   └─ ❌ Contester → Upload sa propre capture + demande d'arbitrage
```

#### **Types de captures acceptées** :

```
Captures Valides :
├─ Screenshot du score final (dans le jeu)
├─ Photo de l'écran (si screenshot impossible)
└─ Vidéo courte (max 30 secondes) montrant le score

Captures Refusées :
├─ Images floues ou illisibles
├─ Captures sans date/heure visible
└─ Montages ou modifications
```

#### **Système de Vérification** :

```
Niveau 1 : Auto-validation (pas de litige)
├─ Les 2 joueurs soumettent le même résultat
└─ Résultat validé instantanément

Niveau 2 : Arbitrage automatique (litige simple)
├─ Un seul joueur a soumis une capture
└─ L'autre n'a pas répondu sous 24h
→ Résultat validé en faveur de celui qui a soumis

Niveau 3 : Arbitrage humain (litige complexe)
├─ Les 2 joueurs ont soumis des résultats différents
├─ Les 2 ont des captures contradictoires
└─ Un arbitre examine les preuves et tranche
```

**Délais** :
- ⏱️ **24h** pour soumettre le résultat après un match
- ⏱️ **24h** pour l'adversaire pour confirmer/contester
- ⏱️ **48h** pour l'arbitre pour trancher

**Sanctions** :
```
Si un joueur ne soumet jamais de preuve :
├─ 1ère fois : Avertissement
├─ 2ème fois : Perte du match par forfait
└─ 3ème fois : Exclusion du tournoi + bannissement temporaire
```

---

## 👮 Rôles et Gouvernance sur la Plateforme

MLM fonctionne avec une structure de gouvernance claire pour garantir l'équité et la qualité.

### 1. **Administrateurs (Admin)**

**Nombre** : 2-3 personnes

**Rôle** :
- 🔧 **Gestion globale** de la plateforme (paramètres, configurations)
- 👥 **Supervision** des modérateurs et arbitres
- 💰 **Gestion financière** (reversements, commissions)
- 📊 **Analyse des données** (statistiques, performance)
- 🚨 **Décisions finales** sur les cas complexes

**Pouvoirs** :
```
✅ Bannir définitivement un utilisateur
✅ Modifier les paramètres de la plateforme
✅ Accès à toutes les données
✅ Révoquer des modérateurs/arbitres
✅ Gérer les fonds de la plateforme
```

**Accès** : Compte admin avec authentification 2FA obligatoire

---

### 2. **Arbitres**

**Nombre** : 10-15 personnes (évolutif selon volume)

**Rôle** :
- ⚖️ **Résoudre les litiges** entre joueurs sur les résultats de matchs
- 🔍 **Examiner les preuves** (captures d'écran, vidéos)
- ✅ **Valider ou invalider** les résultats contestés
- 📝 **Documenter** les décisions pour historique

**Quand interviennent-ils ?**

```
Un arbitre intervient UNIQUEMENT si :
├─ L'organisateur du tournoi est CERTIFIÉ niveau 1+ (badge)
└─ Un litige est signalé ET :
    ├─ Les 2 joueurs ont soumis des résultats différents
    ├─ Une preuve est douteuse ou contradictoire
    └─ Un joueur accuse l'autre de triche

⚠️ IMPORTANT : Les arbitres N'INTERVIENNENT PAS sur :
└─ Tournois d'organisateurs non certifiés (niveau 0)
    → Ces tournois sont "à leurs risques"
```

**Processus d'arbitrage** :

```
1. Litige signalé → Ticket créé
2. Arbitre assigné automatiquement (disponibilité + charge)
3. Arbitre examine les preuves (24-48h max)
4. Arbitre prend une décision :
   ├─ Valide le résultat d'un joueur
   ├─ Déclare un match nul (si preuves insuffisantes)
   └─ Sanctionne un joueur (si triche avérée)
5. Décision finale + notification aux 2 joueurs
```

**Rémunération** :
- 💰 **Fixe** : 20,000 FCFA/mois (si actif)
- 💰 **Variable** : 500 FCFA par litige résolu (bonus)
- 🎯 **Performance** : Prime mensuelle selon qualité des décisions

**Qualifications requises** :
- ✅ Joueur expérimenté des jeux concernés (E-football, FC Mobile)
- ✅ Connaissance des règles MLM
- ✅ Disponibilité 2-3h/jour
- ✅ Impartialité et intégrité

---

### 3. **Modérateurs**

**Nombre** : 5-8 personnes

**Rôle** :
- 🛡️ **Modérer** les chats, forums, commentaires (supprimer spam, insultes)
- 📨 **Recueillir les plaintes** des utilisateurs
- 🚫 **Gérer les bannissements temporaires** (spam, comportement toxique)
- ✅ **Valider les inscriptions des organisateurs** (vérification identité, paiement)
- 📊 **Suivre** les signalements et créer des rapports pour les admins

**Processus de validation des organisateurs** :

```
Quand un utilisateur veut devenir organisateur certifié (niveau 1) :

1. Demande de certification reçue
2. Modérateur vérifie :
   ├─ Paiement de 50,000 FCFA effectué ?
   ├─ Identité vérifiée (carte d'identité, selfie) ?
   ├─ Pas de bannissement dans l'historique ?
   └─ Profil complété correctement ?

3. Modérateur décide :
   ├─ ✅ APPROUVÉ → Organisateur obtient badge niveau 1
   └─ ❌ REFUSÉ → Demande rejetée avec raison (+ remboursement si applicable)

4. Suivi post-certification :
   └─ Modérateur surveille les premiers tournois de l'organisateur
```

**Gestion des bannissements** :

```
Modérateurs peuvent bannir temporairement pour :
├─ Spam dans les chats (24h-7j)
├─ Insultes, harcèlement (7j-30j)
├─ Tentative de triche signalée (en attente d'arbitrage)
└─ Non-respect des règles (variable)

Admins peuvent bannir définitivement pour :
├─ Triche avérée et répétée
├─ Fraude financière
├─ Compte multi-comptes pour manipulation
└─ Violations graves des CGU
```

**Rémunération** :
- 💰 **Fixe** : 15,000 FCFA/mois (si actif)
- 💰 **Variable** : 200 FCFA par validation d'organisateur

---

### 4. **Utilisateurs**

Les utilisateurs ont différents niveaux de privilèges :

```
📊 Hiérarchie des Utilisateurs :

1. Joueur Standard (tout le monde)
   ├─ Participer aux tournois
   ├─ Chatter avec autres joueurs
   └─ Consulter classements

2. Organisateur Non-Certifié (gratuit, niveau 0)
   ├─ Créer des tournois GRATUITS uniquement
   ├─ Maximum 100 participants par tournoi
   └─ Pas d'intervention d'arbitres

3. Organisateur Certifié Niveau 1 (50,000 FCFA)
   ├─ Créer des tournois PAYANTS
   ├─ Maximum 200 participants
   ├─ Arbitres disponibles pour litiges
   └─ Commission de 50% sur les frais d'inscription

4. Organisateur Certifié Niveau 2 (1 tournoi réussi)
   ├─ Badge "Organisateur Confirmé"
   ├─ Maximum 500 participants
   ├─ Priorité sur l'assistance support
   └─ Visibilité augmentée sur la plateforme

5. Organisateur Certifié Niveau 3 (5 tournois réussis)
   ├─ Badge "Organisateur Elite"
   ├─ Participants illimités
   ├─ Support dédié 24/7
   ├─ Mise en avant sur page d'accueil
   └─ Commission augmentée à 55%
```

---

## 🏆 Système d'Organisation de Compétitions

### Types de Compétitions

Sur MLM, les organisateurs peuvent créer différents types de compétitions :

#### **1. Compétitions par Accessibilité**

```
🌐 OUVERTES (Public)
├─ Tout le monde peut s'inscrire
├─ Visibles sur la page d'accueil
├─ Recherchables par tous les utilisateurs
└─ Idéal pour : Tournois communautaires, événements publics

🔒 PRIVÉES (Invitation uniquement)
├─ Accessible uniquement avec un code d'invitation
├─ Invisible dans les recherches publiques
├─ Organisateur partage le code aux joueurs autorisés
└─ Idéal pour : Tournois entre amis, ligues privées, clans
```

**Exemple Compétition Privée** :
```
Amadou crée un tournoi pour son clan "DakarGamingCrew"

1. Amadou configure :
   ├─ Nom : "DGC Championship"
   ├─ Type : PRIVÉ
   └─ Code d'invitation : "DGC2024"

2. Amadou partage le code sur WhatsApp :
   "Yo les gars ! Tournoi MLM ce weekend.
    Code : DGC2024
    Lien : mlm.africa/join/DGC2024"

3. Seuls ceux qui ont le code peuvent s'inscrire
```

---

#### **2. Compétitions par Modèle Économique**

```
🆓 GRATUITES (Free)
├─ Aucun frais d'inscription
├─ Pas de prize pool monétaire
├─ Récompenses symboliques (badges, titres, points XP)
├─ Organisable par : Tous (même niveau 0)
└─ Idéal pour : S'amuser, tester, construire une communauté

💰 PAYANTES (Cash Prize)
├─ Frais d'inscription définis par l'organisateur
├─ Prize pool monétaire (60% des frais collectés)
├─ Commission organisateur (30%) + plateforme (10%)
├─ Organisable par : Organisateurs certifiés niveau 1+ uniquement
└─ Idéal pour : Compétitions sérieuses, gagner de l'argent
```

---

### Certification des Organisateurs : Système de Badges

Pour garantir la qualité et éviter les arnaques, MLM utilise un **système de certification par badges** pour les organisateurs.

#### **Badge Niveau 0 : Organisateur Non-Certifié** 🆓

**Conditions** :
- ✅ Inscription gratuite (0 FCFA)
- ✅ Aucune vérification requise

**Privilèges** :
```
✅ Créer des tournois GRATUITS uniquement
✅ Maximum 100 participants par tournoi
✅ Accès au chat et communication basique
✅ Statistiques de base

❌ PAS de tournois payants
❌ PAS d'intervention d'arbitres
❌ PAS de priorité support
❌ PAS de badge visible sur profil
```

**Cas d'usage** :
- Tester la plateforme
- Organiser des mini-tournois entre amis
- Construire une réputation avant de passer certifié

---

#### **Badge Niveau 1 : Organisateur Certifié** 🏅

**Conditions** :
- 💰 **Payer 50,000 FCFA** (frais de certification unique)
- ✅ **Vérification d'identité** (carte d'identité + selfie)
- ✅ **Validation par un modérateur** (48h max)

**Processus de certification** :
```
1. Utilisateur demande la certification niveau 1
2. Paiement de 50,000 FCFA via Mobile Money
3. Upload documents :
   ├─ Carte d'identité (CNI/passeport)
   ├─ Selfie avec la carte
   └─ Preuve de paiement

4. Modérateur examine (24-48h)
5. Décision :
   ├─ ✅ APPROUVÉ → Badge niveau 1 attribué
   └─ ❌ REFUSÉ → Remboursement + raison du refus
```

**Privilèges** :
```
✅ Créer des tournois PAYANTS
✅ Maximum 200 participants par tournoi
✅ Commission de 50% sur frais d'inscription
✅ Arbitres disponibles pour résoudre litiges
✅ Badge "Certifié" visible sur profil
✅ Support standard (72h de réponse)
✅ Tournois privés ET publics
```

**Obligations** :
```
📋 Respecter les règles MLM
📋 Distribuer les prize pools dans les 48h après tournoi
📋 Répondre aux participants sous 24h
📋 Soumettre les résultats finaux correctement
```

---

#### **Badge Niveau 2 : Organisateur Confirmé** 🥈

**Conditions** :
- ✅ **Avoir un badge niveau 1**
- ✅ **Organiser 1 tournoi payant avec succès**
  - Minimum 16 participants
  - Aucun litige non résolu
  - Tous les prize pools distribués à temps
  - Note de satisfaction ≥ 4/5 par les participants

**Progression automatique** :
```
Dès qu'un organisateur niveau 1 termine son 1er tournoi :
└─ Système vérifie automatiquement les critères
    ├─ ✅ Tous les critères OK → Badge niveau 2 attribué
    └─ ❌ Critères non respectés → Reste niveau 1
```

**Privilèges supplémentaires** :
```
✅ Maximum 500 participants par tournoi
✅ Badge "Confirmé" visible (🥈)
✅ Priorité dans les résultats de recherche
✅ Support prioritaire (48h de réponse)
✅ Statistiques avancées (taux de participation, satisfaction)
✅ Peut créer des tournois sur plusieurs jeux simultanément
```

---

#### **Badge Niveau 3 : Organisateur Elite** 🥇

**Conditions** :
- ✅ **Avoir un badge niveau 2**
- ✅ **Organiser 5 tournois payants avec succès**
  - Chaque tournoi : minimum 32 participants
  - Note moyenne ≥ 4.5/5
  - Aucun bannissement ou sanction
  - Taux de litiges < 5%

**Privilèges supplémentaires** :
```
✅ Participants ILLIMITÉS par tournoi
✅ Badge "Elite" visible (🥇)
✅ Mise en avant sur page d'accueil MLM
✅ Support dédié 24/7 (12h de réponse max)
✅ Commission augmentée à 55% (au lieu de 50%)
✅ Accès aux statistiques de la plateforme
✅ Peut organiser des ligues multi-éditions
✅ Peut demander des partenariats avec marques
✅ Profil vérifié avec badge officiel
```

**Avantages économiques** :
```
Exemple : Tournoi de 100 joueurs × 4 MLC = 400 MLC collectés

Organisateur Niveau 1 (50%) :
└─ 200 MLC = 100,000 FCFA

Organisateur Niveau 3 (55%) :
└─ 220 MLC = 110,000 FCFA

Différence : +20 MLC = +10,000 FCFA par tournoi
```

---

### Conditions et Privilèges par Niveau

**Tableau Récapitulatif** :

| Critère | Niveau 0 | Niveau 1 | Niveau 2 | Niveau 3 |
|---------|----------|----------|----------|----------|
| **Frais certification** | Gratuit | 50,000 FCFA | Auto | Auto |
| **Tournois payants** | ❌ | ✅ | ✅ | ✅ |
| **Max participants** | 100 | 200 | 500 | Illimité |
| **Arbitres** | ❌ | ✅ | ✅ | ✅ |
| **Commission** | 0% | 50% | 50% | 55% |
| **Support** | 7j | 72h | 48h | 24h (dédié) |
| **Badge visible** | ❌ | 🏅 Certifié | 🥈 Confirmé | 🥇 Elite |
| **Mise en avant** | ❌ | ❌ | Priorité recherche | Page d'accueil |
| **Multi-jeux** | ❌ | ❌ | ✅ | ✅ |
| **Ligues éditions** | ❌ | ❌ | ❌ | ✅ |

---

### Pourquoi ce Système de Badges ?

**Avantages pour MLM** :
- 🛡️ **Qualité garantie** : Seuls les organisateurs sérieux peuvent créer des tournois payants
- 💰 **Revenus** : 50,000 FCFA par certification niveau 1
- ⚖️ **Arbitrage efficace** : Réservé aux tournois certifiés = moins de litiges
- 📈 **Progression** : Encourage les organisateurs à améliorer leur qualité

**Avantages pour les Joueurs** :
- ✅ **Confiance** : Badge visible = organisateur fiable
- 💰 **Sécurité** : Prize pools garantis (organisateurs certifiés)
- ⚖️ **Support** : Arbitres disponibles si problème
- 🏆 **Qualité** : Tournois mieux organisés

**Avantages pour les Organisateurs** :
- 📈 **Crédibilité** : Badge = confiance = plus de participants
- 💰 **Revenus** : Commission attractive (50-55%)
- 🚀 **Visibilité** : Mise en avant selon niveau
- 🎯 **Évolution** : Objectifs clairs pour progresser

---

## 💰 Modèle Économique

### Comment MLM Gagne de l'Argent ?

#### 1. **Commission sur Tournois Payants** (Revenu Principal)

**Exemple concret** :

Tournoi de **8 joueurs** × **4 coins** (2,000 FCFA) = **32 coins collectés** (16,000 FCFA)

```
Répartition :
├─ 35% minimum → Prize Pool (11.2 coins = 5,600 FCFA) → Redistribué aux joueurs
├─ 50% → Commission Organisateur (16 coins = 8,000 FCFA)
└─ 15% → Commission MLM (4.8 coins = 2,400 FCFA)
```

**Note** : L'organisateur doit payer **50,000 FCFA** en frais d'inscription unique pour pouvoir créer des tournois payants.

**Projection Conservatrice** :

Hypothèses (Année 1) :
- **10,000 utilisateurs actifs** en Afrique de l'Ouest
- **2 tournois payants** par utilisateur/mois en moyenne
- **Frais moyen** : 3 coins (1,500 FCFA) par inscription
- **Commission MLM** : 15%
- **200 organisateurs** payant 50,000 FCFA

**Calcul** :
```
Revenus tournois :
10,000 users × 2 tournois/mois × 3 coins × 15% = 9,000 coins/mois
= 450,000 FCFA/mois
= 5,400,000 FCFA/an (~8,200 EUR/an)

Revenus inscription organisateurs :
200 organisateurs × 50,000 FCFA = 10,000,000 FCFA/an (~15,250 EUR/an)

TOTAL AN 1 : 15,400,000 FCFA/an (~23,450 EUR/an)
```

**Projection Optimiste (Année 2)** :
- **50,000 utilisateurs**
- **3 tournois/mois**
- Frais moyen : 4 coins (2,000 FCFA)
- **1,000 organisateurs**

```
Revenus tournois :
50,000 × 3 × 4 × 15% = 90,000 coins/mois
= 45,000,000 FCFA/mois
= 540,000,000 FCFA/an (~823,000 EUR/an)

Revenus inscription organisateurs :
1,000 × 50,000 FCFA = 50,000,000 FCFA/an (~76,000 EUR/an)

TOTAL AN 2 : 590,000,000 FCFA/an (~899,000 EUR/an)
```

---

#### 2. **Publicités & Sponsoring**

**Emplacements publicitaires** :
- Bannières dans l'app (entre les matchs)
- Vidéos récompensées (regarder une pub = +5 coins gratuits)
- Sponsoring de divisions (ex: "Ligue 1 by Orange")

**Potentiel** :
- **1,000 utilisateurs actifs** = ~500 EUR/mois (publicités)
- **10,000 utilisateurs** = ~5,000 EUR/mois

---

#### 3. **Frais d'Accès aux Éditions de Divisions** (Récurrent)

Pour rejoindre une édition (3 mois) :
- **Édition 1** : 4 MLC (2,000 FCFA)
- **Édition 2** : 6 MLC (3,000 FCFA)
- **Édition 3** : 8 MLC (4,000 FCFA)
- **Édition 4+** : Progressif (paramétrable)

**Exemple de calcul Édition 1** :
Si **100 joueurs** s'inscrivent à l'Édition 1 (4 MLC chacun) :
```
100 × 4 MLC = 400 MLC collectés

Distribution :
├─ 220 MLC (55%) → Prize Pools (4 champions)
│   ├─ Champion D4 : 220 × 45.5% = 100 MLC
│   ├─ Champion D3 : 220 × 27.3% = 60 MLC
│   ├─ Champion D2 : 220 × 18.2% = 40 MLC
│   └─ Champion D1 : 220 × 9.0% = 20 MLC
└─ 180 MLC (45%) → Plateforme MLM

Revenus plateforme : 180 MLC = 90,000 FCFA par édition
```

**Projection annuelle (4 éditions/an)** :
```
Si moyenne de 100 joueurs par édition :
Revenus par édition : 100 × 4 MLC = 400 MLC
Plateforme (45%) : 400 × 45% = 180 MLC par édition
4 éditions × 180 MLC = 720 MLC/an
= 360,000 FCFA/an (~550 EUR/an)

Si moyenne de 500 joueurs par édition :
Revenus par édition : 500 × 4 MLC = 2,000 MLC
Plateforme (45%) : 2,000 × 45% = 900 MLC par édition
4 éditions × 900 MLC = 3,600 MLC/an
= 1,800,000 FCFA/an (~2,740 EUR/an)

Si moyenne de 500 joueurs ET frais augmentent (Édition 2 = 6 MLC) :
Revenus par édition : 500 × 6 MLC = 3,000 MLC
Plateforme (45%) : 3,000 × 45% = 1,350 MLC par édition
4 éditions × 1,350 MLC = 5,400 MLC/an
= 2,700,000 FCFA/an (~4,110 EUR/an)
```

**💡 Avantage du système en %** :
- Les revenus de la plateforme évoluent automatiquement avec :
  - Le nombre de participants
  - L'augmentation des frais d'inscription
- Pas besoin de recalculer les montants manuellement

---

#### 4. **Fonctionnalités Premium** (Future)

- **Abonnement Pro** : 10 coins/mois (5,000 FCFA)
  - Pas de publicités
  - Statistiques avancées
  - Badge exclusif "MLM Pro"
  - Accès prioritaire aux gros tournois

- **Organisateur Pro** : 20 coins/mois (10,000 FCFA)
  - Créer des tournois de 32+ joueurs
  - Outils avancés (bracket custom, arbitres multiples)
  - Branding personnalisé

---

### 💵 Projection de Revenus (3 ans)

| Année | Utilisateurs | Revenus/Mois | Revenus/An | Détail |
|-------|--------------|--------------|------------|--------|
| **An 1** | 10,000 | 1,500,000 FCFA | 18M FCFA (~27K EUR) | Tournois + Organisateurs + Pub |
| **An 2** | 50,000 | 50,000,000 FCFA | 600M FCFA (~914K EUR) | Croissance organique + Divisions |
| **An 3** | 150,000 | 180,000,000 FCFA | 2.16Md FCFA (~3.3M EUR) | Expansion régionale + Premium |

---

## 🚀 Pourquoi MLM Va Réussir

### 1️⃣ **Le Timing Est Parfait**

**3 Tendances Convergent Maintenant** :

1. **📱 Explosion du Mobile** : 500M+ smartphones en Afrique (2024) [Source: GSMA Intelligence, 2024]
2. **💳 Mobile Money Mature** : 70% de la population utilise Orange/MTN Money [Source: GSMA State of the Industry Report on Mobile Money, 2023]
3. **🎮 Gaming en Croissance** : +12% par an (vs +8% mondial) [Source: Newzoo, PwC Entertainment & Media Outlook 2023]

**Il y a 5 ans** : Trop tôt (pas assez de smartphones)
**Dans 5 ans** : Trop tard (les gros acteurs vont arriver)
**Aujourd'hui** : ⭐ **Fenêtre d'opportunité**

---

### 2️⃣ **First-Mover Advantage en Afrique**

**Les grands acteurs (Faceit, ESL, etc.)** :
- ❌ Ne supportent PAS le mobile money
- ❌ Pas d'interface en français
- ❌ Prix en USD (barrière psychologique)
- ❌ Pas de focus Afrique

**MLM** :
- ✅ Mobile money natif (Orange, MTN, Moov)
- ✅ Interface français + langues locales (futur)
- ✅ Prix en FCFA (psychologiquement accessible)
- ✅ 100% focus Afrique

**Résultat** : **Aucun concurrent direct** pour les 2-3 prochaines années.

---

### 3️⃣ **Viralité Intégrée**

**Le partage est dans l'ADN de l'app** :

```
Amadou crée un tournoi
   ↓
Partage le lien sur WhatsApp
   ↓
8 amis s'inscrivent (dont 5 nouveaux utilisateurs)
   ↓
Ces 5 nouveaux créent leurs propres tournois
   ↓
Chacun invite 8 amis
   ↓
40 nouveaux utilisateurs
```

**Coefficient viral estimé** : 1 utilisateur → 3-5 nouveaux utilisateurs en 1 mois

**Canaux de croissance organique** :
- WhatsApp (partage de liens)
- Bouche-à-oreille dans les groupes gaming
- TikTok/YouTube (clips de victoires)
- Facebook Gaming Groups

**Coût d'acquisition** : **0 FCFA** (100% organique au début)

---

### 4️⃣ **Network Effect Puissant**

Plus il y a de joueurs → Plus c'est intéressant de rejoindre :

- Plus de tournois disponibles
- Plus de diversité de niveaux
- Prize pools plus gros
- Classement MLM Rank plus crédible

**Effet boule de neige** : Après **10,000 utilisateurs**, la croissance s'accélère seule.

---

## 🌍 Stratégie de Lancement

### Phase 1 : Pilot au Sénégal (Mois 1-3)

**Pourquoi le Sénégal ?**
- Forte pénétration Orange Money (80% de la population) [Source: Orange Money Sénégal, Rapport Annuel 2023]
- Communauté gaming très active (Dakar Gaming Week)
- Marché francophone (plus facile pour débuter)
- Taille optimale pour tester (17M habitants) [Source: Banque Mondiale, Population Data 2024]

**Objectifs** :
- ✅ **500 utilisateurs** en 1 mois
- ✅ **2,000 utilisateurs** en 3 mois
- ✅ **50+ tournois** organisés par semaine
- ✅ Feedback utilisateurs pour améliorer l'app

**Stratégie Marketing** :
1. **Partenariat micro-influenceurs** : 10 YouTubers gaming sénégalais (5K-20K abos)
2. **Tournoi de lancement** : Prize Pool de 100,000 FCFA (soit 20 coins - financé par MLM)
3. **Bouche-à-oreille** : Offrir 2 coins gratuits (1,000 FCFA) pour chaque ami parrainé

**Budget Phase 1** : 500,000 FCFA (~760 EUR)

---

### Phase 2 : Expansion Afrique de l'Ouest (Mois 4-12)

**Pays cibles** :
- 🇨🇮 Côte d'Ivoire (Orange Money, 28M habitants) [Source: Banque Mondiale, 2024]
- 🇲🇱 Mali (Orange Money, 21M habitants) [Source: Banque Mondiale, 2024]
- 🇬🇭 Ghana (MTN Money, 32M habitants) [Source: Banque Mondiale, 2024]
- 🇳🇬 Nigeria (MTN Money, 216M habitants) [Source: Banque Mondiale, 2024] - Phase 2B

**Objectifs Année 1** :
- 📱 **10,000 utilisateurs** actifs
- 🏆 **500+ tournois** par semaine
- 💰 **1,500,000 FCFA/mois** de revenus

---

### Phase 3 : Pan-Africain (Année 2+)

Expansion à :
- Afrique Centrale (Cameroun, RDC)
- Afrique de l'Est (Kenya, Tanzanie)
- Afrique Australe (Afrique du Sud)

---

## 💪 Avantages Compétitifs

### 1. **Expertise du Marché Local**

Nous **comprenons** les joueurs africains :
- Leurs budgets (500-2,000 FCFA par tournoi, pas 10 EUR)
- Leurs moyens de paiement (mobile money, pas carte bancaire)
- Leurs horaires (soirs + week-ends)
- Leurs références culturelles (football est roi)

**Les plateformes internationales ne comprennent pas ça.**

---

### 2. **Technologie Adaptée**

- **App légère** : Fonctionne sur des téléphones à 50 EUR
- **Optimisée pour 3G/4G** : Pas besoin de fibre
- **Mode offline** : Peut déclarer un score sans connexion (sync plus tard)
- **Multilingue** : Français, Wolof, Bambara (future)

---

### 3. **Communauté d'Abord**

MLM n'est pas juste une app, c'est un **mouvement** :
- Discord/WhatsApp communautaire
- Événements IRL (LAN parties)
- Partenariats avec les salles de jeux
- Sponsoring de joueurs prometteurs

---

## 📈 Opportunités de Croissance

### 1️⃣ **Expansion Multi-Jeux**

Après E-football/FC Mobile, ajouter :
- **PUBG Mobile** (énorme en Afrique)
- **Call of Duty Mobile**
- **Clash Royale**
- **Mobile Legends**

**Potentiel** : **×5 la base utilisateurs**

---

### 2️⃣ **Partenariats avec Télécoms**

**Orange/MTN/Moov** pourraient être intéressés :
- Bundler MLM Coins avec les forfaits data
- Ex: "Recharge 5,000 FCFA → Reçois 10 MLM Coins gratuits"
- Co-branding des divisions ("Division Orange", "MTN League")

**Avantage** : Acquisition de **millions** d'utilisateurs instantanément

---

### 3️⃣ **Tournois avec Marques**

**Coca-Cola, Nike, Adidas** cherchent à toucher les jeunes africains :
- Sponsoriser des gros tournois (Prize Pool 500,000 FCFA soit 100 coins)
- Visibilité dans l'app
- Données démographiques des joueurs

---

### 4️⃣ **Licences Officielles**

Partenariats avec **Konami (E-football)** ou **EA Sports (FC Mobile)** :
- Tournois officiels
- Qualifications pour compétitions internationales
- Légitimité accrue

---

## 💡 Pourquoi Investir dans MLM ?

### Pour un Investisseur

**Ticket d'Entrée Faible, Potentiel Énorme** :
- **Investissement initial** : 10,000 - 20,000 EUR
  - Développement app (déjà en cours)
  - Marketing Phase 1
  - Opérations 6 mois
- **Valorisation potentielle** (3 ans) : **1-5M EUR**
  - 150,000 utilisateurs × 10 EUR de LTV = 1.5M EUR
  - Multiple de 3-5x sur le CA

**Retour Rapide** :
- Rentabilité possible dès **Mois 6** (avec 2,000 users payants)
- Pas de dette
- Modèle scalable (pas de coûts variables élevés)

---

### Pour un Partenaire Stratégique (Télécom, Gaming)

**Distribution Instantanée** :
- Accès à votre base clients (ex: 10M abonnés Orange)
- Synergies data/forfaits
- Image "innovant & jeune"

**Co-développement** :
- Tournois exclusifs co-brandés
- Intégration API paiement
- Partage de revenus (70/30)

---

## 🎯 Vision à Long Terme

### Année 1 : **Devenir la plateforme #1 en Afrique de l'Ouest**
- 10,000 utilisateurs
- 4 pays (Sénégal, CI, Mali, Ghana)

### Année 3 : **Expansion Pan-Africaine**
- 150,000 utilisateurs
- 15 pays africains
- Multi-jeux (E-football, PUBG, Call of Duty Mobile)

### Année 5 : **Le "Faceit Africain"**
- 1M+ utilisateurs
- Tournois officiels avec Konami/EA
- Ligues professionnelles
- Joueurs pro sponsorisés

**Mission ultime** : **Prouver que les gamers africains sont aussi bons que les autres, et leur permettre de vivre de leur passion.**

---

## 🤝 Appel à l'Action

### Vous Êtes...

**🎯 Un Investisseur ?**
- Contactez-nous pour le pitch deck complet
- Rencontrons-nous pour discuter du potentiel

**🤝 Un Partenaire Potentiel (Télécom, Gaming) ?**
- Explorons des synergies win-win
- Pilotes co-brandés

**💼 Un Talent (Dev, Marketing, Ops) ?**
- Rejoignez une aventure à fort impact
- Soyez parmi les premiers

**🎮 Un Joueur/Organisateur ?**
- Inscrivez-vous à la beta (lancement Janvier 2025)
- Invitez vos amis

---

## 📞 Contact

**Email** : contact@mlm-gaming.com
**WhatsApp** : +221 XX XXX XX XX
**Site Web** : www.mlm-gaming.com (à venir)

**Réseaux Sociaux** :
- Discord : discord.gg/mlm-africa
- Instagram : @mlm_gaming_africa
- TikTok : @mlm_gaming

---

## 📎 Annexes

### Ressources Complémentaires

1. **[Cahier des Charges Technique](./cahier_de_charge.md)** - Spécifications détaillées
2. **[User Stories](./user_stories.md)** - Parcours utilisateurs complets
3. **[Architecture Technique](./architecture_technique.md)** - Diagrammes et API
4. **[Pitch Deck](./pitch_deck.pdf)** - Présentation investisseurs (à venir)

---

**🌍 Mobile League Manager - L'avenir du gaming compétitif en Afrique commence ici.**

**🚀 Rejoignez le mouvement.**

---

*Document v1.0 - Décembre 2024*
*Pour toute question : contact@mlm-gaming.com*
