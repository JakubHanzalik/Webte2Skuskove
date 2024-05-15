import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

interface Answer {
  id?: number;
  answer: string;
  correct: boolean;
}

interface QuestionDTO {
  question_code?: string;
  text: string;
  active: boolean;
  subjectId: number;
  type: number;
  authorId?: number;
  answers: Answer[];
}

@Injectable({
  providedIn: 'root'
})
export class QuestionsService {
  private apiUrl = '/api/question';

  constructor(private http: HttpClient) {}

  getAllQuestions(): Observable<QuestionDTO[]> {
    return this.http.get<QuestionDTO[]>(this.apiUrl).pipe(
      map((questions: any[]) => questions.map(question => ({
        question_code: question.code,
        text: question.text,
        active: question.active,
        subjectId: question.subjectId,
        authorId: question.authorId,
        type: question.type,
        answers: question.answers ? question.answers.map((answer: any) => ({
          id: answer.id,
          answer: answer.text,
          correct: answer.correct
        })) : []
      }))),
      catchError(error => {
        console.error('Error fetching questions:', error);
        return throwError(() => new Error('Failed to fetch questions'));
      })
    );
  }

  getQuestionByCode(code: string): Observable<QuestionDTO> {
    return this.http.get<QuestionDTO>(`${this.apiUrl}/${code}`).pipe(
      map((question: any) => ({
        question_code: question.code,
        text: question.text,
        active: question.active,
        subjectId: question.subjectId,
        authorId: question.authorId,
        type: question.type,
        answers: question.answers ? question.answers.map((answer: any) => ({
          id: answer.id,
          answer: answer.text,
          correct: answer.correct
        })) : []
      })),
      catchError(error => {
        console.error('Error fetching question:', error);
        return throwError(() => new Error('Failed to fetch question'));
      })
    );
  }

  createQuestion(question: QuestionDTO): Observable<QuestionDTO> {
    console.log('Sending request to create question with data:', question);
    return this.http.post<QuestionDTO>(this.apiUrl, question, { withCredentials: true }).pipe(
      catchError(error => {
        console.error('Failed to create question:', error);
        return throwError(() => new Error('Failed to create question'));
      })
    );
  }

  updateQuestion(code: string, questionData: any): Observable<any> {
    const url = `${this.apiUrl}/${code}`;
    console.log('Sending request to update question with data:', questionData);
    return this.http.put(url, questionData, { withCredentials: true }).pipe(
      catchError(error => {
        console.error('Failed to update question:', error);
        return throwError(() => new Error('Failed to update question'));
      })
    );
  }

  deleteQuestion(questionCode: string): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${questionCode}`).pipe(
      catchError((error) => {
        console.error('Failed to delete question due to:', error);
        return throwError(() => new Error('Failed to delete question'));
      })
    );
  }
}
