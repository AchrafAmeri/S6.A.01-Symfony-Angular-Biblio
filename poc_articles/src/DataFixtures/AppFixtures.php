<?php

namespace App\DataFixtures;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    // Injection du service pour hasher les mots de passe
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Initialisation de Faker en français
        $faker = Factory::create('fr_FR');

        // 1. Création des Catégories (5)
        $categories = [];
        $nomsCategories = ['Science-Fiction', 'Roman Policier', 'Fantasy', 'Documentaire', 'Bande Dessinée'];
        foreach ($nomsCategories as $nom) {
            $categorie = new Categorie();
            $categorie->setNom($nom)
                      ->setDescription($faker->paragraph());
            $manager->persist($categorie);
            $categories[] = $categorie;
        }

        // 2. Création des Auteurs (5)
        $auteurs = [];
        for ($i = 0; $i < 5; $i++) {
            $auteur = new Auteur();
            $auteur->setNom($faker->lastName())
                   ->setPrenom($faker->firstName())
                   ->setDateNaissance($faker->dateTimeBetween('-80 years', '-30 years'))
                   // 1 chance sur 3 que l'auteur soit décédé
                   ->setDateDeces($faker->boolean(30) ? $faker->dateTimeBetween('-20 years', 'now') : null)
                   ->setNationalite($faker->country())
                   ->setPhoto('https://picsum.photos/seed/auteur'.$i.'/200/300')
                   ->setDescription($faker->text(200));
            $manager->persist($auteur);
            $auteurs[] = $auteur;
        }

        // 3. Création des Livres (20)
        $livres = [];
        $langues = ['Français', 'Anglais', 'Espagnol'];
        for ($i = 0; $i < 20; $i++) {
            $livre = new Livre();
            $livre->setTitre(ucfirst($faker->words(3, true)))
                  ->setDateSortie($faker->dateTimeBetween('-50 years', 'now'))
                  ->setLangue($faker->randomElement($langues))
                  ->setPhotoCouverture('https://picsum.photos/seed/livre'.$i.'/300/400');

            // Ajout de 1 à 3 catégories au hasard
            $nbCategories = $faker->numberBetween(1, 3);
            $randomCategories = $faker->randomElements($categories, $nbCategories);
            foreach ($randomCategories as $cat) {
                $livre->addCategory($cat); // Attention: vérifie si ta méthode s'appelle addCategory ou addCategorie
            }

            // Ajout de 1 à 2 auteurs au hasard
            $nbAuteurs = $faker->numberBetween(1, 2);
            $randomAuteurs = $faker->randomElements($auteurs, $nbAuteurs);
            foreach ($randomAuteurs as $aut) {
                $livre->addAuteur($aut);
            }

            $manager->persist($livre);
            $livres[] = $livre;
        }

        // 4. Création des Utilisateurs / Adhérents (10 + 2 Staff)
        $adherents = [];

        // --- Le Responsable (Admin) ---
        $admin = new Utilisateur();
        $admin->setEmail('admin@articles.fr')
              ->setRoles(['ROLE_ADMIN'])
              ->setPassword($this->hasher->hashPassword($admin, 'admin'))
              ->setNom('Responsable')
              ->setPrenom('Jean')
              ->setDateAdhesion(new \DateTime())
              ->setDateNaiss(new \DateTime('1980-01-01'));
        $manager->persist($admin);

        // --- Le Bibliothécaire ---
        $biblio = new Utilisateur();
        $biblio->setEmail('biblio@articles.fr')
               ->setRoles(['ROLE_BIBLIO'])
               ->setPassword($this->hasher->hashPassword($biblio, 'biblio'))
               ->setNom('Bibliothecaire')
               ->setPrenom('Marie')
               ->setDateAdhesion(new \DateTime())
               ->setDateNaiss(new \DateTime('1990-05-15'));
        $manager->persist($biblio);

        // --- Les 10 Adhérents ---
        for ($i = 0; $i < 10; $i++) {
            $adherent = new Utilisateur();
            $adherent->setEmail("adherent$i@articles.fr")
                     ->setRoles(['ROLE_USER'])
                     ->setPassword($this->hasher->hashPassword($adherent, 'password'))
                     ->setNom($faker->lastName())
                     ->setPrenom($faker->firstName())
                     ->setDateAdhesion($faker->dateTimeBetween('-3 years', 'now'))
                     ->setDateNaiss($faker->dateTimeBetween('-60 years', '-15 years'))
                     ->setAdressePostale($faker->address())
                     ->setNumTel($faker->mobileNumber())
                     ->setPhoto('https://picsum.photos/seed/user'.$i.'/150/150');
            
            $manager->persist($adherent);
            $adherents[] = $adherent;
        }

        // 5. Création des Emprunts (15)
        for ($i = 0; $i < 15; $i++) {
            $emprunt = new Emprunt();
            
            // On prend un adhérent au hasard et un livre au hasard
            $randomAdherent = $faker->randomElement($adherents);
            $randomLivre = $faker->randomElement($livres);
            
            // Date d'emprunt dans le passé (entre -30 jours et aujourd'hui)
            $dateEmprunt = $faker->dateTimeBetween('-30 days', 'now');
            $emprunt->setDateEmprunt($dateEmprunt)
                    ->setUtilisateur($randomAdherent)
                    ->setLivre($randomLivre);

            // 1 chance sur 2 que le livre ait déjà été retourné
            if ($faker->boolean(50)) {
                // S'il est rendu, la date de retour est entre la date d'emprunt et aujourd'hui
                $emprunt->setDateRetour($faker->dateTimeBetween($dateEmprunt->format('Y-m-d H:i:s'), 'now'));
            }

            $manager->persist($emprunt);
        }

        // 6. Sauvegarde en Base de Données
        $manager->flush();
    }
}