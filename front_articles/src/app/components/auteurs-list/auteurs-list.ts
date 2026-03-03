import { Component, inject, OnInit, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ApiService } from '../../services/api-service';
import { Auteur } from '../../models/auteur';

@Component({
  selector: 'app-auteurs-list',
  imports: [RouterLink],
  templateUrl: './auteurs-list.html'
})
export class AuteursList implements OnInit {
  private apiService = inject(ApiService);
  auteurs = signal<Auteur[]>([]);
  
  isLoading = signal(true); 

  ngOnInit() {
    this.apiService.getAuteurs().subscribe({
      next: (data) => {
        this.auteurs.set(data);
        this.isLoading.set(false);
      },
      error: (err) => {
        console.error('Erreur lors du chargement des auteurs', err);
        this.isLoading.set(false);
      }
    });
  }
}