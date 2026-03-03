import { Component, inject, OnInit, signal } from '@angular/core';
import { AuthService } from '../../services/auth-service';
import { ApiService } from '../../services/api-service';
import { Emprunt } from '../../models/emprunt';
import { Reservation } from '../../models/reservation';
import { DatePipe } from '@angular/common';
import { forkJoin } from 'rxjs';

@Component({
  selector: 'app-profil',
  imports: [DatePipe],
  templateUrl: './profil.html',
  styleUrl: './profil.css'
})
export class Profil implements OnInit {
  authService = inject(AuthService);
  apiService = inject(ApiService);

  emprunts = signal<Emprunt[]>([]);
  reservations = signal<Reservation[]>([]);
  
  isLoading = signal<boolean>(true);

  ngOnInit() {
    this.refreshData();
  }

  refreshData() {
    if (this.authService.isLoggedIn()) {
      this.isLoading.set(true);

      forkJoin({
        empruntsData: this.apiService.getMesEmprunts(),
        reservationsData: this.apiService.getMesReservations()
      }).subscribe({
        next: (results) => {
          this.emprunts.set(results.empruntsData);
          this.reservations.set(results.reservationsData);
          this.isLoading.set(false);
        },
        error: (err) => {
          console.error('Error fetching profile data', err);
          this.isLoading.set(false);
        }
      });
    } else {
        this.isLoading.set(false);
    }
  }

  onAnnuler(id: number) {
    if (confirm('Voulez-vous vraiment annuler cette réservation ?')) {
      this.apiService.annulerReservation(id).subscribe({
        next: () => this.refreshData(),
        error: (err) => console.error(err)
      });
    }
  }
}