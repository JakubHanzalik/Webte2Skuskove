import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { catchError, tap, map } from 'rxjs/operators';
import { BehaviorSubject, Observable, throwError } from 'rxjs';

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

export interface ChangePassword {
  password: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {
  public loggedInStatus = new BehaviorSubject<boolean>(this.checkInitialLogin());

  isLoggedIn$ = this.loggedInStatus.asObservable();

  constructor(private http: HttpClient, private router: Router) {}

  private checkInitialLogin(): boolean {
    return localStorage.getItem('isLoggedIn') === 'true';
  }

  login(credentials: LoginCredentials): Observable<any> {
    return this.http.post('/api/login', credentials).pipe(
      tap((res: any) => {
        console.log('Logged in:', res);
        localStorage.setItem('isLoggedIn', 'true');
        this.loggedInStatus.next(true);
        this.router.navigate(['/']);  
      }),
      catchError(err => {
        console.error('Login error:', err);
        return throwError(err);
      })
    );
  }
  
  register(credentials: RegisterCredentials): Observable<any> {
    return this.http.post('/api/register', credentials).pipe(
      tap((res: any) => {
        console.log('Registration successful:', res);
        localStorage.setItem('isLoggedIn', 'true'); 
        this.loggedInStatus.next(true);  
        this.router.navigate(['/']);  
      }),
      catchError(err => {
        console.error('Registration error:', err);
        return throwError(err);
      })
    );
  }
  
  
  logout(): Observable<any> {
    return this.http.post('/api/logout', {}).pipe(
      tap(() => {
        localStorage.removeItem('isLoggedIn');
        this.loggedInStatus.next(false);
        this.router.navigate(['/login']);  
      }),
      catchError(err => {
        console.error('Logout error:', err);
        return throwError(err);
      })
    );
  }

  document(): Observable<Blob> {
    return this.http.get('/api/docs', { responseType: 'blob' }).pipe(
      tap(data => {
        console.log('PDF fetched successfully');
      }),
      catchError(err => {
        console.error('Failed to fetch PDF', err);
        return throwError(err);
      })
    );
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
