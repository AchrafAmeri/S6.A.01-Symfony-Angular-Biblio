export interface Utilisateur {
  id: number;
  email: string;
  roles: string[];
  nom?: string;
  prenom?: string;
  photo?: string;
}