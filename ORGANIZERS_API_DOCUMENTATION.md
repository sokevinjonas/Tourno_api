# 📚 Documentation API - Système d'Organisateurs

## Vue d'ensemble

Le système d'organisateurs permet aux joueurs de découvrir, suivre et interagir avec les organisateurs de tournois. Chaque organisateur possède un profil enrichi avec badges, bio, liens sociaux, statistiques et système de vérification.

## 🔑 Concepts clés

### Types de badges
- `certified` - Organisateur certifié (officiel) - **Coût: 50 pièces MLM** - Attribué automatiquement lors de l'upgrade
- `verified` - Organisateur vérifié - **Coût: 200 pièces MLM** - Requiert vérification de documents et contrat signé
- `partner` - Partenaire de la plateforme - **Coût: 200 pièces MLM** - Requiert vérification de documents et contrat signé
- `null` - Aucun badge

**Notes importantes:**
- Les badges `verified` et `partner` nécessitent tous deux les mêmes documents (identité + contrat). La différence réside dans le niveau de partenariat et les avantages associés.
- Le badge `certified` coûte 50 pièces MLM, déduites automatiquement lors de l'upgrade vers organisateur.
- Les demandes de vérification pour `verified` ou `partner` coûtent 200 pièces MLM, payées lors de la soumission.
- **En cas de rejet**: Les 200 pièces sont **automatiquement remboursées** dans le wallet.

### Statuts de vérification
- `null` - Aucune demande de vérification
- `attente` - Demande en attente de validation
- `valider` - Demande validée (badge attribué)
- `rejeter` - Demande rejetée

### Organisateur en vedette (`is_featured`)
Les organisateurs marqués comme "featured" apparaissent en priorité dans les listes et sur la page d'accueil.

---

## 📡 Endpoints API

### 1. Obtenir la liste des organisateurs

**Endpoint:** `GET /api/organizers`

**Authentification:** Non requise (Public)

**Query Parameters:**
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `featured` | boolean | Non | Filtrer uniquement les organisateurs en vedette |
| `badge` | string | Non | Filtrer par type de badge (`certified`, `verified`, `partner`) |
| `sort` | string | Non | Trier par nombre de followers (`followers`) ou par date (`latest`) |

**Exemples de requêtes:**
```typescript
// Tous les organisateurs
GET /api/organizers

// Organisateurs en vedette seulement
GET /api/organizers?featured=true

// Organisateurs certifiés
GET /api/organizers?badge=certified

// Triés par nombre de followers
GET /api/organizers?sort=followers
```

**Réponse (200 OK):**
```json
{
  "organizers": [
    {
      "id": 10,
      "name": "Tourno Official",
      "badge": "certified",
      "tournaments": 42,
      "followers": 12500,
      "avatar": "T",
      "is_featured": true,
      "bio": "Organisation officielle de tournois MLM...",
      "social_links": {
        "twitter": "https://twitter.com/tourno_mlm",
        "discord": "https://discord.gg/tourno"
      }
    }
  ],
  "total": 1
}
```

---

### 2. Obtenir les détails d'un organisateur

**Endpoint:** `GET /api/organizers/{id}`

**Authentification:** Non requise (Public)

**Réponse (200 OK):**
```json
{
  "organizer": {
    "id": 10,
    "name": "Tourno Official",
    "email": "organizer1@mlm.com",
    "badge": "certified",
    "tournaments": 42,
    "followers": 12500,
    "avatar": "T",
    "is_featured": true,
    "bio": "Organisation officielle de tournois MLM...",
    "social_links": {
      "twitter": "https://twitter.com/tourno_mlm",
      "discord": "https://discord.gg/tourno"
    },
    "recent_tournaments": [...]
  }
}
```

---

### 3. Vérifier si l'utilisateur connecté est organisateur

**Endpoint:** `GET /api/organizers/check-if-organizer`

**Authentification:** ✅ Requise (Bearer Token)

