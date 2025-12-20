# 📚 Documentation API - Système d'Organisateurs

## Vue d'ensemble

Le système d'organisateurs permet aux joueurs de découvrir, suivre et interagir avec les organisateurs de tournois. Chaque organisateur possède un profil enrichi avec badges, bio, liens sociaux et statistiques.

## 🔑 Concepts clés

### Types de badges
- `certified` - Organisateur certifié (officiel)
- `verified` - Organisateur vérifié
- `partner` - Partenaire de la plateforme
- `null` - Aucun badge

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
    },
    {
      "id": 11,
      "name": "Elite Gaming",
      "badge": "certified",
      "tournaments": 18,
      "followers": 850,
      "avatar": "E",
      "is_featured": false,
      "bio": "Communauté de gamers passionnés...",
      "social_links": {
        "twitter": "https://twitter.com/elite_gaming"
      }
    }
  ],
  "total": 2
}
```

**Utilisation Frontend (TypeScript/React):**
```typescript
interface Organizer {
  id: number;
  name: string;
  badge: 'certified' | 'verified' | 'partner' | null;
  tournaments: number;
  followers: number;
  avatar: string; // URL ou initiale
  is_featured: boolean;
  bio?: string;
  social_links?: {
    twitter?: string;
    discord?: string;
    [key: string]: string | undefined;
  };
}

interface OrganizersResponse {
  organizers: Organizer[];
  total: number;
}

// Exemple d'appel
const fetchOrganizers = async (featured?: boolean): Promise<OrganizersResponse> => {
  const params = new URLSearchParams();
  if (featured) params.append('featured', 'true');

  const response = await fetch(`/api/organizers?${params}`);
  return response.json();
};
```

---

### 2. Obtenir les détails d'un organisateur

**Endpoint:** `GET /api/organizers/{id}`

**Authentification:** Non requise (Public)

**Path Parameters:**
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `id` | integer | Oui | ID de l'organisateur |

**Exemple de requête:**
```typescript
GET /api/organizers/10
```

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
    "bio": "Organisation officielle de tournois MLM. Nous organisons des compétitions équitables et professionnelles pour tous les joueurs.",
    "social_links": {
      "twitter": "https://twitter.com/tourno_mlm",
      "discord": "https://discord.gg/tourno"
    },
    "recent_tournaments": [
      {
        "id": 1,
        "name": "Swiss Championship - eFootball",
        "game": "efootball",
        "start_date": "2025-12-25 12:00:00",
        "status": "open",
        "max_participants": 18,
        "registrations_count": 18
      }
    ]
  }
}
```

**Réponse (404 Not Found):**
```json
{
  "message": "Organizer not found"
}
```

**Utilisation Frontend:**
```typescript
interface OrganizerDetails extends Organizer {
  email: string;
  recent_tournaments: Tournament[];
}

const fetchOrganizerDetails = async (id: number): Promise<OrganizerDetails> => {
  const response = await fetch(`/api/organizers/${id}`);
  if (!response.ok) {
    throw new Error('Organizer not found');
  }
  const data = await response.json();
  return data.organizer;
};
```

---

### 3. Suivre/Ne plus suivre un organisateur

**Endpoint:** `POST /api/organizers/{id}/follow`

**Authentification:** ✅ Requise (Bearer Token)

**Path Parameters:**
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `id` | integer | Oui | ID de l'organisateur à suivre/ne plus suivre |

**Headers:**
```
Authorization: Bearer {access_token}
```

**Corps de la requête:** Aucun

**Exemple de requête:**
```typescript
POST /api/organizers/10/follow
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

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

**Réponse (404 Not Found):**
```json
{
  "message": "Organizer not found"
}
```

**Utilisation Frontend:**
```typescript
interface FollowResponse {
  message: string;
  is_following: boolean;
  followers_count: number;
}

const toggleFollowOrganizer = async (
  organizerId: number,
  token: string
): Promise<FollowResponse> => {
  const response = await fetch(`/api/organizers/${organizerId}/follow`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('Failed to toggle follow');
  }

  return response.json();
};

