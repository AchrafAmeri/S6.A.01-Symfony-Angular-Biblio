import { Component, inject, OnInit, signal } from '@angular/core';
import { AuthService } from '../../services/auth-service';
import { ApiService } from '../../services/api-service';
import { Emprunt } from '../../models/emprunt';
import { Reservation } from '../../models/reservation';
import { DatePipe } from '@angular/common';

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

  ngOnInit() {
    this.refreshData();
  }

  refreshData() {
    if (this.authService.isLoggedIn()) {
      this.apiService.getMesEmprunts().subscribe(data => this.emprunts.set(data));
      this.apiService.getMesReservations().subscribe(data => this.reservations.set(data));
    }
  }

  onAnnuler(id: number) {
    if (confirm('Voulez-vous vraiment annuler cette réservation ?')) {
      this.apiService.annulerReservation(id).subscribe({
        next: () => this.refreshData(), // On rafraîchit la liste après suppression
        error: (err) => console.error(err)
      });
    }
  }
}