**Exemple de requête:**
```typescript
GET /api/organizers/check-if-organizer
Authorization: Bearer {token}
```

**Réponse (200 OK) - Organisateur avec badge verified:**
```json
{
  "is_organizer": true,
  "role": "organizer",
  "badge": "verified",
  "status": "valider"
}
```

**Réponse (200 OK) - Organisateur avec demande en attente:**
```json
{
  "is_organizer": true,
  "role": "organizer",
  "badge": null,
  "status": "attente"
}
```

**Réponse (200 OK) - Joueur:**
```json
{
  "is_organizer": false,
  "role": "player",
  "badge": null,
  "status": null
}
```

---

### 4. Devenir organisateur (Upgrade)

**Endpoint:** `POST /api/organizers/upgrade`

**Authentification:** ✅ Requise (Bearer Token)

**Corps de la requête:** Aucun

**Coût:** 50 pièces MLM (déduites automatiquement du wallet)

**Réponse (200 OK):**
```json
{
  "message": "User upgraded to organizer successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "organizer"
  },
  "organizer_profile": {
    "id": 1,
    "display_name": "John Doe",
    "avatar_initial": "J",
    "badge": "certified",
    "is_featured": false
  },
  "transaction": {
    "amount": 50.00,
    "new_balance": 50.00
  }
}
```

**Réponse (400 Bad Request) - Déjà organisateur:**
```json
{
  "message": "User is already an organizer"
}
```

**Réponse (400 Bad Request) - Solde insuffisant:**
```json
{
  "message": "Insufficient balance. You need 50 MLM pieces to become an organizer.",
  "required": 50.00,
  "current_balance": 25.00
}
```

---

### 5. Soumettre une demande de vérification

**Endpoint:** `POST /api/organizers/verification/submit`

**Authentification:** ✅ Requise (Bearer Token) - Organisateurs uniquement

**Coût:** 200 pièces MLM (déduites automatiquement du wallet)

**Content-Type:** `multipart/form-data` (Upload de fichiers)

**Corps de la requête (FormData):**

```text
badge_type: "verified"
nature_document: "cnib"
doc_recto: [File] (image/jpeg ou image/png, max 5 MB)
doc_verso: [File] (image/jpeg ou image/png, max 5 MB)
contrat_signer: [File] (application/pdf, max 10 MB)
```

**Validation:**

| Champ | Type | Valeurs/Contraintes |
|-------|------|-------------------|
| `badge_type` | enum | `verified`, `partner` |
| `nature_document` | enum | `cnib`, `permis`, `passport` |
| `doc_recto` | file | Image JPEG/PNG, max 5120 KB (5 MB) |
| `doc_verso` | file | Image JPEG/PNG, max 5120 KB (5 MB) |
| `contrat_signer` | file | PDF, max 10240 KB (10 MB) |

**Réponse (200 OK):**
```json
{
  "message": "Verification request submitted successfully",
  "verification": {
    "nature_document": "cnib",
    "status": "attente",
    "requested_badge": "verified"
  },
  "transaction": {
    "amount": 200.00,
    "new_balance": 300.00
  }
}
```

**Réponse (400 Bad Request) - Demande existante:**
```json
{
  "message": "You already have a pending verification request"
}
```

**Réponse (400 Bad Request) - Solde insuffisant:**
```json
{
  "message": "Insufficient balance. You need 200 MLM pieces to submit a verification request.",
  "required": 200.00,
  "current_balance": 150.00
}
```

**Réponse (403 Forbidden):**
```json
{
  "message": "Only organizers can submit verification requests"
}
```

---

### 6. Obtenir les demandes en attente (Modérateurs)

**Endpoint:** `GET /api/organizers/verification/pending`

**Authentification:** ✅ Requise (Bearer Token) - Modérateurs/Admin uniquement