// Exemple d'utilisation dans un composant React
const FollowButton: React.FC<{ organizer: Organizer }> = ({ organizer }) => {
  const [isFollowing, setIsFollowing] = useState(false);
  const [followersCount, setFollowersCount] = useState(organizer.followers);

  const handleFollow = async () => {
    try {
      const result = await toggleFollowOrganizer(organizer.id, authToken);
      setIsFollowing(result.is_following);
      setFollowersCount(result.followers_count);
    } catch (error) {
      console.error('Failed to follow organizer:', error);
    }
  };

  return (
    <button onClick={handleFollow}>
      {isFollowing ? 'Ne plus suivre' : 'Suivre'}
      <span>{followersCount} followers</span>
    </button>
  );
};
```

---

### 4. Vérifier si l'utilisateur suit un organisateur

**Endpoint:** `GET /api/organizers/{id}/check-following`

**Authentification:** ✅ Requise (Bearer Token)

**Path Parameters:**
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `id` | integer | Oui | ID de l'organisateur |

**Headers:**
```
Authorization: Bearer {access_token}
```

**Exemple de requête:**
```typescript
GET /api/organizers/10/check-following
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Réponse (200 OK):**
```json
{
  "is_following": true
}
```

**Utilisation Frontend:**
```typescript
interface FollowingStatus {
  is_following: boolean;
}

const checkFollowingStatus = async (
  organizerId: number,
  token: string
): Promise<boolean> => {
  const response = await fetch(
    `/api/organizers/${organizerId}/check-following`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    }
  );

  const data: FollowingStatus = await response.json();
  return data.is_following;
};

// Utilisation dans un effet React
useEffect(() => {
  const loadFollowingStatus = async () => {
    const status = await checkFollowingStatus(organizer.id, authToken);
    setIsFollowing(status);
  };

  loadFollowingStatus();
}, [organizer.id]);
```

---

### 5. Obtenir mes abonnements (organisateurs suivis)

**Endpoint:** `GET /api/organizers/my/following`

**Authentification:** ✅ Requise (Bearer Token)

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:** Aucun

**Exemple de requête:**
```typescript
GET /api/organizers/my/following
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

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
    },
    {
      "id": 11,
      "name": "Elite Gaming",
      "badge": "certified",
      "tournaments": 18,
      "followers": 850,
      "avatar": "E",
      "is_featured": false
    }
  ],
  "total": 2
}
```

**Utilisation Frontend:**
```typescript
interface MyFollowingResponse {
  following: Organizer[];
  total: number;
}

const fetchMyFollowing = async (token: string): Promise<Organizer[]> => {
  const response = await fetch('/api/organizers/my/following', {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });

  const data: MyFollowingResponse = await response.json();
  return data.following;
};

// Exemple de composant
const MyFollowingList: React.FC = () => {
  const [following, setFollowing] = useState<Organizer[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadFollowing = async () => {
      try {
        const organizers = await fetchMyFollowing(authToken);
        setFollowing(organizers);
      } catch (error) {
        console.error('Failed to load following:', error);
      } finally {
        setLoading(false);
      }
    };

    loadFollowing();
  }, []);

  if (loading) return <div>Chargement...</div>;

  return (
    <div>
      <h2>Mes abonnements ({following.length})</h2>
      {following.map(organizer => (
        <OrganizerCard key={organizer.id} organizer={organizer} />
      ))}
    </div>
  );
};
```

---

## 🎨 Exemples d'intégration Frontend

### Page Liste des Organisateurs

