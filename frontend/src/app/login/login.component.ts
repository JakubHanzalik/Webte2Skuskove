import { Component } from '@angular/core';
import { AuthenticationService, LoginCredentials } from '../authentication.service';
import { FormsModule } from '@angular/forms';
import { RouterModule, RouterOutlet } from '@angular/router';
import { MaterialModule } from '../material';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    RouterModule,
    HttpClientModule,
    RouterOutlet,
    MaterialModule,
    FormsModule,
    CommonModule
  ],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css',
  providers: [ AuthenticationService ]
})
export class LoginComponent {
  credentials: LoginCredentials = { username: '', password: '' };
  errorMessage: string = '';

  constructor(private authService: AuthenticationService) {}

  login(): void {
    this.authService.login(this.credentials).subscribe({
      next: () => {
        console.log('User logged in successfully');
        // Navigate to home or dashboard page
      },
      error: (err) => {
        this.errorMessage = 'Failed to login';
        console.error(err);
      }
    });
  }
}
