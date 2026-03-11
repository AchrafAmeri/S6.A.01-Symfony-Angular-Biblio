-- Adminer 5.4.1 MariaDB 10.8.3-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `saepoc`;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `auteur`;
CREATE TABLE `auteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `date_deces` date DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `auteur` (`id`, `nom`, `prenom`, `date_naissance`, `date_deces`, `nationalite`, `photo`, `description`) VALUES
(1,	'Mace',	'Jacqueline',	'1969-02-08',	NULL,	'Swaziland',	'https://picsum.photos/seed/auteur0/200/300',	'Neque occaecati corporis magnam magnam esse saepe ut. Omnis blanditiis commodi non sint officiis vero modi dolor. Repellat ea ea accusantium possimus repellendus.'),
(2,	'Bruneau',	'Dominique',	'1963-12-04',	'2023-05-31',	'Aruba',	'https://picsum.photos/seed/auteur1/200/300',	'Voluptatem ut quis architecto vel adipisci. Fugiat cupiditate quam eos nisi ipsum qui similique. Dolores cumque tempora qui et repudiandae tempore temporibus eum.'),
(3,	'Jean',	'Capucine',	'1987-02-09',	NULL,	'Pologne',	'https://picsum.photos/seed/auteur2/200/300',	'Tempora aut fuga nemo atque sequi officia iste. Eos repudiandae dolorum sed quae.'),
(4,	'Muller',	'Anne',	'1978-04-11',	NULL,	'Îles Mineures Éloignées des États-Unis',	'https://picsum.photos/seed/auteur3/200/300',	'Incidunt ut est non. Eos assumenda maxime voluptatem rem. Amet iusto veniam vel temporibus dolor consectetur ad.'),
(5,	'Guillet',	'Adrien',	'1978-11-01',	'2023-01-30',	'Turkménistan',	'https://picsum.photos/seed/auteur4/200/300',	'Aut pariatur nihil sed atque. Magni voluptas itaque sit deleniti. Nobis voluptatibus perspiciatis sunt blanditiis eos maiores veritatis. Non fugiat est aperiam et repellat nesciunt inventore tempore.');

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE `categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categorie` (`id`, `nom`, `description`) VALUES
(4,	'Science-Fiction',	'Odio qui eius ab expedita. Omnis ab dicta dolore quis ea unde. Est temporibus quia minima maiores qui rerum non. Veniam dolorum exercitationem id sunt quo est eligendi. Quas delectus voluptas modi aut.'),
(5,	'Roman Policier',	'Qui tenetur et nobis dolorem qui. Repellat neque odit similique velit ullam ea.'),
(6,	'Fantasy',	'Fugiat explicabo fugiat magnam nam. Sunt laborum quos ab quis rerum. Omnis sunt dolore harum ut ut amet. Est repellendus est non nesciunt quod.'),
(7,	'Documentaire',	'Nisi sunt laborum autem magnam molestiae. Similique reiciendis consequatur cupiditate. Rerum iste tenetur et. Non dolores praesentium inventore repellat dolor omnis ex.'),
(8,	'Bande Dessinée',	'Sequi ipsam saepe omnis nostrum sint. Quae quo quam est reprehenderit. Quibusdam molestiae soluta rerum ratione rerum. Ipsa libero consequatur veniam.');

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260209103606',	'2026-02-09 10:44:41',	82),
('DoctrineMigrations\\Version20260228112342',	'2026-02-28 11:23:51',	399),
('DoctrineMigrations\\Version20260228113618',	'2026-02-28 11:36:25',	293);

DROP TABLE IF EXISTS `emprunt`;
CREATE TABLE `emprunt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_emprunt` datetime NOT NULL,
  `date_retour` datetime DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_364071D7FB88E14F` (`utilisateur_id`),
  KEY `IDX_364071D737D925CB` (`livre_id`),
  CONSTRAINT `FK_364071D737D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id`),
  CONSTRAINT `FK_364071D7FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `emprunt` (`id`, `date_emprunt`, `date_retour`, `utilisateur_id`, `livre_id`) VALUES
(1,	'2026-02-23 05:51:55',	'2026-02-24 03:20:11',	6,	11),
(2,	'2026-02-03 00:12:53',	NULL,	10,	13),
(3,	'2026-02-13 18:23:00',	'2026-03-04 12:59:00',	12,	2),
(4,	'2026-02-06 11:26:08',	'2026-02-27 07:00:04',	8,	7),
(5,	'2026-01-31 17:08:15',	'2026-02-21 19:19:55',	12,	6),
(6,	'2026-02-24 20:56:53',	NULL,	7,	11),
(7,	'2026-02-19 15:26:46',	'2026-02-25 19:18:24',	9,	17),
(8,	'2026-03-01 03:20:09',	'2026-03-01 09:34:27',	14,	5),
(9,	'2026-02-15 12:10:59',	NULL,	7,	17),
(10,	'2026-02-02 22:01:22',	'2026-02-24 19:12:48',	14,	18),
(11,	'2026-02-19 12:15:04',	NULL,	10,	13),
(12,	'2026-02-09 08:28:30',	NULL,	13,	11),
(13,	'2026-02-01 10:08:57',	'2026-02-28 04:04:41',	10,	3),
(14,	'2026-02-08 00:03:39',	'2026-02-20 03:33:46',	7,	15),
(15,	'2026-02-24 19:38:02',	NULL,	14,	11),
(16,	'2026-03-04 12:56:00',	NULL,	6,	3),
(17,	'2026-03-04 12:11:48',	NULL,	6,	2),
(18,	'2026-03-04 12:12:27',	NULL,	6,	4),
(19,	'2026-03-04 12:13:35',	'2026-03-04 12:21:40',	6,	5),
(20,	'2026-03-04 12:13:38',	NULL,	6,	6);

DROP TABLE IF EXISTS `livre`;
CREATE TABLE `livre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `date_sortie` date DEFAULT NULL,
  `langue` varchar(50) NOT NULL,
  `photo_couverture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `livre` (`id`, `titre`, `date_sortie`, `langue`, `photo_couverture`) VALUES
(1,	'Beatae asperiores aut',	'1976-06-01',	'Espagnol',	'https://picsum.photos/seed/livre0/300/400'),
(2,	'Natus mollitia ut',	'2011-08-20',	'Espagnol',	'https://picsum.photos/seed/livre1/300/400'),
(3,	'Voluptas eum tenetur',	'2008-02-20',	'Français',	'https://picsum.photos/seed/livre2/300/400'),
(4,	'Libero rem consequatur',	'2020-02-11',	'Anglais',	'https://picsum.photos/seed/livre3/300/400'),
(5,	'Enim vel eos',	'1980-09-26',	'Espagnol',	'https://picsum.photos/seed/livre4/300/400'),
(6,	'Fugiat animi nobis',	'2000-09-04',	'Français',	'https://picsum.photos/seed/livre5/300/400'),
(7,	'Illum quis quis',	'1997-11-28',	'Anglais',	'https://picsum.photos/seed/livre6/300/400'),
(8,	'Repellat rerum unde',	'1986-07-01',	'Anglais',	'https://picsum.photos/seed/livre7/300/400'),
(9,	'Iure tempore praesentium',	'2001-11-07',	'Français',	'https://picsum.photos/seed/livre8/300/400'),
(10,	'Quis consequuntur ut',	'2009-04-25',	'Espagnol',	'https://picsum.photos/seed/livre9/300/400'),
(11,	'Molestias magni qui',	'1984-12-20',	'Espagnol',	'https://picsum.photos/seed/livre10/300/400'),
(12,	'Ea velit perferendis',	'1999-08-06',	'Anglais',	'https://picsum.photos/seed/livre11/300/400'),
(13,	'Quod nihil et',	'1989-07-28',	'Français',	'https://picsum.photos/seed/livre12/300/400'),
(14,	'Minima consectetur maiores',	'2003-12-25',	'Français',	'https://picsum.photos/seed/livre13/300/400'),
(15,	'Voluptas ullam incidunt',	'2004-07-08',	'Anglais',	'https://picsum.photos/seed/livre14/300/400'),
(16,	'Eos iusto sit',	'1993-07-29',	'Espagnol',	'https://picsum.photos/seed/livre15/300/400'),
(17,	'Placeat voluptatum qui',	'2002-10-03',	'Espagnol',	'https://picsum.photos/seed/livre16/300/400'),
(18,	'Dignissimos fuga quod',	'2009-03-11',	'Français',	'https://picsum.photos/seed/livre17/300/400'),
(19,	'Recusandae harum voluptate',	'1984-12-25',	'Français',	'https://picsum.photos/seed/livre18/300/400'),
(20,	'Rerum rerum earum',	'1986-01-30',	'Espagnol',	'https://picsum.photos/seed/livre19/300/400'),
(21,	'ffefezfeeze',	NULL,	'DDIJH',	'fefezfzef');

DROP TABLE IF EXISTS `livre_auteur`;
CREATE TABLE `livre_auteur` (
  `livre_id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  PRIMARY KEY (`livre_id`,`auteur_id`),
  KEY `IDX_A11876B537D925CB` (`livre_id`),
  KEY `IDX_A11876B560BB6FE6` (`auteur_id`),
  CONSTRAINT `FK_A11876B537D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A11876B560BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `auteur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `livre_auteur` (`livre_id`, `auteur_id`) VALUES
