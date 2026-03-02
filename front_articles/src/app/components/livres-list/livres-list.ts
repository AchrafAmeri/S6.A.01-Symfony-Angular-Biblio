import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { ApiService } from '../../services/api-service';
import { FormsModule } from '@angular/forms';
import { Livre } from '../../models/livre';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-livres-list',
  imports: [RouterLink, FormsModule],
  templateUrl: './livres-list.html',
  styleUrl: './livres-list.css',
})
export class LivresList implements OnInit {
  private apiService = inject(ApiService);
  
  // Données des livres
  livres = signal<Livre[]>([]);

  // Signaux pour la recherche
  searchTitre = signal('');
  searchAuteur = signal('');
  searchCategorie = signal('');
  
  // Signaux pour la pagination
  page = signal(1);
  totalPages = signal(1);

  // Génère dynamiquement le tableau des numéros de page [1, 2, 3...]
  pagesArray = computed(() => 
    Array.from({ length: this.totalPages() }, (_, i) => i + 1)
  );

  ngOnInit() {
    this.loadLivres();
  }

  loadLivres() {
    this.apiService.getLivres(
      this.searchTitre(), 
      this.searchAuteur(), 
      this.searchCategorie(), 
      this.page()
    ).subscribe({
      next: (response) => {
        // On adapte ici car l'API renvoie maintenant { data: Livre[], totalPages: x, ... }
        this.livres.set(response.data); 
        this.totalPages.set(response.totalPages);
      },
      error: (err) => console.error('Erreur lors du chargement des livres', err)
    });
  }

  onSearch() {
    this.page.set(1); // Reset à la page 1 pour une nouvelle recherche
    this.loadLivres();
  }

  // Nouvelle méthode pour aller directement à une page précise
  goToPage(p: number) {
    if (p >= 1 && p <= this.totalPages()) {
      this.page.set(p);
      this.loadLivres();
    }
  }
}