**Réponse (200 OK):**
```json
{
  "verifications": [
    {
      "id": 5,
      "organizer": {
        "id": 15,
        "name": "Elite Gaming",
        "email": "elite@gaming.com"
      },
      "nature_document": "cnib",
      "doc_recto": "https://storage.example.com/documents/cnib_recto.jpg",
      "doc_verso": "https://storage.example.com/documents/cnib_verso.jpg",
      "contrat_signer": "https://storage.example.com/contracts/contract.pdf",
      "status": "attente",
      "rejection_reason": null,
      "processed_by": null,
      "submitted_at": "2025-12-20 23:45:00"
    }
  ],
  "total": 1
}
```

**Réponse (403 Forbidden):**
```json
{
  "message": "Unauthorized. Moderators only."
}
```

---

### 7. Valider une demande de vérification (Modérateurs)

**Endpoint:** `POST /api/organizers/verification/{id}/validate`

**Authentification:** ✅ Requise (Bearer Token) - Modérateurs/Admin uniquement

**Corps de la requête:**
```json
{
  "badge": "verified"
}
```

**Validation:**
| Champ | Type | Valeurs acceptées |
|-------|------|-------------------|
| `badge` | enum | `verified`, `partner` |

**Réponse (200 OK):**
```json
{
  "message": "Verification request validated successfully",
  "organizer_profile": {
    "id": 5,
    "display_name": "Elite Gaming",
    "badge": "verified",
    "status": "valider",
    "processed_by": {
      "id": 2,
      "name": "Moderator John"
    }
  }
}
```

**Réponse (404 Not Found):**
```json
{
  "message": "Organizer profile not found"
}
```

---

### 8. Rejeter une demande de vérification (Modérateurs)

**Endpoint:** `POST /api/organizers/verification/{id}/reject`

**Authentification:** ✅ Requise (Bearer Token) - Modérateurs/Admin uniquement

**Remboursement automatique:** Les 200 pièces MLM sont automatiquement remboursées dans le wallet de l'organisateur

**Corps de la requête:**
```json
{
  "rejection_reason": "Les documents fournis ne sont pas valides ou sont expirés."
}
```

**Validation:**
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `rejection_reason` | string | Non | Raison du rejet (max 500 caractères) |

**Réponse (200 OK):**
```json
{
  "message": "Verification request rejected",
  "rejection_reason": "Les documents fournis ne sont pas valides ou sont expirés.",
  "refund": {
    "amount": 200.00,
    "new_balance": 500.00
  },
  "processed_by": {
    "id": 2,
    "name": "Moderator John"
  }
}
```

---

### 9. Suivre/Ne plus suivre un organisateur

**Endpoint:** `POST /api/organizers/{id}/follow`

**Authentification:** ✅ Requise (Bearer Token)

**Corps de la requête:** Aucun

**Réponse - Abonnement réussi (200 OK):**
```json
{
  "message": "Organizer followed successfully",
  "is_following": true,
  "followers_count": 12501
}
```

**Réponse - Désabonnement réussi (200 OK):**
```json
{
  "message": "Organizer unfollowed successfully",
  "is_following": false,
  "followers_count": 12500
}
```

**Réponse (400 Bad Request) - Auto-follow:**
```json
{
  "message": "You cannot follow yourself"
}
```

---

### 10. Vérifier si l'utilisateur suit un organisateur

**Endpoint:** `GET /api/organizers/{id}/check-following`

**Authentification:** ✅ Requise (Bearer Token)

**Réponse (200 OK):**
```json
{
  "is_following": true
}
```

---

### 11. Obtenir mes abonnements (organisateurs suivis)

**Endpoint:** `GET /api/organizers/my/following`

**Authentification:** ✅ Requise (Bearer Token)

**Réponse (200 OK):**
```json
{
  "following": [
    {
      "id": 10,
      "name": "Tourno Official",
      "badge": "certified",
      "tournaments": 42,
      "followers": 12500,
      "avatar": "T",
      "is_featured": true
    }
  ],
  "total": 1
}
```

---