```typescript
import { useState, useEffect } from 'react';

const OrganizersPage: React.FC = () => {
  const [organizers, setOrganizers] = useState<Organizer[]>([]);
  const [filter, setFilter] = useState<'all' | 'featured' | 'certified'>('all');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadOrganizers = async () => {
      setLoading(true);
      try {
        const params = new URLSearchParams();

        if (filter === 'featured') {
          params.append('featured', 'true');
        } else if (filter === 'certified') {
          params.append('badge', 'certified');
        }

        const response = await fetch(`/api/organizers?${params}`);
        const data: OrganizersResponse = await response.json();
        setOrganizers(data.organizers);
      } catch (error) {
        console.error('Failed to load organizers:', error);
      } finally {
        setLoading(false);
      }
    };

    loadOrganizers();
  }, [filter]);

  return (
    <div>
      <h1>Organisateurs</h1>

      {/* Filtres */}
      <div className="filters">
        <button onClick={() => setFilter('all')}>Tous</button>
        <button onClick={() => setFilter('featured')}>En vedette</button>
        <button onClick={() => setFilter('certified')}>Certifiés</button>
      </div>

      {/* Liste */}
      {loading ? (
        <div>Chargement...</div>
      ) : (
        <div className="organizers-grid">
          {organizers.map(organizer => (
            <OrganizerCard key={organizer.id} organizer={organizer} />
          ))}
        </div>
      )}
    </div>
  );
};
```

### Composant Carte Organisateur

```typescript
const OrganizerCard: React.FC<{ organizer: Organizer }> = ({ organizer }) => {
  const [isFollowing, setIsFollowing] = useState(false);
  const [followersCount, setFollowersCount] = useState(organizer.followers);
  const { authToken } = useAuth();

  useEffect(() => {
    // Charger le statut de suivi
    if (authToken) {
      checkFollowingStatus(organizer.id, authToken)
        .then(setIsFollowing)
        .catch(console.error);
    }
  }, [organizer.id, authToken]);

  const handleFollow = async () => {
    if (!authToken) {
      // Rediriger vers la page de connexion
      return;
    }

    try {
      const result = await toggleFollowOrganizer(organizer.id, authToken);
      setIsFollowing(result.is_following);
      setFollowersCount(result.followers_count);
    } catch (error) {
      console.error('Failed to toggle follow:', error);
    }
  };

  return (
    <div className="organizer-card">
      {/* Badge en vedette */}
      {organizer.is_featured && (
        <div className="featured-badge">⭐ En vedette</div>
      )}

      {/* Avatar */}
      <div className="avatar">
        {organizer.avatar.startsWith('http') ? (
          <img src={organizer.avatar} alt={organizer.name} />
        ) : (
          <div className="avatar-initial">{organizer.avatar}</div>
        )}
      </div>

      {/* Infos */}
      <div className="info">
        <h3>
          {organizer.name}
          {organizer.badge && (
            <span className={`badge ${organizer.badge}`}>
              {organizer.badge === 'certified' && '✓'}
              {organizer.badge === 'verified' && '✓'}
              {organizer.badge === 'partner' && '★'}
            </span>
          )}
        </h3>

        <div className="stats">
          <span>{organizer.tournaments} tournois</span>
          <span>{followersCount} followers</span>
        </div>

        {organizer.bio && (
          <p className="bio">{organizer.bio}</p>
        )}

        {/* Liens sociaux */}
        {organizer.social_links && (
          <div className="social-links">
            {organizer.social_links.twitter && (
              <a href={organizer.social_links.twitter} target="_blank">
                Twitter
              </a>
            )}
            {organizer.social_links.discord && (
              <a href={organizer.social_links.discord} target="_blank">
                Discord
              </a>
            )}
          </div>
        )}
      </div>

      {/* Bouton suivre */}
      <button
        onClick={handleFollow}
        className={isFollowing ? 'following' : 'follow'}
      >
        {isFollowing ? 'Abonné' : 'Suivre'}
      </button>
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

// Utilisation
const result = await fetchWithAuth('/organizers/10/follow', {
  method: 'POST',
});
```

---

## ⚠️ Gestion des erreurs

### Codes d'erreur possibles

| Code | Description | Action recommandée |
|------|-------------|-------------------|
| 200 | Succès | - |
| 401 | Non authentifié | Rediriger vers la page de connexion |
| 404 | Organisateur non trouvé | Afficher un message d'erreur |
| 500 | Erreur serveur | Réessayer ou contacter le support |