(1,	1),
(1,	4),
(2,	3),
(2,	4),
(3,	5),
(4,	1),
(5,	1),
(6,	1),
(7,	1),
(7,	2),
(8,	1),
(8,	4),
(9,	1),
(10,	5),
(11,	3),
(11,	4),
(12,	1),
(12,	2),
(13,	1),
(13,	3),
(14,	5),
(15,	3),
(16,	1),
(16,	3),
(17,	2),
(18,	2),
(19,	4),
(20,	2),
(21,	1),
(21,	2);

DROP TABLE IF EXISTS `livre_categorie`;
CREATE TABLE `livre_categorie` (
  `livre_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  PRIMARY KEY (`livre_id`,`categorie_id`),
  KEY `IDX_E61B069E37D925CB` (`livre_id`),
  KEY `IDX_E61B069EBCF5E72D` (`categorie_id`),
  CONSTRAINT `FK_E61B069E37D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E61B069EBCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `livre_categorie` (`livre_id`, `categorie_id`) VALUES
(1,	5),
(1,	7),
(1,	8),
(2,	5),
(2,	8),
(3,	5),
(3,	7),
(3,	8),
(4,	4),
(5,	5),
(5,	7),
(5,	8),
(6,	5),
(7,	4),
(7,	6),
(8,	5),
(8,	6),
(8,	8),
(9,	4),
(9,	8),
(10,	7),
(11,	6),
(12,	5),
(12,	7),
(13,	4),
(13,	7),
(14,	4),
(14,	6),
(14,	7),
(15,	4),
(15,	7),
(16,	4),
(16,	5),
(16,	8),
(17,	5),
(17,	7),
(17,	8),
(18,	4),
(18,	6),
(19,	4),
(19,	6),
(19,	8),
(20,	5),
(20,	6),
(20,	7),
(21,	5),
(21,	6);

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_resa` datetime NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4DA239FB88E14F` (`utilisateur_id`),
  KEY `IDX_4DA23937D925CB` (`livre_id`),
  CONSTRAINT `FK_4DA23937D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id`),
  CONSTRAINT `FK_4DA239FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reservations` (`id`, `date_resa`, `utilisateur_id`, `livre_id`) VALUES
