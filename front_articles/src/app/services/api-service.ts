import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Categorie } from '../models/categorie';
import { Livre } from '../models/livre';
import { Emprunt } from '../models/emprunt';
import { Reservation } from '../models/reservation';
import { Auteur } from '../models/auteur';
import { Utilisateur } from '../models/utilisateur';

// Interface pour la pagination
export interface PaginatedLivres {
  data: Livre[];
  totalPages: number;
  currentPage: number;
}

@Injectable({
  providedIn: 'root',
})
export class ApiService {
  private http = inject(HttpClient);
  private apiUrl = 'https://127.0.0.1:8008/api';

  getCategories(): Observable<Categorie[]> {
    return this.http.get<Categorie[]>(`${this.apiUrl}/categories`);
  }

  getLivres(titre: string = '', auteur: string = '', categorie: string = '', page: number = 1): Observable<PaginatedLivres> {
    let params = new HttpParams().set('page', page.toString());
    
    if (titre) params = params.set('titre', titre);
    if (auteur) params = params.set('auteur', auteur);
    if (categorie) params = params.set('categorie', categorie);

    return this.http.get<PaginatedLivres>(`${this.apiUrl}/livres`, { params });
  }

  getLivre(id: number): Observable<Livre> {
    return this.http.get<Livre>(`${this.apiUrl}/livres/${id}`);
  }

  getMesEmprunts(): Observable<Emprunt[]> {
    return this.http.get<Emprunt[]>(`${this.apiUrl}/mes-emprunts`);
  }

  getMesReservations(): Observable<Reservation[]> {
    return this.http.get<Reservation[]>(`${this.apiUrl}/mes-reservations`);
  }

  getAuteurs(): Observable<Auteur[]> {
    return this.http.get<Auteur[]>(`${this.apiUrl}/auteurs`);
  }

  getAuteur(id: number): Observable<Auteur> {
    return this.http.get<Auteur>(`${this.apiUrl}/auteurs/${id}`);
  }

  reserveLivre(id: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/reservations/${id}`, {});
  }

  annulerReservation(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/reservations/${id}`);
  }

  getStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/admin/stats`);
  }

  getMe(): Observable<Utilisateur> {
    return this.http.get<Utilisateur>(`${this.apiUrl}/user/me`);
  }

  updateProfile(data: { adressePostale?: string; numTel?: string }): Observable<Utilisateur> {
    return this.http.put<Utilisateur>(`${this.apiUrl}/user/me`, data);
  }
}