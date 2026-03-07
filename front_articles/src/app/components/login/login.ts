import { Component, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth-service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-login',
  imports: [FormsModule],
  templateUrl: './login.html',
  styleUrl: './login.css',
})
export class Login {
  private authService = inject(AuthService);
  private router = inject(Router);

  email = '';
  password = '';
  errorMessage = '';

  onSubmit() {
    this.errorMessage = '';
    this.authService.login(this.email, this.password).subscribe({
      next: (response) => {
        this.authService.handleLoginSuccess(response.token);

        if (this.authService.isBiblio()) {
          // On passe par le pont SSO en fournissant le token
          window.location.href = `${environment.ssoUrl}?token=${response.token}`;
        } else {
          this.router.navigate(['/']);
        }
      },
      error: () => {
        this.errorMessage = 'Email ou mot de passe incorrect.';
      }
    });
  }
}