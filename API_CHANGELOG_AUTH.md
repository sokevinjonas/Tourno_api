# Changement API - Authentification par Code à 6 Chiffres

## Date: 02 Janvier 2026

### ⚠️ BREAKING CHANGE - Authentification

L'authentification par **lien magique** a été remplacée par un système d'**authentification par code à 6 chiffres**.

---

## 📋 Résumé des changements

### Avant
- L'utilisateur recevait un **lien magique** par email
- Il cliquait sur le lien pour s'authentifier automatiquement
- Le frontend recevait un token dans l'URL

### Maintenant
- L'utilisateur reçoit un **code à 6 chiffres** par email
- Il saisit manuellement ce code dans l'application
- Le frontend envoie le code à l'API pour vérification

---

## 🔄 Modifications des endpoints

### 1. Envoi du code (inchangé en apparence)

**Endpoint:** `POST /api/auth/magic-link/send`

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response (200 OK):**
```json
{
  "message": "Code de vérification envoyé à votre email",
  "expires_in": "15 minutes"
}
```

**Changements:**
- Le message de réponse a changé
- L'utilisateur reçoit maintenant un code à 6 chiffres par email au lieu d'un lien

---

### 2. Vérification du code ⚠️ BREAKING CHANGE

**Endpoint:** `POST /api/auth/magic-link/verify`

**Avant:**
```json
{
  "token": "long_random_64_character_token_here..."
}
```

**Maintenant:**
```json
{
  "code": "123456"
}
```

**Validation:**
- Le champ `code` est **obligatoire**
- Le code doit être une chaîne de **exactement 6 caractères**
- Format: `string|size:6`

**Response (200 OK):**
```json
{
  "message": "Authentification réussie",
  "user": {
    "uuid": "...",
    "name": "...",
    "email": "...",
    "profile": {...},
    "wallet": {...}
  },
  "token": "sanctum_auth_token_here"
}
```

**Response (400 Bad Request):**
```json
{
  "message": "Échec de l'authentification",
  "error": "Code invalide ou expiré"
}
```

**Response (422 Validation Error):**
```json
{
  "message": "Validation failed",
  "errors": {
    "code": ["Le champ code doit contenir 6 caractères."]
  }
}
```

---

## 🎨 Modifications requises côté Frontend

### 1. Supprimer la page de redirection du lien magique

❌ **À supprimer:**
- La page `/auth/verify?token=...` qui interceptait le lien magique

### 2. Créer une page de saisie du code

✅ **À créer:** Une nouvelle page avec:

```typescript
// Exemple de composant (Angular/React/Vue)
interface CodeVerificationForm {
  code: string; // Champ de saisie pour le code à 6 chiffres
}

// Validation
const codeRegex = /^\d{6}$/; // Exactement 6 chiffres
```

**Recommandations UX:**
- Input de type texte ou numérique
- Masque de saisie pour afficher les 6 cases: `□ □ □ □ □ □`
- Auto-focus sur la première case
- Passage automatique à la case suivante après saisie d'un chiffre
- Validation en temps réel (6 chiffres requis)
- Bouton "Vérifier" ou soumission automatique après le 6ème chiffre

**Exemple de flow:**
```
1. User entre son email
   ↓
2. API envoie le code par email
   ↓
3. User reçoit l'email avec le code (ex: 123456)
   ↓
4. User saisit le code dans l'app: [1][2][3][4][5][6]
   ↓
5. Frontend envoie { "code": "123456" } à /api/auth/magic-link/verify
   ↓
6. API retourne le token d'authentification
```

### 3. Mettre à jour le service d'authentification

```typescript
// Avant
verifyMagicLink(token: string) {
  return this.http.post('/api/auth/magic-link/verify', { token });
}

// Maintenant
verifyCode(code: string) {
  return this.http.post('/api/auth/magic-link/verify', { code });
}
```

### 4. Gestion des erreurs

```typescript
// Codes d'erreur possibles
switch (error.status) {
  case 400:
    // Code invalide ou expiré
    showError('Le code est invalide ou a expiré. Demandez un nouveau code.');
    break;
  case 422:
    // Validation échouée (ex: code pas exactement 6 chiffres)
    showError('Le code doit contenir exactement 6 chiffres.');
    break;
  case 500:
    // Erreur serveur
    showError('Une erreur est survenue. Veuillez réessayer.');
    break;
}
```

---

## 📧 Changements dans l'email

### Avant
L'utilisateur recevait un email avec:
- Un bouton "Se connecter ici"
- Un lien complet à copier/coller

### Maintenant
L'utilisateur reçoit un email avec:
- Un **code à 6 chiffres** affiché en grand
- Format: `123456`
- Style: Police monospace, grande taille, lettres espacées

---

## 🔒 Sécurité

Les règles de sécurité restent identiques:

- ✅ Code valide pendant **15 minutes**
- ✅ Code à **usage unique** (ne peut pas être réutilisé)
- ✅ Ancien code invalidé lors d'une nouvelle demande
- ✅ Codes expirés automatiquement supprimés

---

## 🧪 Tests

### Scénarios à tester:

1. ✅ **Happy path**: Saisie du bon code dans le délai
2. ❌ **Code expiré**: Code saisi après 15 minutes
3. ❌ **Code invalide**: Code qui n'existe pas
4. ❌ **Code déjà utilisé**: Tentative de réutilisation
5. ❌ **Format invalide**: Code avec moins/plus de 6 chiffres
6. ✅ **Nouveau code**: Demande d'un nouveau code (invalide l'ancien)

---

## 📝 Notes techniques

### Base de données

La table `login_tokens` a été modifiée:
```sql
-- Avant
token VARCHAR(64) UNIQUE

-- Maintenant
code VARCHAR(6)
+ INDEX sur code
```

### Migration nécessaire

Si votre base de données est déjà en production, vous devez:
1. Migrer la colonne `token` → `code`
2. Nettoyer les anciens tokens
3. Redémarrer l'application

---

## 🆘 Support

En cas de questions ou problèmes, contactez l'équipe backend.

---

**Dernière mise à jour:** 02 Janvier 2026
