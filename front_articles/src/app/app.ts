import { Component, inject } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive, Router } from '@angular/router';
import { AuthService } from './services/auth-service';
import { environment } from '../environments/environment';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './app.html',
  styleUrl: './app.css',
})
export class App {
  authService = inject(AuthService);
  private router = inject(Router);

  constructor() {
    // On regarde s'il y a un token dans l'URL (envoyé par le pont Symfony)
    const urlParams = new URLSearchParams(window.location.search);
    const ssoToken = urlParams.get('token');
    const action = urlParams.get('action');

    if (ssoToken) {
      // On connecte l'utilisateur silencieusement avec ce nouveau token
      this.authService.handleLoginSuccess(ssoToken);

      // On nettoie l'URL pour cacher le token et faire plus propre visuellement
      window.history.replaceState({}, document.title, window.location.pathname);
    }else if (action === 'logout') {
      this.authService.localLogout(); // On vide le JWT sans faire de redirection
      window.history.replaceState({}, document.title, window.location.pathname); // On nettoie l'URL
    }
  }

  goToAdmin() {
    // Bibliothécaire → Interface EasyAdmin Symfony (gestion quotidienne)
    // Responsable → Peut aussi y accéder (hérite des droits)
    const token = this.authService.getToken();
    if (token) {
      window.location.href = `${environment.ssoUrl}?token=${token}`;
    }
  }

  goToStats() {
    // Responsable uniquement → Dashboard Angular avec statistiques détaillées
    this.router.navigate(['/admin/dashboard']);
  }
}
