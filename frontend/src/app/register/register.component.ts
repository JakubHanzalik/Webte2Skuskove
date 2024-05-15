import { Component } from '@angular/core';
import { AuthenticationService, RegisterCredentials } from '../services/authentication.service';
import { Router, RouterModule, RouterOutlet } from '@angular/router';
import { MaterialModule } from '../material';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { MatIconModule } from '@angular/material/icon';

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
  styleUrl: './register.component.css',
  providers: [ AuthenticationService ]
})
export class RegisterComponent {
  credentials: RegisterCredentials = {
    username: '',
    password: '',
    name: '',
    surname: ''
  };
  errorMessage: string = '';

  constructor(private authService: AuthenticationService, private router : Router) {}

  register(): void {
    this.router.navigate(['/']); 
    this.authService.register(this.credentials).subscribe({
      next: () => {
        this.authService.loggedInStatus.next(true);
        console.log('Registration successful');
        window.location.reload();
      },
      error: (err) => {
        this.errorMessage = 'Registration failed';
        console.error(err);
      }
    });
  }
}
