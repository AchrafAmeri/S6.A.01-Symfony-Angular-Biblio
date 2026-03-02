import { Livre } from './livre';

export interface Reservation {
  id: number;
  livre: Livre;
  dateResa: string;
}