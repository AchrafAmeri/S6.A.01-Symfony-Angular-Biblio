import { Component, inject, OnInit, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth-service';
import { ApiService } from '../../services/api-service';
import { Livre } from '../../models/livre';
import { DatePipe } from '@angular/common';

@Component({
  selector: 'app-livre-detail',
  imports: [RouterLink, DatePipe],
  templateUrl: './livre-detail.html',
  styleUrl: './livre-detail.css'
})
export class LivreDetail implements OnInit {
  private route = inject(ActivatedRoute);
  private apiService = inject(ApiService);
  authService = inject(AuthService);

  livre = signal<Livre | undefined>(undefined);
  message = signal<{ text: string, type: string } | null>(null);
  
  reserveParMoi = signal<boolean>(false);

  ngOnInit() {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    
    if (id) {
      this.apiService.getLivre(id).subscribe({
        next: (data) => {
          this.livre.set(data);
        },
        error: (err) => console.error('Erreur lors du chargement du livre', err)
      });
    }
  }

  onReserve() {
    if (!this.livre()) return;

    this.apiService.reserveLivre(this.livre()!.id).subscribe({
      next: (res) => {
        this.message.set({ text: res.message, type: 'success' });
        
        this.livre.update(l => l ? { ...l, isReserve: true } : l);
        
        this.reserveParMoi.set(true);
      },
      error: (err) => {
        const errorMsg = err.error?.message || 'Une erreur est survenue.';
        this.message.set({ text: errorMsg, type: 'danger' });
      }
    });
  }
}