### Exemple de gestion d'erreurs

```typescript
const handleApiError = (error: any) => {
  if (error.status === 401) {
    // Rediriger vers la page de connexion
    window.location.href = '/login';
  } else if (error.status === 404) {
    // Afficher un message
    toast.error('Organisateur introuvable');
  } else {
    // Erreur générique
    toast.error('Une erreur est survenue');
  }
};

// Utilisation
try {
  const result = await toggleFollowOrganizer(id, token);
} catch (error) {
  handleApiError(error);
}
```

---

## 📊 Types TypeScript complets

```typescript
// types/organizer.ts

export type BadgeType = 'certified' | 'verified' | 'partner' | null;

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

export interface OrganizersResponse {
  organizers: Organizer[];
  total: number;
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

export interface Tournament {
  id: number;
  name: string;
  game: string;
  start_date: string;
  status: string;
  max_participants: number;
  registrations_count: number;
}
```

---

## 🎯 Cas d'usage complets

### 1. Afficher les organisateurs en vedette sur la page d'accueil

```typescript
const FeaturedOrganizers: React.FC = () => {
  const [organizers, setOrganizers] = useState<Organizer[]>([]);

  useEffect(() => {
    fetch('/api/organizers?featured=true')
      .then(res => res.json())
      .then((data: OrganizersResponse) => {
        setOrganizers(data.organizers);
      });
  }, []);

  return (
    <section>
      <h2>Organisateurs en vedette</h2>
      <div className="featured-grid">
        {organizers.map(org => (
          <OrganizerCard key={org.id} organizer={org} />
        ))}
      </div>
    </section>
  );
};
```

### 2. Page de profil d'organisateur

```typescript
const OrganizerProfilePage: React.FC<{ id: number }> = ({ id }) => {
  const [organizer, setOrganizer] = useState<OrganizerDetails | null>(null);
  const [isFollowing, setIsFollowing] = useState(false);

  useEffect(() => {
    // Charger les détails
    fetch(`/api/organizers/${id}`)
      .then(res => res.json())
      .then(data => setOrganizer(data.organizer));

    // Charger le statut de suivi
    fetchWithAuth(`/organizers/${id}/check-following`)
      .then(res => res.json())
      .then((data: FollowingStatus) => setIsFollowing(data.is_following));
  }, [id]);

  if (!organizer) return <div>Chargement...</div>;

  return (
    <div className="organizer-profile">
      <header>
        <h1>{organizer.name}</h1>
        <button onClick={handleFollow}>
          {isFollowing ? 'Abonné' : 'Suivre'}
        </button>
      </header>

      <div className="stats">
        <div>{organizer.tournaments} tournois</div>
        <div>{organizer.followers} followers</div>
      </div>

      <p>{organizer.bio}</p>

      <section className="recent-tournaments">
        <h2>Tournois récents</h2>
        {organizer.recent_tournaments.map(tournament => (
          <TournamentCard key={tournament.id} tournament={tournament} />
        ))}
      </section>
    </div>
  );
};
```

---

## 📝 Notes importantes

1. **Authentification**: Seuls les endpoints de suivi/abonnement nécessitent une authentification
2. **Cache**: Pensez à mettre en cache les données des organisateurs pour améliorer les performances
3. **Temps réel**: Le nombre de followers est mis à jour en temps réel après chaque action
4. **Avatar**: Peut être soit une URL d'image, soit une initiale (lettre unique)
5. **Badges**: Les badges sont optionnels, vérifiez toujours leur présence avant affichage

---

## 🚀 Prochaines étapes

Pour une intégration complète:
1. Créer un service API centralisé pour gérer toutes les requêtes
2. Implémenter un système de cache (React Query, SWR, etc.)
3. Ajouter des animations pour les transitions de suivi/désuivi
4. Mettre en place des notifications lorsqu'un organisateur suivi crée un nouveau tournoi
5. Ajouter des statistiques plus détaillées (taux de victoire, nombre de participants total, etc.)
