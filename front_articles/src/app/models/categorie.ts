import { Article } from './article';

export interface Categorie {
  id: number;
  libelle: string;
  articles?: Article[];
}
