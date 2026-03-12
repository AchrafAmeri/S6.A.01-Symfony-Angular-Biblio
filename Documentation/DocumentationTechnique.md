# Documentation Technique — Bibliothèque en ligne

**SAE S6.A.01 — Symfony / Angular**
BUT Informatique 3e année — 2025/2026

---

## Table des matières

1. [Architecture du projet](#1-architecture-du-projet)
2. [Choix techniques](#2-choix-techniques)
3. [Guide d'installation](#3-guide-dinstallation)
4. [Difficultés rencontrées et solutions apportées](#4-difficultés-rencontrées-et-solutions-apportées)
5. [Répartition du travail](#5-répartition-du-travail)

---

## 1. Architecture du projet

### 1.1 Schéma global

```
┌─────────────────────────────────────────────────────────────────┐
│                     Navigateur utilisateur                      │
│                                                                 │
│  ┌──────────────────────────────┐   ┌────────────────────────┐  │
│  │  Front-office Angular 21     │   │  Back-office EasyAdmin  │  │
│  │  http://localhost:4200        │   │  https://localhost:8008 │  │
│  │                              │   │  /admin                 │  │
│  │  - Catalogue livres          │   │                        │  │
│  │  - Espace adhérent           │   │  - Gestion CRUD        │  │
│  │  - Réservation               │   │  - Emprunts/Retours    │  │
│  │  - Authentification JWT      │   │  - Statistiques        │  │
│  └──────────────┬───────────────┘   └──────────┬─────────────┘  │
└─────────────────┼────────────────────────────────┼───────────────┘
                  │ HTTP/JSON (JWT Bearer)          │ HTTP/Session
                  │ CORS (NelmioCorsBundle)         │ (cookie)
                  ▼                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                  Back-end Symfony 7.4 (PHP 8.2)                 │
│                  https://localhost:8008                          │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  API REST  /api/*                                        │   │
│  │  - /api/login_check  (JWT)   - /api/livres              │   │
│  │  - /api/register             - /api/auteurs             │   │
│  │  - /api/user/me              - /api/categories          │   │
│  │  - /api/reservations/{id}    - /api/mes-emprunts        │   │
│  │  - /api/admin/stats          - /api/mes-reservations    │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Firewall Symfony Security                               │   │
│  │  - Firewall "login" → json_login → JWT success handler  │   │
│  │  - Firewall "api"   → stateless JWT  (LexikJWT)         │   │
│  │  - Firewall "main"  → session cookie (EasyAdmin)        │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Pont SSO (SsoController)                                │   │
│  │  Angular → /sso/to-symfony?token=<JWT>                  │   │
│  │  Symfony → /sso/to-angular → redirect ?token=<JWT>      │   │
│  └──────────────────────────────────────────────────────────┘   │
└──────────────────────────────┬──────────────────────────────────┘
                               │ Doctrine ORM
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│  Base de données MariaDB 10.8 (port 3306)                       │
│  Base : saepoc                                                  │
│                                                                 │
│  livre  ──── auteur (ManyToMany)                                │
│  livre  ──── categorie (ManyToMany)                             │
│  livre  ──── emprunt (OneToMany)                                │
│  livre  ──── reservations (OneToMany)                           │
│  utilisateur ──── emprunt (OneToMany)                           │
│  utilisateur ──── reservations (OneToMany)                      │
└─────────────────────────────────────────────────────────────────┘
```

### 1.2 Diagramme de classes

```
┌─────────────────────────────────────┐
│  Livre                              │
├─────────────────────────────────────┤
│ - id: int                           │
│ - titre: string                     │
│ - dateSortie: DateTime|null         │
│ - langue: string                    │
│ - photoCouverture: string|null      │
├─────────────────────────────────────┤
│ + getAuteurs(): Collection          │
│ + getCategories(): Collection       │
│ + getEmprunts(): Collection         │
│ + getReservations(): Collection     │
└─────────────────────────────────────┘
         │ ManyToMany            │ ManyToMany
         ▼                       ▼
┌──────────────────┐    ┌─────────────────┐
│  Auteur          │    │  Categorie      │
├──────────────────┤    ├─────────────────┤
│ - id: int        │    │ - id: int       │
│ - nom: string    │    │ - nom: string   │
│ - prenom: string │    │ - description   │
│ - dateNaissance  │    │   : text|null   │
│ - dateDeces      │    └─────────────────┘
│ - nationalite    │
│ - photo          │
│ - description    │
└──────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Utilisateur (implements UserInterface)                 │
├─────────────────────────────────────────────────────────┤
│ - id: int                                               │
│ - email: string (UNIQUE)                                │
│ - roles: string[]  (ROLE_USER / ROLE_BIBLIO / ROLE_ADMIN│
│ - password: string (hashé argon2id)                     │
│ - nom: string|null                                      │
│ - prenom: string|null                                   │
│ - dateAdhesion: DateTime                                │
│ - dateNaiss: DateTime|null                              │
│ - adressePostale: string|null                           │
│ - numTel: string|null                                   │
│ - photo: string|null                                    │
└─────────────────────────────────────────────────────────┘
         │ OneToMany              │ OneToMany
         ▼                        ▼
┌──────────────────┐    ┌──────────────────────┐
│  Emprunt         │    │  Reservations        │
├──────────────────┤    ├──────────────────────┤
│ - id: int        │    │ - id: int            │
│ - dateEmprunt:   │    │ - dateResa:          │
│   DateTime       │    │   DateTimeImmutable  │
│ - dateRetour:    │    │ - utilisateur:       │
│   DateTime|null  │    │   Utilisateur (FK)   │
│ - utilisateur:   │    │ - livre: Livre (FK)  │
│   Utilisateur(FK)│    ├──────────────────────┤
│ - livre:         │    │ [#ReservationRules]  │
│   Livre (FK)     │    │ max 3/utilisateur    │
├──────────────────┤    │ livre non déjà réservé│
│ [#EmpruntRules]  │    │ livre non emprunté   │
│ max 5/utilisateur│    └──────────────────────┘
│ livre disponible │
│ retard > 15 jours│
└──────────────────┘
```

### 1.3 Structure des dossiers

```
poc_articles/               Symfony (back-end + API + back-office)
├── src/
│   ├── Controller/
│   │   ├── Api/            Contrôleurs REST (JSON)
│   │   ├── Admin/          Contrôleurs EasyAdmin (CRUD)
│   │   ├── HomeController  Redirection / vers /admin
│   │   ├── SecurityController  Login / Logout session
│   │   └── SsoController   Pont JWT ↔ session
│   ├── Entity/             Entités Doctrine
│   ├── Repository/         Requêtes personnalisées
│   ├── Validator/          Règles métier (EmpruntRules, ReservationRules)
│   ├── Security/           AdminAuthenticator
│   └── DataFixtures/       Données de test
├── config/
│   └── packages/
│       ├── security.yaml   Firewalls + access control
│       ├── lexik_jwt_authentication.yaml
│       └── nelmio_cors.yaml
└── migrations/             Migrations Doctrine

front_articles/             Angular (front-office)
├── src/app/
│   ├── components/         Composants (home, livres-list, livre-detail,
│   │                       auteurs-list, auteur-detail, categories-list,
│   │                       profil, login, admin-dashboard)
│   ├── services/           ApiService, AuthService
│   ├── guards/             authGuard, adminGuard
│   ├── interceptors/       authInterceptor (Bearer token + 401)
│   ├── models/             Interfaces TypeScript
│   └── environments/       URLs d'API

mariadb-fp/                 MariaDB 10.8 portable (Windows)
```

---

## 2. Choix techniques

### 2.1 Stack et versions

| Composant | Choix | Version |
|-----------|-------|---------|
| Back-end | Symfony | 7.4 |
| Back-office | EasyAdminBundle | 4.x |
| Authentification API | LexikJWTAuthenticationBundle | 3.x |
| CORS | NelmioCorsBundle | 2.x |
| PDF | dompdf | 3.x |
| ORM | Doctrine | 3.x |
| Front-end | Angular | 21 |
| CSS | Bootstrap 5 + Bootswatch | 5.3 |
| Base de données | MariaDB | 10.8 |
| PHP | — | >= 8.2 |
| Node.js | — | >= 22 |

### 2.2 Authentification double mécanisme

L'application expose deux interfaces distinctes qui n'utilisent pas le même mécanisme d'authentification.

**API REST (Angular)** : authentification stateless par JWT. Le client envoie `Authorization: Bearer <token>` à chaque requête. Le token est stocké dans le `localStorage` du navigateur. L'interceptor Angular l'injecte automatiquement et redirige vers `/login` en cas de 401.

**Back-office EasyAdmin** : authentification par session PHP cookie classique, nécessaire car EasyAdmin repose sur le firewall `main` de Symfony qui est stateful.

Pour permettre à un utilisateur connecté sur Angular d'accéder à EasyAdmin sans se reconnecter (et inversement), un pont SSO a été mis en place via `SsoController` :
- Angular → EasyAdmin : l'Angular passe son JWT dans l'URL (`/sso/to-symfony?token=<JWT>`), Symfony décode le token, retrouve l'utilisateur et ouvre une session.
- EasyAdmin → Angular : Symfony génère un nouveau JWT et redirige vers le front avec `?token=<JWT>` dans l'URL.

### 2.3 Hiérarchie des rôles

```
ROLE_ADMIN
    └── ROLE_BIBLIO
            └── ROLE_USER
```

- `ROLE_USER` : adhérent — peut consulter le catalogue, réserver, voir son espace.
- `ROLE_BIBLIO` : bibliothécaire — accès à EasyAdmin, gestion emprunts/réservations/catalogue.
- `ROLE_ADMIN` : responsable — hérite de tout + accès au dashboard de statistiques Angular.

### 2.4 Structure des entités

**Livre** est l'entité centrale. Elle est liée en ManyToMany à `Auteur` et `Categorie` (un livre peut avoir plusieurs auteurs et inversement). Les relations `Emprunt` et `Reservations` sont OneToMany côté `Livre` et côté `Utilisateur`.

Le choix du ManyToMany sans entité pivot explicite est justifié par l'absence de données supplémentaires sur la liaison (pas de rôle d'auteur, pas de position). Doctrine gère la table de jointure automatiquement.

**Emprunt.dateRetour** est nullable : un emprunt sans date de retour est un emprunt en cours. C'est ce champ qui sert de discriminant dans toutes les requêtes (livres disponibles, retards, statistiques).

La durée de prêt maximale de 15 jours est calculée à la volée dans `Emprunt::getIsEnRetard()` via un clone de `dateEmprunt` (pas stockée en base).

### 2.5 Règles métier via contraintes de validation Symfony

Plutôt que d'éparpiller les contrôles dans les contrôleurs, les règles métier sont centralisées dans des classes `Constraint` / `ConstraintValidator` personnalisées annotées directement sur les entités :

- `#[EmpruntRules]` sur `Emprunt` : vérifie que l'adhérent n'a pas déjà 5 emprunts en cours et que le livre est disponible.
- `#[ReservationRules]` sur `Reservations` : vérifie la limite de 3 réservations par adhérent et que le livre n'est pas déjà réservé ou emprunté.

Ces contraintes sont déclenchées aussi bien lors de la création via l'API que via EasyAdmin, garantissant une cohérence des règles quel que soit le point d'entrée.

### 2.6 Recherche et pagination

La recherche de livres est implémentée dans `LivreRepository::findBySearchCriteria()` via le QueryBuilder Doctrine. Les trois filtres (titre, auteur, catégorie) sont cumulables et utilisent des `LIKE` case-insensitive. La pagination utilise la classe `Doctrine\ORM\Tools\Pagination\Paginator` (gère correctement les jointures ManyToMany). La réponse API retourne un objet `{ data, totalPages, currentPage }` consommé directement par Angular.

Paramètres : 9 livres par page (configurable dans le repository).

### 2.7 Groupes de sérialisation

Pour éviter les boucles de référence et contrôler précisément les données exposées par l'API, le composant sérialiseur Symfony est utilisé avec des groupes :

- `livre:read` : données du livre + auteurs + catégories (sans les emprunts).
- `user:read` : données de l'utilisateur + ses emprunts + ses réservations (avec résumé livre).
- `auteur:read` : données de l'auteur seul.
- `categorie:read` : données de la catégorie seule.

### 2.8 Back-office EasyAdmin

EasyAdmin est choisi pour sa capacité à générer rapidement une interface d'administration complète à partir des entités Doctrine, sans écrire de templates HTML. Les personnalisations notables :

- `EmpruntCrudController` : action personnalisée "Enregistrer le retour" (met à jour `dateRetour`) ; indicateur visuel retard (badge HTML rouge/vert) ; export fiche PDF via dompdf.
- `ReservationsCrudController` : action "Transformer en emprunt" qui crée un `Emprunt` depuis la `Reservation` puis supprime la réservation, avec validation des règles métier avant persistance.
- `UtilisateurCrudController` : hachage du mot de passe à la création et à la mise à jour (détection si déjà haché avec le préfixe `$argon`).
- Dashboard : affichage de KPI (nb livres, adhérents, catégories, emprunts en cours) via Doctrine `count()`.

### 2.9 Front-office Angular

**Architecture standalone** (Angular 21, pas de NgModule). Chaque composant déclare ses imports directement.

**Gestion d'état** : `AuthService` utilise les **signals** Angular pour que les composants réagissent automatiquement aux changements de connexion (affichage conditionnel de la barre de navigation notamment).

**Routage** : protégé par deux guards fonctionnels (`authGuard`, `adminGuard`). Les routes `/profil` et `/admin/dashboard` sont inaccessibles sans être connecté ou sans le bon rôle.

---

## 3. Guide d'installation

### Prérequis

Installer les logiciels suivants :

| Outil | Version minimale | Lien |
|-------|-----------------|------|
| PHP | 8.2 | https://windows.php.net/download/ |
| Composer | 2.x | https://getcomposer.org/download/ |
| Symfony CLI | dernière | https://symfony.com/download |
| Node.js | 22 LTS | https://nodejs.org/ |
| Git | — | https://git-scm.com/ |

> MariaDB est fourni dans le dépôt (dossier `mariadb-fp/`), aucune installation séparée n'est requise.

Vérifier les installations :

```bash
php -v
composer -V
symfony version
node -v
npm -v
```

---

### Étape 1 — Récupérer le projet

```bash
git clone <url-du-depot>
cd S6.A.01-Symfony-Angular-Biblio
```

---

### Étape 2 — Démarrer MariaDB

Double-cliquer sur `mariadb-fp/start-server.bat` (ou l'exécuter depuis un terminal).

Le serveur MariaDB écoute sur le port **3306**.

Vérifier que la base `saepoc` existe (accessible via Adminer sur `http://localhost:8008/adminer` une fois Symfony lancé, ou via `mysql-client.bat`). Si elle n'existe pas, la créer :

```sql
CREATE DATABASE saepoc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Étape 3 — Configurer le back-end Symfony

```bash
cd poc_articles
```

Copier le fichier d'environnement :

```bash
cp .env .env.local
```

Éditer `.env.local` et renseigner la connexion à la base de données :

```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/saepoc?serverVersion=10.8.3-MariaDB&charset=utf8mb4"
```

> Le mot de passe root de MariaDB fourni est vide par défaut dans la configuration locale.

Renseigner également l'URL du front Angular (pour le pont SSO et CORS) :

```dotenv
APP_ANGULAR_FRONT_URL=http://localhost:4200
```

---

### Étape 4 — Installer les dépendances PHP

```bash
composer install
```

---

### Étape 5 — Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

Cela crée `config/jwt/private.pem` et `config/jwt/public.pem`.

---

### Étape 6 — Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

---

### Étape 7 — Charger les données de démonstration (optionnel)

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

---

### Étape 8 — Démarrer le serveur Symfony

```bash
symfony server:start
```

Le back-end est accessible sur **https://localhost:8008**.

> La Symfony CLI génère automatiquement un certificat TLS local. En cas d'avertissement navigateur, accepter l'exception de sécurité ou installer le certificat CA avec `symfony server:ca:install`.

---

### Étape 9 — Configurer et démarrer le front Angular

Ouvrir un nouveau terminal :

```bash
cd front_articles
npm install
```

Vérifier `src/environments/environment.ts` :

```typescript
export const environment = {
  production: false,
  apiUrl: 'https://localhost:8008/api',
  ssoUrl: 'https://localhost:8008/sso/to-symfony',
  logoutUrl: 'https://localhost:8008/logout',
  frontUrl: 'http://localhost:4200'
};
```

Démarrer le serveur de développement :

```bash
npm start
```

Le front-office est accessible sur **http://localhost:4200**.

---

### Accès à l'application

| Interface | URL |
|-----------|-----|
| Front-office Angular | http://localhost:4200 |
| API Symfony | https://localhost:8008/api |
| Back-office EasyAdmin | https://localhost:8008/admin |
| Adminer (base de données) | https://localhost:8008/adminer |

Compte administrateur par défaut (créé par les fixtures) :

| Champ | Valeur |
|-------|--------|
| Email | `admin@biblio.fr` |
| Mot de passe | `admin` |
| Rôle | `ROLE_ADMIN` |

---

## 4. Difficultés rencontrées et solutions apportées

*(section à compléter)*

---

## 5. Répartition du travail

| Membre | Périmètre |
|--------|-----------|
| **Mohammed** | Exigences back-office : interface EasyAdmin (Symfony), gestion CRUD, actions personnalisées (retour emprunt, transformation réservation → emprunt, export PDF), tableau de bord administrateur |
| **Pierre-Louis** | Exigences API REST : conception et implémentation de l'ensemble des endpoints Symfony (`/api/*`), authentification JWT, gestion des réservations côté API, règles de validation métier |
| **David** | Exigences front-office : application Angular (composants, services, guards, interceptor, routing), intégration de l'API, gestion de l'authentification côté client, pont SSO |
