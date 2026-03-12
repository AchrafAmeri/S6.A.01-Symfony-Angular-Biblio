# HELP.md - Guide de Présentation Orale

## 📚 Vue d'ensemble du projet

### Qu'est-ce que ce projet ?

Ce projet est une **application web Full Stack de gestion de bibliothèque** développée dans le cadre de la SAE S6.A.01. Il s'agit d'un système complet permettant de gérer le catalogue de livres, les auteurs, les emprunts et les réservations d'une bibliothèque.

### Architecture Technique

L'application suit une architecture **Client-Serveur** avec séparation claire des responsabilités :

#### Backend (Symfony 7.4)
- **Type** : API REST + Back-office d'administration
- **Dossier** : `poc_articles/`
- **Technologies** :
  - PHP 8.4
  - Symfony 7.4 (framework MVC)
  - Doctrine ORM (gestion de base de données)
  - LexikJWTAuthenticationBundle (authentification JWT)
  - EasyAdmin (interface d'administration)
- **Rôle** :
  - Expose des endpoints API RESTful pour le frontend
  - Gère la logique métier (validation, règles de gestion)
  - Assure la persistance des données via Doctrine
  - Fournit une interface d'administration pour les bibliothécaires

#### Frontend (Angular 21)
- **Type** : Single Page Application (SPA)
- **Dossier** : `front_articles/`
- **Technologies** :
  - Angular 21 (framework JavaScript)
  - TypeScript
  - RxJS (programmation réactive)
  - Angular Signals (gestion d'état réactive)
  - Bootstrap 5 (design responsive)
- **Rôle** :
  - Interface utilisateur interactive et moderne
  - Communication avec l'API backend
  - Gestion de l'authentification JWT côté client
  - Navigation SPA sans rechargement de page

#### Base de données (MariaDB 10.8.3)
- **Type** : Base de données relationnelle
- **Dossier** : `mariadb-fp/` (version portable)
- **Schéma** : 6 tables principales
  - `livre` : Catalogue des livres
  - `auteur` : Liste des auteurs
  - `categorie` : Catégories de livres
  - `utilisateur` : Membres de la bibliothèque
  - `emprunt` : Historique des emprunts
  - `reservations` : Réservations en cours
  - Tables de liaison : `livre_auteur`, `livre_categorie`

### Modèle de Données

```
┌─────────────┐       ┌──────────────┐       ┌──────────────┐
│   Livre     │◄─────►│ livre_auteur │◄─────►│    Auteur    │
│             │       └──────────────┘       │              │
│ - titre     │                               │ - nom        │
│ - dateEdit  │       ┌──────────────┐       │ - prenom     │
│ - langue    │◄─────►│livre_categor │◄─────►│ - biographie │
│ - couverture│       └──────────────┘       └──────────────┘
└─────┬───────┘                 ▲
      │                         │              ┌──────────────┐
      │                         └─────────────►│  Categorie   │
      │                                        │ - nom        │
      ▼                                        └──────────────┘
┌─────────────┐       ┌──────────────┐
│  Emprunt    │       │ Reservations │
│             │       │              │
│ - dateEmpr  │       │ - dateRes    │
│ - dateRetour│       └──────┬───────┘
└─────┬───────┘              │
      │                      │
      │      ┌───────────────▼────┐
      └─────►│   Utilisateur      │
             │                    │
             │ - email            │
             │ - roles            │
             │ - adressePostale   │
             └────────────────────┘
```

### Flux de Communication

```
Navigateur ──HTTP/HTTPS──► Angular Frontend ──API REST (JSON)──► Symfony Backend ──Doctrine ORM──► MariaDB
   (UI)                      (SPA)                                  (API + Admin)                    (BDD)
    ▲                          │                                         │
    │                          │                                         │
    └──────────────────────────┴─────────────────────────────────────────┘
          JWT Token (Authorization: Bearer xxx)
```

---

## 🎯 Fonctionnalité 1 : Recherche Avancée de Livres

### Description

Cette fonctionnalité permet aux utilisateurs de **rechercher des livres** dans le catalogue en utilisant plusieurs **critères de filtrage simultanés** :
- Titre du livre
- Nom de l'auteur
- Catégorie

Les résultats sont **paginés** (9 livres par page) pour améliorer les performances et l'expérience utilisateur.

### Architecture Technique

#### Frontend Angular (`front_articles/src/app/components/livres-list/`)

**Composant TypeScript** (`livres-list.ts`) :
```typescript
export class LivresList implements OnInit {
  // Signaux réactifs pour les critères de recherche
  searchTitre = signal('');
  searchAuteur = signal('');
  searchCategorie = signal('');

  // Signaux pour la pagination
  page = signal(1);
  totalPages = signal(1);

  // Données des livres
  livres = signal<Livre[]>([]);
}
```

**Points clés d'implémentation** :

1. **Angular Signals** : Utilisation de `signal()` pour créer des variables réactives. Contrairement aux Observables RxJS traditionnels, les Signals offrent une gestion d'état plus simple et performante avec mise à jour automatique du DOM.

2. **Méthode `loadLivres()`** :
   ```typescript
   loadLivres() {
     this.apiService.getLivres(
       this.searchTitre(),    // () pour obtenir la valeur du signal
       this.searchAuteur(),
       this.searchCategorie(),
       this.page()
     ).subscribe({
       next: (response) => {
         this.livres.set(response.data);        // .set() pour modifier un signal
         this.totalPages.set(response.totalPages);
       }
     });
   }
   ```

3. **Méthode `onSearch()`** : Réinitialise la pagination à la page 1 lors d'une nouvelle recherche pour éviter des résultats vides.

4. **Computed Values** :
   ```typescript
   pagesArray = computed(() =>
     Array.from({ length: this.totalPages() }, (_, i) => i + 1)
   );
   ```
   Crée automatiquement un tableau de numéros de page [1, 2, 3...] qui se recalcule automatiquement quand `totalPages` change.

**Template HTML** (`livres-list.html`) :
- Formulaire avec `[(ngModel)]` pour le two-way data binding
- Boucle `@for` (nouvelle syntaxe Angular 17+) pour afficher les livres
- Boutons de pagination avec `(click)="goToPage(p)"`

#### Backend Symfony (`poc_articles/src/Controller/Api/LivreController.php`)

**Contrôleur API** :
```php
#[Route('/api/livres', name: 'api_livres', methods: ['GET'])]
public function index(Request $request, LivreRepository $livreRepository): JsonResponse
{
    $titre = $request->query->get('titre', '');
    $auteur = $request->query->get('auteur', '');
    $categorie = $request->query->get('categorie', '');
    $page = $request->query->getInt('page', 1);

    $livres = $livreRepository->findBySearchCriteria($titre, $auteur, $categorie, $page);

    return $this->json($livres, 200, [], ['groups' => 'livre:read']);
}
```

**Points clés** :
- Récupération des paramètres query string (`?titre=xxx&auteur=yyy`)
- Délégation de la logique au Repository (pattern Repository)
- Sérialisation JSON avec groupes Symfony Serializer

#### Repository Doctrine (`poc_articles/src/Repository/LivreRepository.php`)

**Requête Doctrine avec QueryBuilder** :
```php
public function findBySearchCriteria(?string $titre, ?string $auteur, ?string $categorie, int $page = 1, int $limit = 9): array
{
    $qb = $this->createQueryBuilder('l')
        ->leftJoin('l.auteurs', 'a')      // Jointure Many-to-Many
        ->addSelect('a')
        ->leftJoin('l.categories', 'c')
        ->addSelect('c');

    if (!empty($titre)) {
        $qb->andWhere('l.titre LIKE :titre')
           ->setParameter('titre', '%' . $titre . '%');
    }

    // Filtrage auteur (nom OU prénom)
    if (!empty($auteur)) {
        $qb->andWhere('a.nom LIKE :auteur OR a.prenom LIKE :auteur')
           ->setParameter('auteur', '%' . $auteur . '%');
    }

    // Pagination avec offset
    $offset = ($page - 1) * $limit;
    $qb->setFirstResult($offset)
       ->setMaxResults($limit);

    $paginator = new Paginator($qb, true);

    return [
        'data' => iterator_to_array($paginator->getIterator()),
        'totalPages' => (int)ceil(count($paginator) / $limit),
        'currentPage' => $page
    ];
}
```

**Points techniques importants** :

1. **QueryBuilder Doctrine** : Construction dynamique de requêtes SQL de manière orientée objet

2. **LEFT JOIN avec addSelect()** :
   - Évite le problème N+1 queries (charge les relations en une seule requête)
   - `addSelect()` force le chargement immédiat des entités liées

3. **LIKE avec wildcards** (`%terme%`) : Recherche partielle insensible à la position

4. **Paramètres préparés** (`:titre`, `:auteur`) : Protection contre les injections SQL

5. **Doctrine Paginator** : Gère automatiquement le COUNT et la pagination même avec des jointures complexes

### Flux de Données Complet

```
1. User tape "Tolkien" dans le champ auteur
   ↓
2. Angular détecte le changement via ngModel
   ↓
3. User clique sur "Rechercher"
   ↓
4. Méthode onSearch() appelée → reset page à 1
   ↓
5. loadLivres() appelée
   ↓
6. HTTP GET /api/livres?auteur=Tolkien&page=1
   ↓
7. Symfony reçoit la requête → LivreController
   ↓
8. Controller appelle LivreRepository
   ↓
9. Doctrine construit la requête SQL :
   SELECT l.*, a.*, c.*
   FROM livre l
   LEFT JOIN livre_auteur la ON l.id = la.livre_id
   LEFT JOIN auteur a ON la.auteur_id = a.id
   LEFT JOIN livre_categorie lc ON l.id = lc.livre_id
   LEFT JOIN categorie c ON lc.categorie_id = c.id
   WHERE (a.nom LIKE '%Tolkien%' OR a.prenom LIKE '%Tolkien%')
   LIMIT 9 OFFSET 0
   ↓
10. MariaDB exécute la requête et retourne les résultats
    ↓
11. Doctrine hydrate les objets PHP (Livre, Auteur, Categorie)
    ↓
12. Symfony sérialise en JSON avec groupes 'livre:read'
    ↓
13. Response HTTP JSON envoyée au frontend
    ↓
14. Angular reçoit la réponse dans subscribe()
    ↓
15. livres.set(response.data) met à jour le signal
    ↓
16. Angular détecte le changement et re-render le DOM automatiquement
```

### Avantages de cette Architecture

1. **Séparation des responsabilités** : UI / Logique métier / Données
2. **Réactivité** : Angular Signals assurent une mise à jour automatique
3. **Performance** :
   - Pagination côté serveur
   - Chargement eager des relations (pas de N+1)
   - Requêtes préparées optimisées
4. **Sécurité** : Protection injection SQL via paramètres Doctrine
5. **Expérience utilisateur** : Recherche multi-critères + pagination fluide

---

## 🎯 Fonctionnalité 2 : Recherche d'Auteurs avec Pagination

### Description

Cette fonctionnalité permet de **rechercher des auteurs** par leur **nom ou prénom** avec une **liste paginée** des résultats (9 auteurs par page). C'est un composant essentiel pour faciliter la navigation dans le catalogue d'auteurs de la bibliothèque.

### Architecture Technique

#### Frontend Angular (`front_articles/src/app/components/auteurs-list/`)

**Composant TypeScript** (`auteurs-list.ts`) :
```typescript
export class AuteursList implements OnInit {
  // Signal pour la recherche
  searchNom = signal('');

  // Signaux pour la pagination
  page = signal(1);
  totalPages = signal(1);

  // Données des auteurs
  auteurs = signal<Auteur[]>([]);
}
```

**Méthode de chargement** :
```typescript
loadAuteurs() {
  this.apiService.getAuteurs(
    this.searchNom(),
    this.page()
  ).subscribe({
    next: (response: any) => {
      this.auteurs.set(response.data);
      this.totalPages.set(response.totalPages);
    }
  });
}
```

**Différences avec la recherche de livres** :
- Un seul critère de recherche (nom/prénom) au lieu de trois
- Structure identique pour la pagination
- Même pattern d'utilisation des Signals

#### Backend Symfony (`poc_articles/src/Controller/Api/AuteurController.php`)

**Contrôleur API** :
```php
#[Route('/api/auteurs', name: 'api_auteurs', methods: ['GET'])]
public function index(Request $request, AuteurRepository $auteurRepository): JsonResponse
{
    $nom = $request->query->get('nom', '');
    $page = (int) $request->query->get('page', 1);

    $result = $auteurRepository->findBySearchCriteria($nom, $page);

    return $this->json($result, 200, [], ['groups' => 'auteur:read']);
}
```

#### Repository Doctrine (`poc_articles/src/Repository/AuteurRepository.php`)

**Requête personnalisée** :
```php
public function findBySearchCriteria(?string $nom, int $page = 1, int $limit = 9): array
{
    $qb = $this->createQueryBuilder('a');

    // Recherche sur nom OU prénom
    if (!empty($nom)) {
        $qb->andWhere('a.nom LIKE :nom OR a.prenom LIKE :nom')
           ->setParameter('nom', '%' . $nom . '%');
    }

    // Pagination
    $offset = ($page - 1) * $limit;
    $qb->setFirstResult($offset)
       ->setMaxResults($limit);

    $paginator = new Paginator($qb, true);

    $totalItems = count($paginator);
    $totalPages = ceil($totalItems / $limit);

    return [
        'data' => iterator_to_array($paginator->getIterator()),
        'totalPages' => $totalPages > 0 ? (int)$totalPages : 1,
        'currentPage' => $page
    ];
}
```

**Points clés** :
1. **Recherche combinée** : `a.nom LIKE :nom OR a.prenom LIKE :nom` - Un seul terme recherche dans les deux champs
2. **Pas de jointures** : Requête plus simple car pas de relations à charger
3. **Protection division par zéro** : `$totalPages > 0 ? (int)$totalPages : 1`

### Comparaison des deux fonctionnalités

| Aspect | Recherche Livres | Recherche Auteurs |
|--------|------------------|-------------------|
| **Critères** | 3 (titre, auteur, catégorie) | 1 (nom/prénom) |
| **Jointures** | 2 LEFT JOIN (auteurs, catégories) | Aucune |
| **Complexité SQL** | Haute | Faible |
| **Frontend** | 3 champs de formulaire | 1 champ de formulaire |
| **Pagination** | Identique (9/page) | Identique (9/page) |
| **Pattern** | Repository + Paginator | Repository + Paginator |

---

## 🔍 Questions & Réponses pour l'Oral

### Questions Générales sur le Projet

#### Q1 : Pourquoi avez-vous choisi une architecture API REST plutôt qu'une application monolithique Symfony traditionnelle ?

**Réponse** :
J'ai choisi une architecture API REST pour plusieurs raisons :

1. **Séparation des responsabilités** : Le backend se concentre uniquement sur la logique métier et les données, tandis que le frontend gère l'interface utilisateur. Cela rend le code plus maintenable.

2. **Réutilisabilité** : L'API peut être consommée par d'autres clients (application mobile, autre frontend, scripts...) sans modifier le backend.

3. **Scalabilité** : Frontend et backend peuvent être déployés et scalés indépendamment. Par exemple, on peut avoir plusieurs serveurs frontend derrière un load balancer.

4. **Technologies modernes** : Angular offre une expérience utilisateur fluide avec une SPA (Single Page Application) sans rechargement de page, impossible avec Twig traditionnel.

5. **Compétences professionnelles** : Cette architecture est très demandée en entreprise et démontre la maîtrise de technologies Full Stack.

#### Q2 : Expliquez le rôle de Doctrine ORM dans votre projet.

**Réponse** :
Doctrine ORM (Object-Relational Mapping) est le système de persistance de données de Symfony. Son rôle est de faire le pont entre le monde objet (PHP) et le monde relationnel (SQL) :

1. **Mapping** : Les classes PHP (Entity) sont mappées aux tables de la base de données :
   ```php
   #[ORM\Entity]
   class Livre {
       #[ORM\Column(type: 'string')]
       private string $titre;
   }
   ```

2. **QueryBuilder** : Permet de construire des requêtes SQL de manière orientée objet sans écrire de SQL brut :
   ```php
   $qb->createQueryBuilder('l')
       ->where('l.titre LIKE :titre')
   ```

3. **Hydratation** : Transforme automatiquement les résultats SQL en objets PHP exploitables.

4. **Relations** : Gère automatiquement les relations entre entités (OneToMany, ManyToMany...) et le lazy/eager loading.

5. **Sécurité** : Protège contre les injections SQL grâce aux requêtes préparées.

Dans mon projet, Doctrine me permet de manipuler les livres, auteurs et catégories comme des objets PHP sans me soucier du SQL sous-jacent.

#### Q3 : Qu'est-ce qu'Angular Signals et pourquoi l'avez-vous utilisé ?

**Réponse** :
Angular Signals est un nouveau système de gestion d'état réactif introduit dans Angular 16+ :

**Définition** : Un Signal est une variable réactive qui notifie automatiquement Angular lorsque sa valeur change, déclenchant une mise à jour du DOM.

**Syntaxe** :
```typescript
searchTitre = signal('');          // Création
let value = this.searchTitre();    // Lecture
this.searchTitre.set('Tolkien');   // Écriture
```

**Avantages par rapport aux Observables RxJS** :

1. **Simplicité** : Pas besoin de subscribe/unsubscribe, pas de gestion de memory leaks
2. **Performance** : Angular sait exactement ce qui a changé et ne re-render que le nécessaire
3. **Synchrone** : Pas de complexité asynchrone pour des états simples
4. **Computed values** : Calculs automatiques dérivés d'autres signals
5. **Fine-grained reactivity** : Mise à jour granulaire du DOM

**Exemple dans mon code** :
```typescript
totalPages = signal(1);
pagesArray = computed(() =>
  Array.from({ length: this.totalPages() }, (_, i) => i + 1)
);
```
Quand `totalPages` change, `pagesArray` se recalcule automatiquement.

### Questions sur la Fonctionnalité 1 (Recherche de Livres)

#### Q4 : Comment avez-vous implémenté la recherche multi-critères côté backend ?

**Réponse** :
J'ai utilisé le **QueryBuilder de Doctrine** avec des conditions dynamiques :

```php
$qb = $this->createQueryBuilder('l')
    ->leftJoin('l.auteurs', 'a')->addSelect('a')
    ->leftJoin('l.categories', 'c')->addSelect('c');

if (!empty($titre)) {
    $qb->andWhere('l.titre LIKE :titre')
       ->setParameter('titre', '%' . $titre . '%');
}

if (!empty($auteur)) {
    $qb->andWhere('a.nom LIKE :auteur OR a.prenom LIKE :auteur')
       ->setParameter('auteur', '%' . $auteur . '%');
}
```

**Points clés** :

1. **Conditions dynamiques** : Les `if (!empty())` n'ajoutent les critères que si l'utilisateur a rempli les champs. Si aucun critère n'est fourni, la requête retourne tous les livres.

2. **LEFT JOIN** : J'utilise des jointures externes pour ne pas perdre les livres qui n'ont pas d'auteur ou de catégorie.

3. **LIKE avec %** : Permet une recherche partielle. Par exemple, "Tolk" trouvera "Tolkien".

4. **Paramètres préparés** : `:titre`, `:auteur` sont des placeholders sécurisés contre les injections SQL. Doctrine remplace automatiquement ces valeurs de manière sécurisée.

5. **OR pour auteur** : Je cherche dans `nom` ET `prenom` pour plus de flexibilité (recherche "John" trouve "John Doe").

#### Q5 : Qu'est-ce que le problème N+1 queries et comment l'avez-vous évité ?

**Réponse** :
Le **problème N+1 queries** est un anti-pattern de performance fréquent avec les ORM :

**Exemple du problème** :
```php
// 1 requête pour charger 10 livres
$livres = $repository->findAll();

foreach ($livres as $livre) {
    // 1 requête supplémentaire PAR livre pour charger ses auteurs
    echo $livre->getAuteurs(); // N requêtes en plus !
}
// Total : 1 + 10 = 11 requêtes au lieu d'1 seule !
```

**Ma solution avec addSelect()** :
```php
$qb->leftJoin('l.auteurs', 'a')
   ->addSelect('a')  // Force le chargement immédiat (EAGER loading)
```

**Résultat** : Une seule requête SQL complexe avec JOIN au lieu de N+1 requêtes :
```sql
SELECT l.*, a.*, c.*
FROM livre l
LEFT JOIN livre_auteur la ON ...
LEFT JOIN auteur a ON ...
LEFT JOIN categorie c ON ...
```

**Avantage** :
- 1 requête au lieu de potentiellement des dizaines
- Amélioration drastique des performances
- Moins de charge sur la base de données

#### Q6 : Comment fonctionne la pagination dans votre recherche de livres ?

**Réponse** :
La pagination est gérée **côté backend** pour des raisons de performance :

**1. Backend (PHP)** :
```php
$limit = 9;  // 9 livres par page
$offset = ($page - 1) * $limit;  // Page 1 → offset 0, Page 2 → offset 9

$qb->setFirstResult($offset)    // OFFSET SQL
   ->setMaxResults($limit);      // LIMIT SQL

$paginator = new Paginator($qb, true);
$totalPages = ceil(count($paginator) / $limit);
```

**SQL généré** :
```sql
SELECT ... FROM livre LIMIT 9 OFFSET 0;  -- Page 1
SELECT ... FROM livre LIMIT 9 OFFSET 9;  -- Page 2
```

**2. Frontend (Angular)** :
```typescript
page = signal(1);  // Page actuelle

goToPage(p: number) {
  this.page.set(p);      // Change le numéro de page
  this.loadLivres();      // Recharge avec le nouveau paramètre
}
```

**3. Requête HTTP** :
```
GET /api/livres?page=2&titre=Tolkien
```

**Avantages de la pagination côté serveur** :
- Ne charge que les données nécessaires (9 livres, pas 1000)
- Réduit la bande passante réseau
- Améliore les temps de réponse
- Scalable même avec des millions de livres

#### Q7 : Pourquoi utilisez-vous des groupes de sérialisation Symfony ?

**Réponse** :
Les **groupes de sérialisation** permettent de contrôler quelles propriétés des entités sont exposées dans l'API :

**Problème sans groupes** :
```php
return $this->json($livre);
// ❌ Sérialise TOUT : relations circulaires, données sensibles...
```

**Solution avec groupes** :
```php
// Dans l'entité Livre
#[Groups(['livre:read'])]
private string $titre;

#[Groups(['livre:read'])]
private \DateTimeInterface $dateParution;

// Pas de groupe = pas exposé
private string $infoInterne;

// Dans le controller
return $this->json($livre, 200, [], ['groups' => 'livre:read']);
```

**Avantages** :

1. **Sécurité** : Cache les données sensibles (mots de passe, infos admin)
2. **Performance** : Ne sérialise que le nécessaire
3. **Relations circulaires** : Évite les boucles infinies (Livre → Auteur → Livres → Auteur...)
4. **Flexibilité** : Différents groupes pour différents endpoints (`livre:read`, `livre:write`, `livre:admin`)

**Exemple de réponse JSON** :
```json
{
  "titre": "Le Seigneur des Anneaux",
  "dateParution": "1954-07-29",
  "auteurs": [...]
  // "infoInterne" n'est PAS exposé
}
```

### Questions sur la Fonctionnalité 2 (Recherche d'Auteurs)

#### Q8 : Quelle est la différence principale entre votre recherche de livres et votre recherche d'auteurs ?

**Réponse** :

**Similarités** :
- Même pattern architectural (Component → Service → Controller → Repository)
- Même système de pagination
- Même utilisation des Angular Signals

**Différences** :

| Aspect | Livres | Auteurs |
|--------|--------|---------|
| **Critères de recherche** | 3 critères (titre, auteur, catégorie) | 1 critère (nom/prénom) |
| **Jointures SQL** | 2 LEFT JOIN (auteurs + catégories) | Aucune |
| **Complexité requête** | Haute (relations Many-to-Many) | Faible (table unique) |
| **Recherche combinée** | Critères indépendants (ET) | Nom OU Prénom (OR) |

**Code Repository Auteur** :
```php
// Plus simple : pas de jointures
$qb = $this->createQueryBuilder('a');

if (!empty($nom)) {
    // Recherche dans nom OU prénom avec le même terme
    $qb->andWhere('a.nom LIKE :nom OR a.prenom LIKE :nom')
       ->setParameter('nom', '%' . $nom . '%');
}
```

**Pourquoi cette simplicité ?** :
- L'entité Auteur n'a pas besoin de charger ses livres pour la liste
- La recherche est directe sur les colonnes de la table `auteur`
- Pas de risque de N+1 queries

#### Q9 : Comment gérez-vous le cas où l'utilisateur recherche "Victor Hugo" dans le champ auteur ?

**Réponse** :
Excellente question ! Mon implémentation actuelle recherche le **terme complet** dans nom OU prénom :

```php
$qb->andWhere('a.nom LIKE :nom OR a.prenom LIKE :nom')
   ->setParameter('nom', '%Victor Hugo%');
```

**Résultat SQL** :
```sql
WHERE (nom LIKE '%Victor Hugo%' OR prenom LIKE '%Victor Hugo%')
```

**Ce qui fonctionne** :
- "Victor" → trouve les prénoms "Victor"
- "Hugo" → trouve les noms "Hugo"
- "Victor Hugo" → trouve si c'est dans un seul champ (ex: `nom = "Victor Hugo"`)

**Limitation** : Si la BDD a `prenom = "Victor"` et `nom = "Hugo"` séparés, "Victor Hugo" ne les trouve pas.

**Amélioration possible** (non implémentée) :
```php
// Découper la recherche en mots
$terms = explode(' ', $nom);
foreach ($terms as $i => $term) {
    $qb->andWhere("a.nom LIKE :term$i OR a.prenom LIKE :term$i")
       ->setParameter("term$i", "%$term%");
}
```

**Pour l'oral** : Je reconnais cette limitation et je peux proposer cette amélioration comme évolution future.

### Questions sur les Performances et la Sécurité

#### Q10 : Quelles mesures de sécurité avez-vous mises en place ?

**Réponse** :

**1. Protection contre les injections SQL** :
```php
// ❌ DANGEREUX (SQL injection)
$sql = "SELECT * FROM livre WHERE titre = '" . $titre . "'";

// ✅ SÉCURISÉ (paramètres préparés)
$qb->where('l.titre LIKE :titre')
   ->setParameter('titre', '%' . $titre . '%');
```
Doctrine échappe automatiquement les caractères dangereux.

**2. Authentification JWT** :
- Token crypté avec clé secrète
- Expiration automatique
- Validation à chaque requête API

**3. Autorisation** :
```php
#[IsGranted('ROLE_USER')]
public function mesEmprunts() { ... }
```

**4. Validation des données** :
```php
// Dans l'entité
#[Assert\NotBlank]
#[Assert\Length(max: 255)]
private string $titre;
```

**5. CORS** : Configuration pour n'accepter que les requêtes depuis le frontend autorisé

**6. HTTPS** : Toutes les communications chiffrées (certificat SSL Symfony)

**7. Groupes de sérialisation** : Pas d'exposition de données sensibles

#### Q11 : Comment optimiseriez-vous les performances si vous aviez 1 million de livres ?

**Réponse** :

**Solutions déjà implémentées** :
1. **Pagination** : Limite à 9 résultats par page
2. **Eager loading** : Évite le N+1 queries
3. **Index BDD** : Sur les colonnes de recherche (`titre`, `nom`)

**Améliorations possibles** :

**1. Index de recherche full-text** :
```sql
CREATE FULLTEXT INDEX idx_titre ON livre(titre);
-- Utilisation : WHERE MATCH(titre) AGAINST('Tolkien')
```

**2. Cache Redis** :
```php
$cache->get('livres_page_1', function() {
    return $repository->findBySearchCriteria(...);
});
```
Stocke les résultats fréquents en mémoire.

**3. Elasticsearch** :
```php
// Recherche ultra-rapide dans des millions de documents
$elasticsearch->search(['query' => ['match' => ['titre' => 'Tolkien']]]);
```

**4. CDN** :
- Images de couverture servies depuis un CDN
- Réduit la charge sur le serveur

**5. Lazy loading des images** :
```html
<img loading="lazy" src="...">
```

**6. Vues matérialisées SQL** :
```sql
CREATE MATERIALIZED VIEW livres_stats AS
SELECT ... FROM livre ... ;
```

**7. Query caching Doctrine** :
```php
$qb->getQuery()->useQueryCache(true);
```

### Questions Techniques Avancées

#### Q12 : Expliquez le cycle de vie d'une requête HTTP dans votre application.

**Réponse** :

**Étape par étape** :

```
1. USER : Tape "Tolkien" et clique sur "Rechercher"
   ↓
2. ANGULAR FRONTEND (TypeScript)
   - Événement (click)="onSearch()"
   - searchAuteur.set('Tolkien')
   - page.set(1)
   - loadLivres() appelée
   ↓
3. API SERVICE (HTTP Client)
   - Construit l'URL : /api/livres?auteur=Tolkien&page=1
   - HTTP GET request avec headers (JWT token)
   ↓
4. RÉSEAU
   - Requête HTTPS chiffrée
   - Traverse Internet
   ↓
5. SYMFONY SERVER
   - Reçoit la requête
   - Routing : /api/livres → LivreController::index
   - Vérifie JWT si endpoint protégé
   ↓
6. CONTROLLER (PHP)
   - Parse les paramètres query : $request->query->get('auteur')
   - Appelle le Repository
   ↓
7. REPOSITORY (Doctrine)
   - Construit la requête avec QueryBuilder
   - Crée le SQL avec paramètres préparés
   ↓
8. MARIADB
   - Exécute SELECT avec LIMIT/OFFSET
   - Retourne les lignes de résultats
   ↓
9. DOCTRINE ORM
   - Hydrate les résultats en objets Livre, Auteur, Categorie
   - Retourne au Repository
   ↓
10. REPOSITORY → CONTROLLER
    - Formate en array ['data' => ..., 'totalPages' => ...]
    ↓
11. SYMFONY SERIALIZER
    - Transforme les objets PHP en JSON
    - Applique les groupes de sérialisation
    ↓
12. RESPONSE HTTP
    - Status 200 OK
    - Content-Type: application/json
    - Body : {"data": [...], "totalPages": 5}
    ↓
13. RÉSEAU (retour)
    - Réponse HTTPS chiffrée
    ↓
14. ANGULAR HTTP CLIENT
    - Reçoit la réponse
    - Parse le JSON automatiquement
    - Déclenche le callback subscribe()
    ↓
15. COMPONENT (TypeScript)
    - this.livres.set(response.data)
    - this.totalPages.set(response.totalPages)
    ↓
16. ANGULAR CHANGE DETECTION
    - Détecte les changements de Signals
    - Met à jour le DOM automatiquement
    ↓
17. BROWSER
    - Affiche les nouveaux livres
    - User voit les résultats
```

**Temps total** : ~200-500ms selon la connexion réseau

#### Q13 : Qu'est-ce que le Two-Way Data Binding et où l'utilisez-vous ?

**Réponse** :

**Définition** :
Le Two-Way Data Binding est une synchronisation **bidirectionnelle** entre le modèle (TypeScript) et la vue (HTML).

**Syntaxe Angular** :
```html
<input [(ngModel)]="searchTitre">
```

Le `[(ngModel)]` est un raccourci pour :
```html
<!-- Équivalent développé -->
<input [value]="searchTitre()"
       (input)="searchTitre.set($event.target.value)">
```

**Flux bidirectionnel** :
```
TypeScript Signal ←→ Input HTML
searchTitre = signal('Tolkien')
        ↕
  <input value="Tolkien">
```

**Utilisation dans mon projet** :
```html
<!-- Recherche livres -->
<input [(ngModel)]="searchTitre" type="text"
       placeholder="Titre du livre">
<input [(ngModel)]="searchAuteur" type="text"
       placeholder="Nom de l'auteur">

<!-- Profil utilisateur -->
<input [(ngModel)]="email" type="email">
<input [(ngModel)]="adressePostale" type="text">
```

**Pourquoi c'est pratique** :
1. **Synchronisation automatique** : Pas besoin d'écrire du code pour lier input et modèle
2. **Moins de code** : 1 ligne au lieu de 2 (property binding + event binding)
3. **Réactivité** : Changements instantanés dans les deux sens

**Alternative unidirectionnelle** (quand 2-way pas nécessaire) :
```html
<div>{{ searchTitre() }}</div>  <!-- One-way : model → view -->
```

#### Q14 : Comment fonctionne l'authentification JWT dans votre projet ?

**Réponse** :

**JWT (JSON Web Token)** : Token crypté contenant les informations utilisateur, signé par le serveur.

**Structure d'un JWT** :
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9  ← Header (algorithme)
.
eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6Ik... ← Payload (données utilisateur)
.
SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c  ← Signature (sécurité)
```

**Flux d'authentification** :

**1. Login** :
```typescript
// Frontend
this.authService.login(email, password).subscribe({
  next: (response) => {
    localStorage.setItem('token', response.token);  // Stocke le JWT
  }
});
```

```php
// Backend - AuthController.php
#[Route('/api/auth/login', methods: ['POST'])]
public function login(Request $request) {
    // Vérifie email + password
    // Génère un JWT signé avec la clé secrète
    return $this->json(['token' => $jwt]);
}
```

**2. Requêtes authentifiées** :
```typescript
// Angular HTTP Interceptor
intercept(req: HttpRequest, next: HttpHandler) {
  const token = localStorage.getItem('token');

  // Clone la requête et ajoute le header Authorization
  const authReq = req.clone({
    setHeaders: {
      Authorization: `Bearer ${token}`
    }
  });

  return next.handle(authReq);
}
```

**3. Validation côté serveur** :
```php
// Symfony vérifie automatiquement le JWT
#[Route('/api/mes-emprunts')]
#[IsGranted('ROLE_USER')]  // Vérifie le rôle dans le JWT
public function mesEmprunts() { ... }
```

**Avantages JWT** :
- **Stateless** : Pas de session côté serveur (scalable)
- **Autonome** : Contient toutes les infos (user ID, roles...)
- **Sécurisé** : Signature cryptographique (impossible à forger)
- **Expiration** : Token expiré automatiquement après X heures

#### Q15 : Pouvez-vous expliquer la différence entre leftJoin et innerJoin en SQL ?

**Réponse** :

**INNER JOIN** :
```sql
SELECT l.titre, a.nom
FROM livre l
INNER JOIN livre_auteur la ON l.id = la.livre_id
INNER JOIN auteur a ON la.auteur_id = a.id
```
**Résultat** : Ne retourne QUE les livres qui ONT au moins un auteur.
- Livre avec auteur ✅
- Livre sans auteur ❌ (exclu)

**LEFT JOIN** :
```sql
SELECT l.titre, a.nom
FROM livre l
LEFT JOIN livre_auteur la ON l.id = la.livre_id
LEFT JOIN auteur a ON la.auteur_id = a.id
```
**Résultat** : Retourne TOUS les livres, même ceux sans auteur.
- Livre avec auteur ✅ (avec info auteur)
- Livre sans auteur ✅ (auteur = NULL)

**Pourquoi LEFT JOIN dans mon projet ?** :
```php
$qb->leftJoin('l.auteurs', 'a')
   ->leftJoin('l.categories', 'c')
```

Raisons :
1. **Données incomplètes** : Certains livres peuvent ne pas avoir d'auteur référencé
2. **Flexibilité** : Je veux voir tous les livres, même mal renseignés
3. **Recherche par titre seul** : Si je cherche juste un titre, je veux le livre même s'il n'a pas d'auteur

**Exemple concret** :
```
Base de données :
- Livre 1 : "1984" (auteur: George Orwell)
- Livre 2 : "Livre Mystérieux" (auteur: NULL)

Recherche "Livre" :
- INNER JOIN → 0 résultat (Livre 2 exclu car pas d'auteur)
- LEFT JOIN → 1 résultat (Livre 2 inclus)
```

**Analogie** :
- INNER JOIN = intersection (∩)
- LEFT JOIN = tout à gauche + correspondances à droite

---

## 📊 Points Forts du Projet à Mentionner

### Compétences Techniques Démontrées

1. **Full Stack Development** :
   - Backend : Symfony, PHP, Doctrine ORM, API REST
   - Frontend : Angular, TypeScript, RxJS, Signals
   - Database : MariaDB, SQL, design relationnel

2. **Architecture Moderne** :
   - Séparation frontend/backend
   - API RESTful bien structurée
   - Pattern Repository pour la logique métier

3. **Performances** :
   - Pagination côté serveur
   - Eager loading (pas de N+1 queries)
   - Requêtes optimisées avec index

4. **Sécurité** :
   - Authentification JWT
   - Protection injection SQL
   - Validation des données
   - Groupes de sérialisation

5. **Best Practices** :
   - Code DRY (Don't Repeat Yourself)
   - Séparation des responsabilités
   - Nommage clair et conventions
   - Gestion d'erreurs

### Améliorations Futures Potentielles

1. **Recherche avancée** :
   - Autocomplétion en temps réel
   - Recherche full-text avec Elasticsearch
   - Filtres avancés (date, langue, disponibilité)

2. **Performance** :
   - Cache Redis pour les requêtes fréquentes
   - CDN pour les images
   - Lazy loading des images

3. **UX** :
   - Historique de recherche
   - Suggestions de recherche
   - Sauvegarde des filtres préférés

4. **Fonctionnalités** :
   - Export CSV/PDF des résultats
   - Système de favoris
   - Recommandations de livres

---

## 🎓 Conclusion

Ce projet démontre la maîtrise des **compétences Réaliser et Gérer** à travers :

### Réaliser (Développement)
- Application Full Stack complexe
- Architecture moderne API REST + SPA
- Technologies actuelles (Symfony 7, Angular 21)
- Code de qualité professionnelle

### Gérer (Base de Données)
- Modélisation relationnelle avec 6 entités
- Relations Many-to-Many bien conçues
- Requêtes SQL optimisées
- Gestion de performances (pagination, indexation)

Les **deux fonctionnalités** (recherche de livres et recherche d'auteurs) illustrent parfaitement ces compétences avec une implémentation complète frontend-backend-database.

---

**Document préparé pour l'oral informatique**
*Mohammed Achraf AMERI - SAE S6.A.01*