## 📊 Types TypeScript complets

```typescript
// types/organizer.ts

export type BadgeType = 'certified' | 'verified' | 'partner' | null;
export type VerificationStatus = 'attente' | 'valider' | 'rejeter' | null;
export type DocumentType = 'cnib' | 'permis' | 'passport';

export interface SocialLinks {
  twitter?: string;
  discord?: string;
  [key: string]: string | undefined;
}

export interface Organizer {
  id: number;
  name: string;
  badge: BadgeType;
  tournaments: number;
  followers: number;
  avatar: string;
  is_featured: boolean;
  bio?: string;
  social_links?: SocialLinks;
}

export interface OrganizerDetails extends Organizer {
  email: string;
  recent_tournaments: Tournament[];
}

export interface OrganizerCheckResponse {
  is_organizer: boolean;
  role: 'player' | 'organizer' | 'moderator' | 'admin';
  badge: BadgeType;
  status: VerificationStatus;
}

export interface UpgradeToOrganizerResponse {
  message: string;
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
  };
  organizer_profile: {
    id: number;
    display_name: string;
    avatar_initial: string;
    is_featured: boolean;
  };
}

export interface VerificationRequest {
  badge_type: 'verified' | 'partner';
  nature_document: DocumentType;
  doc_recto: File;
  doc_verso: File;
  contrat_signer: File;
}

export interface VerificationResponse {
  message: string;
  verification: {
    nature_document: DocumentType;
    status: string;
    requested_badge: string;
  };
}

export interface PendingVerification {
  id: number;
  organizer: {
    id: number;
    name: string;
    email: string;
  };
  nature_document: DocumentType;
  doc_recto: string;
  doc_verso: string;
  contrat_signer: string;
  status: string;
  rejection_reason: string | null;
  processed_by: {
    id: number;
    name: string;
  } | null;
  submitted_at: string;
}

export interface ValidateVerificationRequest {
  badge: 'verified' | 'partner';
}

export interface RejectVerificationRequest {
  rejection_reason?: string;
}

export interface FollowResponse {
  message: string;
  is_following: boolean;
  followers_count: number;
}

export interface FollowingStatus {
  is_following: boolean;
}

export interface MyFollowingResponse {
  following: Organizer[];
  total: number;
}
```

---

## 🎨 Exemples d'intégration Frontend

### Vérifier le statut d'organisateur

```typescript
const checkOrganizerStatus = async (token: string): Promise<OrganizerCheckResponse> => {
  const response = await fetch('/api/organizers/check-if-organizer', {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });

  return response.json();
};

// Utilisation dans un composant React
const OrganizerBadge: React.FC = () => {
  const [status, setStatus] = useState<OrganizerCheckResponse | null>(null);
  const { token } = useAuth();

  useEffect(() => {
    checkOrganizerStatus(token).then(setStatus);
  }, [token]);

  if (!status?.is_organizer) return null;

  return (
    <div>
      {status.badge && <Badge type={status.badge} />}
      {status.status === 'attente' && (
        <span>Vérification en attente...</span>
      )}
      {status.status === 'rejeter' && (
        <span>Demande rejetée</span>
      )}
    </div>
  );
};
```

### Soumettre une demande de vérification

