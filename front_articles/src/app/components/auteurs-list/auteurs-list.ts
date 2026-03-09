import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api-service';
import { Auteur } from '../../models/auteur';

@Component({
  selector: 'app-auteurs-list',
  imports: [RouterLink, FormsModule],
  templateUrl: './auteurs-list.html'
})
export class AuteursList implements OnInit {
  private apiService = inject(ApiService);

  // Données des auteurs
  auteurs = signal<Auteur[]>([]);

  isLoading = signal(true);

  // Signal pour la recherche
  searchNom = signal('');

  // Signaux pour la pagination
  page = signal(1);
  totalPages = signal(1);

  // Génère dynamiquement le tableau des numéros de page [1, 2, 3...]
  pagesArray = computed(() =>
    Array.from({ length: this.totalPages() }, (_, i) => i + 1)
  );

  ngOnInit() {
    this.loadAuteurs();
  }

  loadAuteurs() {
    this.isLoading.set(true);

    this.apiService.getAuteurs(
      this.searchNom(),
      this.page()
    ).subscribe({
      next: (response: any) => {
        this.auteurs.set(response.data);
        this.totalPages.set(response.totalPages);
        this.isLoading.set(false);
      },
      error: (err) => {
        console.error('Erreur lors du chargement des auteurs', err);
        this.isLoading.set(false);
      }
    });
  }

  onSearch() {
    this.page.set(1); // Reset à la page 1 pour une nouvelle recherche
    this.loadAuteurs();
  }

  // Méthode pour aller directement à une page précise
  goToPage(p: number) {
    if (p >= 1 && p <= this.totalPages()) {
      this.page.set(p);
      this.loadAuteurs();
    }
  }
}