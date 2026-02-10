import { Categorie } from './categorie';

export interface Article {
  id: number;
  titre: string;
  contenu?: string;
  publie: boolean;
  date_creation: string;
  numero_ordre: number;
  categorie?: Categorie;
}
