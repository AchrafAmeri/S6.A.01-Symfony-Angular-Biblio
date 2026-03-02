import { Component, inject, OnInit, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ApiService } from '../../services/api-service';
import { Auteur } from '../../models/auteur';

@Component({
  selector: 'app-auteur-detail',
  imports: [RouterLink],
  templateUrl: './auteur-detail.html'
})
export class AuteurDetail implements OnInit {
  private route = inject(ActivatedRoute);
  private apiService = inject(ApiService);
  
  auteur = signal<Auteur | undefined>(undefined);

  ngOnInit() {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    if (id) {
      this.apiService.getAuteur(id).subscribe({
        next: (data) => this.auteur.set(data),
        error: (err) => console.error('Erreur lors du chargement de l\'auteur', err)
      });
    }
  }
}