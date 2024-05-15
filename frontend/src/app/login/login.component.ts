import { Component } from '@angular/core';
import {
  AuthenticationService,
  LoginCredentials,
} from '../services/authentication.service';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule, RouterOutlet } from '@angular/router';
import { MaterialModule } from '../material';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-login',
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
  templateUrl: './login.component.html',
  styleUrl: './login.component.css',
  providers: [AuthenticationService],
})
export class LoginComponent {
  credentials: LoginCredentials = { username: '', password: '' };
  errorMessage: string = '';

  constructor(private authService: AuthenticationService, private router : Router) {}

  login(): void {
    this.router.navigate(['/']); 
    this.authService.login(this.credentials).subscribe({
      next: () => {
        this.authService.loggedInStatus.next(true);
        console.log('User logged in successfully');
        window.location.reload();
       

      },
      error: (err) => {
        this.errorMessage = 'Failed to login';
        console.error(err);
      },
      
    });
  }
}



