import { Component } from '@angular/core';
import { AuthenticationService, RegisterCredentials } from '../services/authentication.service';
import { Router, RouterModule, RouterOutlet } from '@angular/router';
import { MaterialModule } from '../material';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [
    RouterModule,
    HttpClientModule,
    RouterOutlet,
    MaterialModule,
    FormsModule,
    CommonModule,
    MatIconModule
  ],
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css'],
  providers: [AuthenticationService]
})
export class RegisterComponent {
  credentials: RegisterCredentials = {
    username: '',
    password: '',
    name: '',
    surname: ''
  };
  errorMessage: string = '';

  constructor(
    private authService: AuthenticationService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {}

  register(): void {
    this.authService.register(this.credentials).subscribe({
      next: () => {
        this.authService.loggedInStatus.next(true);
        console.log('Registration successful');
        this.snackBar.open('Úspešne zaregistrovaný', 'Close', {
          duration: 3000,
        });
        this.router.navigate(['/']); // Presun na hlavnú stránku po úspešnej registrácii
      },
      error: (err) => {
        this.errorMessage = 'Registration failed';
        console.error(err);
      }
    });
  }
}