```typescript
const submitVerification = async (
  token: string,
  data: VerificationRequest
): Promise<VerificationResponse> => {
  // Create FormData for file upload
  const formData = new FormData();
  formData.append('badge_type', data.badge_type);
  formData.append('nature_document', data.nature_document);
  formData.append('doc_recto', data.doc_recto);
  formData.append('doc_verso', data.doc_verso);
  formData.append('contrat_signer', data.contrat_signer);

  const response = await fetch('/api/organizers/verification/submit', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      // Don't set Content-Type header - browser will set it with boundary
    },
    body: formData,
  });

  if (!response.ok) {
    throw new Error('Failed to submit verification');
  }

  return response.json();
};

// Utilisation dans un formulaire
const VerificationForm: React.FC = () => {
  const [badgeType, setBadgeType] = useState<'verified' | 'partner'>('verified');
  const [natureDocument, setNatureDocument] = useState<DocumentType>('cnib');
  const [docRecto, setDocRecto] = useState<File | null>(null);
  const [docVerso, setDocVerso] = useState<File | null>(null);
  const [contratSigner, setContratSigner] = useState<File | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!docRecto || !docVerso || !contratSigner) {
      toast.error('Veuillez fournir tous les documents requis');
      return;
    }

    try {
      const result = await submitVerification(authToken, {
        badge_type: badgeType,
        nature_document: natureDocument,
        doc_recto: docRecto,
        doc_verso: docVerso,
        contrat_signer: contratSigner,
      });
      toast.success('Demande soumise avec succès!');
    } catch (error) {
      toast.error('Erreur lors de la soumission');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <select
        value={badgeType}
        onChange={(e) => setBadgeType(e.target.value as 'verified' | 'partner')}
      >
        <option value="verified">Vérifié</option>
        <option value="partner">Partenaire</option>
      </select>

      <select
        value={natureDocument}
        onChange={(e) => setNatureDocument(e.target.value as DocumentType)}
      >
        <option value="cnib">CNIB</option>
        <option value="permis">Permis de conduire</option>
        <option value="passport">Passeport</option>
      </select>

      <label>
        Document recto (JPEG/PNG, max 5 MB):
        <input
          type="file"
          accept="image/jpeg,image/png"
          onChange={(e) => setDocRecto(e.target.files?.[0] || null)}
          required
        />
      </label>

      <label>
        Document verso (JPEG/PNG, max 5 MB):
        <input
          type="file"
          accept="image/jpeg,image/png"
          onChange={(e) => setDocVerso(e.target.files?.[0] || null)}
          required
        />
      </label>

      <label>
        Contrat signé (PDF, max 10 MB):
        <input
          type="file"
          accept="application/pdf"
          onChange={(e) => setContratSigner(e.target.files?.[0] || null)}
          required
        />
      </label>

      <button type="submit">Soumettre la demande (Coût: 200 pièces MLM)</button>
    </form>
  );
};
```

### Panel de modération (Modérateurs)

```typescript
const ModerationPanel: React.FC = () => {
  const [pendingVerifications, setPendingVerifications] = useState<PendingVerification[]>([]);
  const { token } = useAuth();

  useEffect(() => {
    fetchPendingVerifications(token).then(data => {
      setPendingVerifications(data.verifications);
    });
  }, [token]);

  const handleValidate = async (id: number, badge: 'verified' | 'partner') => {
    const response = await fetch(`/api/organizers/verification/${id}/validate`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ badge }),
    });

    if (response.ok) {
      toast.success('Demande validée!');
      // Refresh list
    }
  };

  const handleReject = async (id: number, reason: string) => {
    const response = await fetch(`/api/organizers/verification/${id}/reject`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ rejection_reason: reason }),
    });

    if (response.ok) {
      toast.success('Demande rejetée');
      // Refresh list
    }
  };

  return (
    <div>
      <h2>Demandes de vérification en attente ({pendingVerifications.length})</h2>
      {pendingVerifications.map(verification => (
        <VerificationCard
          key={verification.id}
          verification={verification}
          onValidate={handleValidate}
          onReject={handleReject}
        />
      ))}
    </div>
  );
};
```

---

## 🔐 Gestion de l'authentification

### Headers requis pour les endpoints protégés

```typescript
const API_BASE_URL = '/api';

// Intercepteur pour ajouter automatiquement le token
const fetchWithAuth = async (url: string, options: RequestInit = {}) => {
  const token = localStorage.getItem('auth_token');

  const headers = {
    'Content-Type': 'application/json',
    ...options.headers,
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  return fetch(`${API_BASE_URL}${url}`, {
    ...options,
    headers,
  });
};
```

---

