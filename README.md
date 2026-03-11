# SAE S6.A.01 - Projet Gestion de Bibliothèque (Library Manager)

Ce dépôt contient le code source complet de l'application Full Stack de gestion de bibliothèque. Ce projet met en œuvre les compétences "Réaliser" (Développement d'applications complexes avec Angular et PHP/Symfony) et "Gérer" (Conception et exploitation de données avec MySQL/MariaDB).

L'application se compose de deux dossiers principaux :
* `poc_articles` : Back-office et API REST (Symfony 7.4).
* `front_articles` : Front-office public et espace adhérent authentifié (Angular 21).

## Prérequis

* **PHP 8.4** (avec les extensions requises activées dans le `php.ini`)
* **Composer 2.9** (via le fichier `composer.phar` local)
* **Node.js 22** et **npm 11**
* **Angular CLI 21** (utilisé localement via `npx`)
* **Symfony CLI 5.16**
* **MariaDB 10.8.3** (Version portable fournie)

## L'équipe de développement

* [Mohammed Ameri](https://github.com/AchrafAmeri) - Chef de projet / Développeur

* [Pierre-Louis Ducry](https://github.com/Ducry-PL) - Développeur / Tigre

* [Daved Tran](https://github.com/DavidTRANMinhAnh) - Développeur / Testeur

## Installation

### 1. Base de données (MariaDB Portable)
1. Lancez le serveur MariaDB portable via le script `start-server.bat`.
2. Créez la base de données `saepoc` si elle existe pas :  
Via Adminer ou en ligne de commande avec  
`php bin/console doctrine:database:create`
3. Importez les données de test (fixtures de recette) :  
`mysql -u root -p -P 3306 saepoc < dump_recette.sql`

### 2. Back-end (Symfony)
Placez-vous dans le dossier du projet Symfony.
Installez les dépendances en utilisant l'exécutable local de composer (adaptez le chemin si nécessaire) :  
`php ../composer.phar install`

Générez les clés de sécurité pour JWT si elles existent pas :  
`php bin/console lexik:jwt:generate-keypair`

*Vérifiez que votre fichier `.env` contient bien une clé `APP_SECRET` valide et la connexion DB suivante :*  
`DATABASE_URL="mysql://root:root@127.0.0.1:3306/saepoc?serverVersion=10.8.3-MariaDB&charset=utf8mb4"`

### 3. Front-end (Angular)
Placez-vous dans le dossier du projet Angular.
Installez les dépendances Node :
`npm install`

Compilez le projet en utilisant `npx` (pour forcer l'utilisation de la version locale d'Angular CLI) :
`npx ng build --base-href /app/`

*(Copiez ensuite tout le contenu du dossier `dist/front_articles/browser` généré vers le dossier `public/app/` du projet Symfony)*.

En cas de problème avec l'installation, référez-vous au document ci-dessous :  
[Guide d'installation complet](https://htmlpreview.github.io/?https://github.com/AchrafAmeri/S6.A.01-Symfony-Angular-Biblio/blob/main/guide%20d%27installation.html)

## Lancement

L'ensemble du projet (API, Back-office et Front-end Angular) tourne sur un serveur unique. Assurez-vous d'avoir installé le certificat HTTPS (`symfony server:ca:install`), puis lancez la commande suivante depuis le dossier Symfony :
`symfony server:start --port=8008`


## URLs d'accès

* **Front-office Angular (Internaute & Adhérent) :** [https://127.0.0.1:8008/app](https://127.0.0.1:8008/app)
* **Back-office EasyAdmin (Bibliothécaire & Responsable) :** [https://127.0.0.1:8008/admin](https://127.0.0.1:8008/admin)
* **API REST (ex: liste des catégories) :** [https://127.0.0.1:8008/api/categories](https://127.0.0.1:8008/api/categories)
* **Base de données (via Adminer) :** [https://127.0.0.1:8008/adminer/](https://127.0.0.1:8008/adminer/) *(Logins: root / root)*


## Comptes de test (Fixtures SQL)

Le fichier `dump_recette.sql` contient les utilisateurs suivants pour dérouler le cahier de recette :

| Rôle | Email | Mot de passe |
| :--- | :--- | :--- |
| **Responsable Bibliothèque** | `admin@articles.fr` | `admin` |
| **Bibliothécaire** | `biblio@articles.fr` | `biblio` |
| **Adhérent** | `adherent0@articles.fr` | `password` |
| **Adhérent** | `adherent1@articles.fr` | `password` |
