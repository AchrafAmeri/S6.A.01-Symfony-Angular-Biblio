import { Component, inject, signal } from '@angular/core';
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
  
  errorMessage = signal(''); 

  onSubmit() {
    this.errorMessage.set(''); 
    
    this.authService.login(this.email, this.password).subscribe({
      next: (response) => {
        this.authService.handleLoginSuccess(response.token);

        if (this.authService.isBiblio()) {
          window.location.href = `${environment.ssoUrl}?token=${response.token}`;
        } else {
          this.router.navigate(['/']);
        }
      },
      error: () => {
        this.errorMessage.set('Email ou mot de passe incorrect.');
      }
    });
  }
}