## ⚠️ Gestion des erreurs

### Codes d'erreur possibles

| Code | Description | Action recommandée |
|------|-------------|-------------------|
| 200 | Succès | - |
| 400 | Requête invalide | Vérifier les données envoyées |
| 401 | Non authentifié | Rediriger vers la page de connexion |
| 403 | Non autorisé | Vérifier les permissions de l'utilisateur |
| 404 | Ressource non trouvée | Afficher un message d'erreur |
| 500 | Erreur serveur | Réessayer ou contacter le support |

---

## 📝 Notes importantes

1. **Système de paiement**:
   - Badge `certified`: 50 pièces MLM (payées lors de l'upgrade vers organisateur)
   - Badges `verified`/`partner`: 200 pièces MLM (payées lors de la soumission de la demande)
   - Les paiements sont automatiquement déduits du wallet
   - En cas de solde insuffisant, l'opération est refusée

2. **Remboursement automatique**: Si une demande de vérification est rejetée, les 200 pièces MLM sont **automatiquement remboursées** dans le wallet de l'organisateur

3. **Badge certified**: Attribué automatiquement lors de l'upgrade, inclut le badge dans le profil

4. **Badges verified/partner**: Nécessitent tous deux la soumission de documents (identité + contrat signé) et validation par modérateur

5. **Documents requis pour verified et partner**:
   - Document d'identité (recto + verso) : CNIB, permis de conduire ou passeport
   - Images acceptées: JPEG, PNG (max 5 MB chacune)
   - Contrat signé avec la plateforme (PDF, max 10 MB)
   - Les fichiers sont uploadés via `multipart/form-data`
   - Les fichiers sont stockés sur le serveur dans `storage/app/public/organizers/`

6. **Gestion des fichiers**:
   - Les fichiers sont automatiquement supprimés si la transaction échoue
   - En cas de rejet de la demande, tous les documents sont supprimés du serveur
   - Les URL retournées dans les réponses API sont des URL publiques accessibles

7. **Différence verified vs partner**: Les deux nécessitent les mêmes documents et coûtent le même prix, mais offrent des niveaux de partenariat différents

8. **Statuts de vérification**: Permettent de suivre le processus de validation (attente → valider/rejeter)

9. **Traçabilité**: Chaque validation/rejet est enregistré avec l'ID du modérateur qui a traité la demande

10. **Auto-follow prevention**: Un utilisateur ne peut pas suivre son propre profil

11. **Transactions wallet**: Toutes les opérations (paiement, remboursement) sont enregistrées dans l'historique des transactions du wallet

---

## 🚀 Workflow complet

### Pour devenir organisateur vérifié:

1. **Créer un compte** et se connecter
2. **S'assurer d'avoir suffisamment de fonds** dans son wallet:
   - Minimum 50 pièces MLM pour devenir organisateur (badge certified)
   - 200 pièces MLM supplémentaires pour une demande de vérification (verified/partner)
3. **Devenir organisateur** via `POST /api/organizers/upgrade`
   - Coût: 50 pièces MLM
   - Badge certified attribué automatiquement
4. **Soumettre une demande de vérification** (optionnel) via `POST /api/organizers/verification/submit`
   - Coût: 200 pièces MLM
   - Documents requis: identité + contrat signé
5. **Attendre la validation** d'un modérateur
6. **Résultat**:
   - Si validé: Badge verified ou partner attribué
   - Si rejeté: 200 pièces MLM automatiquement remboursées

### Pour les modérateurs:

1. **Consulter les demandes** via `GET /api/organizers/verification/pending`
2. **Examiner les documents** fournis (identité, contrat)
3. **Décision**:
   - **Valider** via `POST /api/organizers/verification/{id}/validate` → Badge attribué
   - **Rejeter** via `POST /api/organizers/verification/{id}/reject` → Remboursement automatique de 200 pièces MLM
4. Le système enregistre automatiquement qui a traité la demande et toutes les transactions
