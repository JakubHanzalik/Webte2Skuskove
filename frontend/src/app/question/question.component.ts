import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { Injectable } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';

@Injectable({
  providedIn: 'root'
})
@Component({
  selector: 'app-question',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatInputModule,
    MatButtonModule,
    MatFormFieldModule,
    MatCheckboxModule,
    MatRadioModule,
    MatSelectModule,
  ],
  templateUrl: './question.component.html',
  styleUrls: ['./question.component.css']
})
export class QuestionComponent implements OnInit {
  questionId: string | null = null;
  questionData: any;
  isLoggedIn: boolean = false;
  selectedAnswerId: number | null = null;
  newAnswers: any[] = [{ answer: '', correct: false }];
  correctAnswerIds: number[] = [];
  userAnswerIds: number[] = [];
  questionTypes = [
    { value: 0, viewValue: 'Single Choice' },
    { value: 1, viewValue: 'Multiple Choice' },
    { value: 2, viewValue: 'Text' },
  ];
  showResults: boolean = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private http: HttpClient
  ) {}

  ngOnInit(): void {
    this.route.paramMap.subscribe(params => {
      this.questionId = params.get('id');
      if (this.questionId) {
        this.checkQuestionExistence(this.questionId);
      } else {
        this.redirectToError();
      }
    });
  }

  checkQuestionExistence(id: string): void {
    const baseUrl = `${window.location.protocol}//${window.location.hostname}${window.location.port ? ':' + window.location.port : ''}/api`;
    const votingUrl = `${baseUrl}/voting/${id}`;

    this.http.get(votingUrl)
      .pipe(
        catchError(err => {
          console.error('Error checking voting question existence:', err);
          this.redirectToError();
          return of(null);
        })
      )
      .subscribe(response => {
        if (response) {
          this.checkIfUserQuestion(id);
          if (!this.isLoggedIn) {
            this.questionData = response;
          }
        } else {
          this.redirectToError();
        }
      });
  }

  checkIfUserQuestion(id: string): void {
    const baseUrl = `${window.location.protocol}//${window.location.hostname}${window.location.port ? ':' + window.location.port : ''}/api`;
    const questionUrl = `${baseUrl}/question/${id}`;

    this.http.get(questionUrl)
      .pipe(
        catchError(err => {
          if (err.status === 401) {
            this.isLoggedIn = false;
            console.log('Not user\'s question or not logged in');
          } else {
            console.error('Error fetching user question');
          }
          return of(null);
        })
      )
      .subscribe(response => {
        if (response) {
          this.isLoggedIn = true;
          this.questionData = response;
          console.log('User\'s question:', response);
        }
      });
  }

  onNewAnswerChange(answer: any, index: number): void {
    if (answer.answer.trim() !== '' && index === this.newAnswers.length - 1) {
      this.newAnswers.push({ answer: '', correct: false });
    }
  }

  updateQuestion(): void {
    if (this.questionId && this.isLoggedIn) {
      const baseUrl = `${window.location.protocol}//${window.location.hostname}${window.location.port ? ':' + window.location.port : ''}/api`;
      const questionUrl = `${baseUrl}/question/${this.questionId}`;

      // Combine existing and new answers
      const updatedQuestionData = {
        ...this.questionData,
        answers: [...this.questionData.answers, ...this.newAnswers.filter(a => a.answer.trim() !== '')]
      };

      console.log('Updating question with data:', updatedQuestionData);

      this.http.put(questionUrl, updatedQuestionData, { responseType: 'json' })
        .pipe(
          catchError(err => {
            console.error('Error updating question:', err);
            return of(null);
          })
        )
        .subscribe(response => {
          console.log('Question updated:', response);
          // Clear new answers
          this.newAnswers = [{ answer: '', correct: false }];
        });
    }
  }

  voteOnQuestion(): void {
    if (this.questionId && !this.isLoggedIn) {
      const baseUrl = `${window.location.protocol}//${window.location.hostname}${window.location.port ? ':' + window.location.port : ''}/api`;
      const voteUrl = `${baseUrl}/voting/${this.questionId}`;

      let voteData: any;

      if (this.questionData.type === 0) {
        if (this.selectedAnswerId !== null) {
          voteData = { answerIds: [this.selectedAnswerId] };
          this.userAnswerIds = [this.selectedAnswerId];
        } else {
          console.error('Voting failed: No answer selected');
          return;
        }
      } else if (this.questionData.type === 1) {
        const selectedAnswerIds = this.questionData.answers.filter((answer: any) => answer.correct).map((answer: any) => answer.id);
        if (selectedAnswerIds.length > 0) {
          voteData = { answerIds: selectedAnswerIds };
          this.userAnswerIds = selectedAnswerIds;
        } else {
          console.error('Voting failed: No answers selected');
          return;
        }
      } else if (this.questionData.type === 2) {
        if (this.questionData.textAnswer && this.questionData.textAnswer.trim() !== '') {
          voteData = { answerText: this.questionData.textAnswer };
          this.userAnswerIds = [1];  // Assuming 1 for text answer, need actual implementation if needed.
        } else {
          console.error('Voting failed: No text answer provided');
          return;
        }
      }

      console.log('Voting with data:', voteData);

      this.http.post(voteUrl, voteData)
        .pipe(
          catchError(err => {
            console.error('Error voting on question:', err);
            return of(null);
          })
        )
        .subscribe(response => {
          console.log('Voted successfully:', response);
          this.showResults = true;
          this.getCorrectAnswers();
        });
    }
  }

  getCorrectAnswers(): void {
    if (this.questionId) {
      const baseUrl = `${window.location.protocol}//${window.location.hostname}${window.location.port ? ':' + window.location.port : ''}/api`;
      const correctAnswersUrl = `${baseUrl}/voting/${this.questionId}/correct`;

      this.http.get(correctAnswersUrl)
        .pipe(
          catchError(err => {
            console.error('Error fetching correct answers:', err);
            return of(null);
          })
        )
        .subscribe((response: any) => {
          console.log('Correct answers:', response);
          if (response && response.answerIds) {
            this.correctAnswerIds = response.answerIds;
          }
        });
    }
  }

  redirectToError(): void {
    this.router.navigate(['/error404']);
  }

  isCorrectAnswer(answerId: number): boolean {
    return this.correctAnswerIds.includes(answerId);
  }

  isUserAnswerWrong(answerId: number): boolean {
    return this.userAnswerIds.includes(answerId) && !this.correctAnswerIds.includes(answerId);
  }

  isUserAnswerCorrect(answerId: number): boolean {
    return this.userAnswerIds.includes(answerId) && this.correctAnswerIds.includes(answerId);
  }
}