(1,	'2026-03-02 14:01:36',	5,	1),
(9,	'2026-03-04 12:13:01',	6,	7);

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_adhesion` datetime NOT NULL,
  `date_naiss` date DEFAULT NULL,
  `adresse_postale` varchar(255) DEFAULT NULL,
  `num_tel` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `utilisateur` (`id`, `email`, `roles`, `password`, `nom`, `prenom`, `date_adhesion`, `date_naiss`, `adresse_postale`, `num_tel`, `photo`) VALUES
(3,	'admin@articles.fr',	'[\"ROLE_ADMIN\"]',	'$2y$13$VALN/VXEk6t/Fun19Uffn.4/HTGil80B521CYYrM4Nd/UO93zW4u.',	'Responsable',	'Jean',	'2026-03-01 09:59:03',	'1980-01-01',	NULL,	NULL,	NULL),
(4,	'biblio@articles.fr',	'[\"ROLE_BIBLIO\"]',	'$2y$13$8B1s9UFiIj84FOHV7q83hO/cIDHmCFbbXab3AJMmgxH7CASDP0GEm',	'Bibliothecaire',	'Marie',	'2026-03-01 09:59:03',	'1990-05-15',	NULL,	NULL,	NULL),
(5,	'adherent0@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$SetwKQPGuB2WNu/QuRFiCOffwqpCvARt6PnZSwgELi7FKHNUTNqGm',	'Evrard',	'Lucy',	'2025-07-02 23:54:05',	'2001-07-23',	'961, rue Ramos\n25929 Launay-sur-Guillaume',	'+33 (0)6 99 18 94 44',	'https://picsum.photos/seed/user0/150/150'),
(6,	'adherent1@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$fDlPxbGLdvsLr1mhGH60ROAZFATtV2PhTc.7GeeLAZvoccZsJKhhO',	'Carre',	'Virginie',	'2024-05-12 11:36:43',	'1997-03-07',	'51, rue Lefevre\n16729 Lambert',	'07 67 76 73 08',	'https://picsum.photos/seed/user1/150/150'),
(7,	'adherent2@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$Ttj8Hiqfukb1qsPo20ulGuoXaBoyvDB8dioTCFMSFrfuUmRVEt1xy',	'Guillon',	'Thibaut',	'2024-10-18 03:26:51',	'1972-08-09',	'6, rue de Da Silva\n02175 Duhamel',	'0637809970',	'https://picsum.photos/seed/user2/150/150'),
(8,	'adherent3@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$cCn.5H2Qr2ht0WVA.cPF/eyIbZ6f6I6LTHRXQME4fgR7brzMtVGAK',	'Renault',	'Thibaut',	'2026-01-13 07:08:20',	'1968-12-30',	'rue de Benard\n15684 Perretnec',	'0633952235',	'https://picsum.photos/seed/user3/150/150'),
(9,	'adherent4@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$uBsAnEqcM2R86e83BldpL.7C4dK3wZhYHMg6HmYkCJPQOkmuWQgqC',	'Fouquet',	'Cécile',	'2025-02-01 03:24:52',	'1975-12-22',	'882, avenue Traore\n09110 Schmitt',	'0744515862',	'https://picsum.photos/seed/user4/150/150'),
(10,	'adherent5@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$ts/eiv.Rji0mSox.rsPJ1epLwtmBDvEYAD4.uc3Qd4Lsp0htlmdMy',	'Marie',	'Jérôme',	'2025-10-30 04:03:54',	'1991-02-18',	'boulevard de Diaz\n39099 Labbenec',	'+33 (0)6 99 83 98 94',	'https://picsum.photos/seed/user5/150/150'),
(11,	'adherent6@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$eDKiXR/671jFOul3JmY84O3ItY.DJ6ezw4FPE1OPHEs4JXSTW1l0q',	'Vaillant',	'Patrick',	'2025-10-08 15:43:23',	'1988-10-23',	'8, rue Blot\n79124 Francois',	'07 78 66 98 58',	'https://picsum.photos/seed/user6/150/150'),
(12,	'adherent7@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$nv4X/TW8m0YV5VEPtlY/quMOK7zP2GYBHHtUQC1e9QxgXmZAoAliS',	'Moulin',	'Laure',	'2024-06-14 15:33:39',	'1970-08-31',	'impasse Pinto\n89251 Giraud-la-Forêt',	'0632980562',	'https://picsum.photos/seed/user7/150/150'),
(13,	'adherent8@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$01Z0KUp7vL4UBdeygHOWMeOhbxIjEq5ZbZMof2nHfHcZbIG5jPwTG',	'Berthelot',	'Josette',	'2025-01-04 02:47:23',	'1974-12-15',	'place de Jean\n89675 Paul',	'0788968904',	'https://picsum.photos/seed/user8/150/150'),
(14,	'adherent9@articles.fr',	'[\"ROLE_USER\"]',	'$2y$13$ki8uf9mfkil6AXt7/.FzL.mIgkUIToNf9qyZGuzQuBasFVRdcrfDq',	'Fontaine',	'Alfred',	'2024-09-30 00:00:00',	'2001-12-17',	'84, rue de David88123 Le Goff',	'+33 6 42 32 42 18',	'https://picsum.photos/seed/user9/150/150');

-- 2026-03-11 18:33:16 UTC
