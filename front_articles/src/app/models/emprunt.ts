import { Livre } from './livre';

export interface Emprunt {
  id: number;
  dateEmprunt: string;
  dateRetour?: string;
  livre: Livre;
}