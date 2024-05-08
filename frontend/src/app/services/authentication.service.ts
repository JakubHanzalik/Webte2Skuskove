import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { map } from 'rxjs/operators';
import { Observable } from 'rxjs';

export interface LoginCredentials {
  username: string;
  password: string;
}

export interface RegisterCredentials {
  username: string;
  password: string;
  name: string;
  surname: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {

  constructor(private http: HttpClient, private router: Router) { }

  login(credentials: LoginCredentials): Observable<any> {
    return this.http.post('/api/login', credentials).pipe(
      map((res: any) => {
        console.log('Logged in' , res);
        return res;
      })
    );
  }

  register(credentials: RegisterCredentials): Observable<any> {
    console.log(credentials);
    return this.http.post('/api/register', credentials).pipe(
      map((res: any) => {
        console.log('Registration done', res);
        return res
      })
    );
  }

  isLoggedIn(): boolean {
    return document.cookie.includes('loggedin=true');
  }

  logout(): void {
    this.http.post('/api/logout', {}).subscribe(() => {
      this.router.navigate(['/login']);
    });
  }
  changePassword(changePassword: ChangePassword): Observable<any> {
    return this.http.post('/api/change-password', changePassword).pipe(
      map((res: any) => {
        console.log('Password changed', res);
        return res;
      })
    );
  }
}
export interface ChangePassword {
  password: string;
}
