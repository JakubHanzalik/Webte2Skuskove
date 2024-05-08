import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { User } from './models/user.model';

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = '/api/user';

  constructor(private http: HttpClient) {}

  getUsers(): Observable<User[]> {
    return this.http.get<User[]>(this.apiUrl).pipe(
      catchError(error => {
        console.error('Error fetching users:', error);
        return throwError(() => new Error('Failed to fetch users'));
      })
    );
  }

  getUserById(id: number): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/${id}`).pipe(
      catchError(error => {
        console.error('Error fetching user:', error);
        return throwError(() => new Error('Failed to fetch user'));
      })
    );
  }

  createUser(user: User): Observable<User> {
    return this.http.put<User>(this.apiUrl, user).pipe(
      catchError(error => {
        console.error('Failed to create user:', error);
        return throwError(() => new Error('Failed to create user'));
      })
    );
  }

  updateUser(id: number, user: User): Observable<User> {
    return this.http.post<User>(`${this.apiUrl}/${id}`, user).pipe(
      catchError(error => {
        console.error('Failed to update user:', error);
        return throwError(() => new Error('Failed to update user'));
      })
    );
  }

  deleteUser(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`).pipe(
      catchError(error => {
        console.error('Failed to delete user:', error);
        return throwError(() => new Error('Failed to delete user'));
      })
    );
  }
}
