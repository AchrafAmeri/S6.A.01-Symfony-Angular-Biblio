# Cahier de Recette
## Système de Gestion de Bibliothèque - Symfony + Angular

---

## 1. Informations du Document

| **Propriété** | **Valeur** |
|---------------|-----------|
| **Projet** | Système de Gestion de Bibliothèque |
| **Auteur** | Équipe Projet SAE S6.A.01 |
| **Technologies** | Symfony 7.4.1, Angular, API Platform, JWT Auth |

---

## 2. Environnement de Test

| **Composant** | **Configuration** |
|--------------|------------------|
| **Backend** | Symfony 7.4.1 + PHP 8.4.18 |
| **Frontend** | Angular (standalone components) |
| **Base de données** | MySQL/MariaDB |
| **Serveur Backend** | http://localhost:8008 |
| **Serveur Frontend** | http://localhost:4200 |
| **API** | REST JSON |

### 2.1 Utilisateurs de Test

| **Email** | **Mot de passe** | **Rôles** | **Usage** |
|-----------|-----------------|-----------|-----------|
| admin@articles.fr | admin | ROLE_ADMIN, ROLE_BIBLIO, ROLE_USER | Tests administrateur |
| biblio@articles.fr | biblio | ROLE_BIBLIO, ROLE_USER | Tests bibliothécaire |
| adherent1@articles.fr | password | ROLE_USER | Tests adhérent standard |

---

## 3. Batteries de Tests

Note capture: quand il existe une suite d'image, utiliser le format `captures/test-N-2.png` (exemple: `test-1-2.png` est la suite de `test-1.png`).

### 3.1 Front-Office Public (Consultation, Recherche)

| # | Scénario | Prérequis | Actions | Résultat attendu | Rôle | Capture d'écran |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Consultation liste des livres | Base contient des livres | Accéder à la page d'accueil ou `/livres`, consulter la liste paginée | Liste de livres affichée avec titres, auteurs, catégories, bouton pour voir les détail. Pagination fonctionnelle. | Public | ![test-1](captures/test-1.png)<br>![test-1-2](captures/test-1-2.png) |
| 2 | Consultation détails d'un livre | Livre ID=1 existant | Cliquer sur un livre, consulter ses détails | Affichage du titre, date de sortie, langue, photo, liste des auteurs et catégories, statut (disponible/réservé/emprunté). | Public | ![test-2](captures/test-2.png) |
| 3 | Recherche de livres par titre | Livres contenant "Harry" | Saisir "Harry" dans barre de recherche, valider | Résultats filtrés affichant uniquement les livres contenant "Harry" dans le titre. | Public | ![test-3](captures/test-3.png) |
| 4 | Recherche de livres par auteur | Auteurs en base | Utiliser filtre auteur avec "Rowling" | Affichage des livres de cet auteur uniquement. | Public | ![test-4](captures/test-4.png) |
| 5 | Consultation liste des auteurs | Auteurs en base | Accéder à `/auteurs` | Liste des auteurs avec nom, prénom, photo. | Public | ![test-5](captures/test-5.png) |

### 3.2 Front-Office Adhérent (Connexion, Réservation, Annulation, Profil)

| # | Scénario | Prérequis | Actions | Résultat attendu | Rôle | Capture d'écran |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 6 | Connexion adhérent | Compte adherent1@articles.fr / password existant | Saisir email + mot de passe, cliquer Connexion | Connexion à son espace et accès à son espace. | Adhérent | ![test-6](captures/test-6.png) |
| 7 | Réservation d'un livre disponible | Adhérent connecté, livre disponible | Sur détails du livre, cliquer "Réserver" | Message de succès "Livre réservé avec succès !", réservation créée en base avec date actuelle. | Adhérent | ![test-7](captures/test-7.png) |
| 8 | Annulation d'une réservation | Adhérent connecté, réservation ID=3 lui appartenant | Accéder à "Mes réservations", cliquer "Annuler" sur une réservation | Réservation supprimée, message "Réservation annulée avec succès." | Adhérent | ![test-6](captures/test-6.png) |
| 9 | Consultation de son profil | Adhérent connecté | Accéder à "Mon profil" | Affichage email, nom, prénom, date adhésion, adresse, téléphone, rôles. | Adhérent | ![test-6](captures/test-6.png) |
| 10 | Modification de son profil | Adhérent connecté | Modifier adresse et téléphone, enregistrer | Profil mis à jour, nouvelles valeurs affichées. | Adhérent | ![test-10](captures/test-10.png) |
| 11 | Tentative de réserver avec limite atteinte | Adhérent avec 3 réservations actives | Tenter de réserver un 4ème livre | Message d'erreur "Limite de 3 réservations atteinte." Aucune réservation créée. HTTP 409 | Adhérent | ![test-11](captures/test-11.png) |

### 3.3 Back-Office Bibliothécaire (Emprunt, Retour, Gestion)

