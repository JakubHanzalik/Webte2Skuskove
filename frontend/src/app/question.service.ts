import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class QuestionService {

  constructor(private http: HttpClient) { }

  getQuestionById(id: string): Observable<any> {
    return this.http.get(`/api/questions/${id}`).pipe(
      catchError((error) => {
        // Handling error or invalid ID
        return of(null);
      })
    );
  }
}
