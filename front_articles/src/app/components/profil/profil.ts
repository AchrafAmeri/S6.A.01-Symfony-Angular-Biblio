import { Component, inject, OnInit, signal } from '@angular/core';
import { AuthService } from '../../services/auth-service';
import { ApiService } from '../../services/api-service';
import { Emprunt } from '../../models/emprunt';
import { Reservation } from '../../models/reservation';
import { Utilisateur } from '../../models/utilisateur';
import { DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { forkJoin } from 'rxjs';

@Component({
  selector: 'app-profil',
  imports: [DatePipe, FormsModule],
  templateUrl: './profil.html',
  styleUrl: './profil.css'
})
export class Profil implements OnInit {
  authService = inject(AuthService);
  apiService = inject(ApiService);

  emprunts = signal<Emprunt[]>([]);
  reservations = signal<Reservation[]>([]);
  user = signal<Utilisateur | null>(null);

  adressePostale = '';
  numTel = '';
  successMessage = '';
  errorMessageProfil = '';

  isLoading = signal<boolean>(true);

  ngOnInit() {
    this.refreshData();
  }

  refreshData() {
    if (this.authService.isLoggedIn()) {
      this.isLoading.set(true);

      forkJoin({
        empruntsData: this.apiService.getMesEmprunts(),
        reservationsData: this.apiService.getMesReservations(),
        userData: this.apiService.getMe()
      }).subscribe({
        next: (results) => {
          this.emprunts.set(results.empruntsData);
          this.reservations.set(results.reservationsData);
          this.user.set(results.userData);
          this.adressePostale = results.userData.adressePostale || '';
          this.numTel = results.userData.numTel || '';
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

  onSaveProfil() {
    this.successMessage = '';
    this.errorMessageProfil = '';
    this.apiService.updateProfile({
      adressePostale: this.adressePostale,
      numTel: this.numTel
    }).subscribe({
      next: (updatedUser) => {
        this.user.set(updatedUser);
        this.successMessage = 'Profil mis à jour avec succès.';
      },
      error: (err) => {
        console.error('Error updating profile', err);
        this.errorMessageProfil = 'Erreur lors de la mise à jour du profil.';
      }
    });
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