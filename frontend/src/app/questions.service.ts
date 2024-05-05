import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class QuestionsService {
  private apiUrl = '/api/question';  

  constructor(private http: HttpClient) { }

  createQuestion(question: { text: string; active: boolean }): Observable<any> {
    return this.http.post(this.apiUrl, question);
  }

  
}

