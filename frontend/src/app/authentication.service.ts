import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { map } from 'rxjs';

// @Injectable({
//   providedIn: 'root'
// })

export interface LoginCredentials{
  email : string;
  password : string;
}

export interface RegisterCredentials{
  name : string;
  email : string;
  password : string;
}

export class AuthenticationService {

  constructor( private http: HttpClient, private router: Router ) { }

  login( credentials: LoginCredentials ){
    return this.http.post('/api/login',credentials).pipe(
      map(( res:any )=>{
        this.setUserDataToLocalStorage(res);
        console.log('logged in');
      })
    );
  }

  register(  ){
    // to do janka podobne jak login len post bude iny.
  }

  private setUserDataToLocalStorage( res:any ): void{
    localStorage.setItem('loggedin' , 'true');
    localStorage.setItem('name', res.name || 'Unknown');
  }

  isLoggedIn(): boolean{
    return localStorage.getItem('loggedin') == 'true' ? true : false;
  }

  logout(): void{
    localStorage.clear();
  }

}
