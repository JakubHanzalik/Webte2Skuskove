import { Component } from '@angular/core';
import { AuthenticationService, RegisterCredentials } from '../authentication.service';
import { RouterModule, RouterOutlet } from '@angular/router';
import { MaterialModule } from '../material';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [
    RouterModule,
    HttpClientModule,
    RouterOutlet,
    MaterialModule,
    FormsModule,
    CommonModule
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

  constructor(private authService: AuthenticationService) {}

  register(): void {
    this.authService.register(this.credentials).subscribe({
      next: () => {
        console.log('Registration successful');
        // Môžete presmerovať na prihlasovaciu stránku alebo na dashboard
      },
      error: (err) => {
        this.errorMessage = 'Registration failed';
        console.error(err);
      }
    });
  }
}
