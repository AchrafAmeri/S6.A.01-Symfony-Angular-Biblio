export interface Utilisateur {
  id: number;
  email: string;
  roles: string[];
  nom?: string;
  prenom?: string;
  dateAdhesion?: string;
  dateNaiss?: string;
  adressePostale?: string;
  numTel?: string;
  photo?: string;
}