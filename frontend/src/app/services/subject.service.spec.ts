import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SubjectService {
  private apiUrl = '/api/subject';  

  constructor(private http: HttpClient) { }

  getSubjects(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }
}
