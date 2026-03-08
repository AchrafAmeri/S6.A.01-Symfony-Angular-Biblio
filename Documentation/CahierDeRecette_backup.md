# Cahier de Recette
## Système de Gestion de Bibliothèque - Symfony + Angular

---

## 1. Informations du Document

| **Propriété** | **Valeur** |
|---------------|-----------|
| **Projet** | Système de Gestion de Bibliothèque |
| **Version** | 1.0 |
| **Date** | 08/03/2026 |
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
| **Authentification** | JWT (Lexik Bundle) avec clés RS256 |
| **API** | REST JSON |

### 2.1 Utilisateurs de Test

| **Email** | **Mot de passe** | **Rôles** | **Usage** |
|-----------|-----------------|-----------|-----------|
| admin@biblio.fr | admin123 | ROLE_ADMIN, ROLE_BIBLIO, ROLE_USER | Tests administrateur |
| biblio@biblio.fr | biblio123 | ROLE_BIBLIO, ROLE_USER | Tests bibliothécaire |
| user@biblio.fr | user123 | ROLE_USER | Tests adhérent standard |

---

## 3. Batteries de Tests