| # | Scénario | Prérequis | Actions | Résultat attendu | Rôle | Capture d'écran |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 12 | Accès au dashboard bibliothécaire | Connecté avec biblio@articles.fr / biblio123 (ROLE_BIBLIO) | Saisir email + mot de passe, cliquer Connexion | Dashboard affiché avec statistiques (nb livres, adhérents, emprunts en cours, catégories), menu de navigation. | Bibliothécaire | ![test-12](captures/test-12.png) |
| 13 | Création d'un emprunt | Connexion ROLE_BIBLIO, adhérent et livre disponibles | Aller dans Emprunts -> Ajouter, sélectionner adhérent + livre, enregistrer | Emprunt créé avec dateEmprunt = maintenant, dateRetour = null (en cours), visible dans liste des emprunts. | Bibliothécaire | ![test-13](captures/test-13.png) |
| 14 | Enregistrement du retour d'un livre | Emprunt en cours (dateRetour = null) | Accéder à Emprunts, modifier l'emprunt, renseigner date de retour, enregistrer | dateRetour mise à jour, emprunt marqué comme terminé, livre redevient disponible. | Bibliothécaire | ![test-14](captures/test-14.png) |
| 15 | Consultation des emprunts en retard | Emprunt avec dateEmprunt > 30 jours et dateRetour = null | Consulter la liste des emprunts | Badge rouge "Oui" affiché dans colonne "En retard ?" pour les emprunts dépassant 30 jours. | Bibliothécaire | ![test-13](captures/test-13.png) |
| 16 | Transformer réservation en emprunt | Avoir des réservations | Dans Réservations, cliquer "Transformer en emprunt" (bouton vert) | Emprunt créé avec même adhérent et livre, réservation supprimée, message de confirmation affiché. | Bibliothécaire | ![test-16](captures/test-16.png) |
| 17 | Export PDF d'un emprunt | Emprunt existant | Dans liste Emprunts, cliquer "Générer reçu PDF" | Fichier PDF téléchargé contenant nom adhérent, titre livre, dates d'emprunt et retour prévue. | Bibliothécaire | ![test-17](captures/test-17.png) |

### 3.4 Back-Office Responsable (CRUD, Statistiques, Gestion Accès)

| # | Scénario | Prérequis | Actions | Résultat attendu | Rôle | Capture d'écran |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 18 | Création d'un nouveau livre | Connecté avec ROLE_BIBLIO, auteurs et catégories existants | Accéder à Livres -> Ajouter, remplir titre, date sortie, langue, photo, sélectionner auteurs et catégories, enregistrer | Livre créé en base, message de confirmation, livre visible dans liste. | Responsable/Bibliothécaire | ![test-18](captures/test-18.png) |
| 19 | Modification d'un livre | Livre existant, connecté ROLE_BIBLIO | Accéder à Livres, cliquer Modifier sur livre choisi, changer titre, enregistrer | Livre mis à jour en base avec nouveau titre. | Responsable/Bibliothécaire | ![test-19](captures/test-19.png) |
| 20 | Suppression d'un livre | Livre sans emprunts ni réservations, ROLE_BIBLIO | Sélectionner un livre, cliquer Supprimer, confirmer | Livre supprimé de la base, message de confirmation. | Responsable/Bibliothécaire | ![test-20](captures/test-20.png) |
| 21 | Consultation statistiques globales | Connecté avec admin@articles.fr / admin (ROLE_ADMIN) | Accéder à dashboard admin ou envoyer GET `/api/admin/stats` avec token JWT | JSON contenant totaux (livres, adhérents, emprunts en cours) + historique mensuel. | Responsable | ![test-21](captures/test-21.png) |
| 22 | Modification des rôles d'un adhérent | Connecté ROLE_ADMIN | Accéder à Adhérents, modifier un adhérent, ajouter ROLE_BIBLIO, enregistrer | Rôles mis à jour, adhérent hérite des permissions ROLE_BIBLIO + ROLE_USER. | Responsable | ![test-22](captures/test-22.png) |
| 23 | Interdiction d'accès admin pour ROLE_USER | Connecté avec adherent@articles.fr (ROLE_USER uniquement) | Tenter d'accéder à `/admin` | Redirection vers `/sso/to-angular` (Angular), pas d'accès au dashboard admin. | Adhérent | ![test-23](captures/test-23.png) |

### 3.5 API REST (Requêtes Publiques et Protégées)

| # | Scénario | Prérequis | Actions | Résultat attendu | Rôle | Capture d'écran |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 24 | Requête API publique - Liste des livres | Aucune authentification | Envoyer GET `/api/livres?page=1` sans header Authorization | JSON avec tableau de livres + pagination (currentPage, totalPages, totalItems, itemsPerPage). | Public | ![test-24](captures/test-24.png) |
| 25 | Requête API protégée - Récupération profil adhérent | Token JWT valide d'un adhérent | Envoyer GET `/api/user/me` avec header `Authorization: Bearer {token}` | JSON profil utilisateur (id, email, nom, prenom, dateAdhesion, roles, adresse, téléphone). | Adhérent | ![test-25](captures/test-25.png) |
| 26 | Requête API protégée sans authentification | Aucun token | Envoyer GET `/api/user/me` sans header Authorization | HTTP 401 Unauthorized, message "JWT Token not found" ou équivalent. | - | ![test-26](captures/test-26.png) |

---

**Fin du Cahier de Recette**
