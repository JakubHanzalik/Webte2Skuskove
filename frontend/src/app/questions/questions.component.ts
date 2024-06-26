import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { QuestionsService } from '../services/questions.service';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { QrcodeService } from '../services/qrcode.service';
import { MatIconModule } from '@angular/material/icon';
import { Router, RouterModule, ActivatedRoute } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';

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

interface Question extends Omit<QuestionDTO, 'active'> {
  active: boolean;
  editing?: boolean;
  qrCodeURL?: string;
}

enum QuestionType {
  SINGLE_CHOICE = 0,
  MULTIPLE_CHOICE = 1,
  TEXT = 2,
}

interface RouterState {
  subjectValue: number;
}

@Component({
  selector: 'app-questions',
  templateUrl: './questions.component.html',
  styleUrls: ['./questions.component.css'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    HttpClientModule,
    MatIconModule,
    RouterModule,
    MatCardModule,
    MatTooltipModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatCheckboxModule,
  ],
  providers: [QuestionsService],
})
export class QuestionsComponent implements OnInit {
  activeQuestions: Question[] = [];
  historicalQuestions: Question[] = [];

  newQuestionText: string = '';
  originalQuestionText: string = '';
  newQuestionSubjectId: number = 1;
  newQuestionType: QuestionType = QuestionType.SINGLE_CHOICE;
  newQuestionAnswers: Answer[] = [
    { answer: '', correct: false },
    { answer: '', correct: false },
  ];

  QuestionType = QuestionType; // Make QuestionType accessible in the template

  constructor(
    private cdr: ChangeDetectorRef,
    private questionsService: QuestionsService,
    private qrCodeService: QrcodeService,
    private router: Router,
    private route: ActivatedRoute,
    private http: HttpClient,
    private snackBar: MatSnackBar // Add MatSnackBar to the constructor
  ) {}

  ngOnInit() {
    this.route.paramMap.subscribe((params) => {
      const subjectId = +params.get('subjectId')!;
      console.log('Subject value from route:', subjectId);
      this.newQuestionSubjectId = subjectId;
      this.loadQuestions(subjectId);
    });
  }

  loadQuestions(subjectFilter?: number) {
    console.log('Subject Filter received in loadQuestions:', subjectFilter);
    this.questionsService.getAllQuestions().subscribe({
      next: (res: QuestionDTO[]) => {
        console.log('Received data:', res);

        const filteredQuestions =
          subjectFilter !== undefined
            ? res.filter((q) => q.subjectId === subjectFilter)
            : res;

        console.log('Filtered Questions:', filteredQuestions);

        this.activeQuestions = filteredQuestions.filter((q) => q.active);
        this.historicalQuestions = filteredQuestions.filter((q) => !q.active);

        console.log('Active Questions:', this.activeQuestions);
        console.log('Historical Questions:', this.historicalQuestions);
      },
      error: (err) => console.error('Error fetching questions:', err),
    });
  }

  async showQRCode(question: Question) {
    if (question.question_code) {
      try {
        const qrCodeURL = await this.qrCodeService.generateQR(
          question.question_code
        );
        question.qrCodeURL = qrCodeURL;
        this.snackBar.open('QR kód úspešne zobrazený', 'Close', {
          duration: 3000,
        });
      } catch (error) {
        console.error('Error generating QR code:', error);
      }
    } else {
      console.error('Question code is undefined.');
    }
  }

  addNewQuestion() {
    if (this.newQuestionText.trim()) {
      const newQuestion: QuestionDTO = {
        text: this.newQuestionText,
        active: true,
        answers: this.newQuestionAnswers
          .filter((answer) => answer.answer.trim())
          .map((answer) => ({
            answer: answer.answer,
            correct: answer.correct,
          })),
        subjectId: this.newQuestionSubjectId,
        type: this.newQuestionType,
      };

      console.log('Creating new question with data:', newQuestion);

      this.questionsService.createQuestion(newQuestion).subscribe({
        next: () => {
          console.log('Question created successfully');
          this.snackBar.open('Otázka úspešne vytvorená', 'Close', {
            duration: 3000,
          });
          this.newQuestionText = '';
          this.newQuestionAnswers = [
            { answer: '', correct: false },
            { answer: '', correct: false },
          ];
          this.cdr.detectChanges();
          this.loadQuestions(this.newQuestionSubjectId); // Refresh the question list
        },
        error: (err) => {
          console.error('Error creating question:', err);
        },
      });
    }
  }

  addAnswerField() {
    if (
      this.newQuestionAnswers[this.newQuestionAnswers.length - 1].answer.trim()
    ) {
      this.newQuestionAnswers.push({ answer: '', correct: false });
    }
  }

  copyQuestion(question: Question) {
    const copiedQuestion: Question = { ...question };

    if (copiedQuestion.active) {
      this.activeQuestions.unshift(copiedQuestion);
    } else {
      this.historicalQuestions.unshift(copiedQuestion);
    }
  }

  deleteQuestion(question: Question) {
    if (question.question_code) {
      this.questionsService.deleteQuestion(question.question_code).subscribe({
        next: () => {
          this.updateQuestionsAfterDeletion(question);
          this.snackBar.open('Otázka úspešne odstránená', 'Close', {
            duration: 3000,
          });
        },
        error: (err) => console.error('Error deleting question:', err),
      });
    } else {
      console.error(
        'Attempted to delete a question without a valid question code.'
      );
    }
  }

  updateQuestionsAfterDeletion(question: Question) {
    if (question.active) {
      this.activeQuestions = this.activeQuestions.filter(
        (q) => q.question_code !== question.question_code
      );
    } else {
      this.historicalQuestions = this.historicalQuestions.filter(
        (q) => q.question_code !== question.question_code
      );
    }
  }

  editQuestion(question: Question) {
    if (question.question_code) {
      this.questionsService
        .getQuestionByCode(question.question_code)
        .subscribe({
          next: (fetchedQuestion: Question) => {
            if (fetchedQuestion) {
              this.originalQuestionText = fetchedQuestion.text;
              fetchedQuestion.active = fetchedQuestion.active === true;
              question.editing = true;
            } else {
              console.error('Otázka nebola nájdená.');
            }
          },
          error: (err) => {
            console.error('Chyba pri získavaní otázky:', err);
          },
        });
    } else {
      console.error('Question code is undefined.');
    }
  }

  saveEditedQuestion(question: Question) {
    if (question.question_code) {
      const questionData = {
        text: question.text,
        subjectId: question.subjectId,
        active: question.active,
        answers: question.answers.map((answer) => ({
          id: answer.id,
          answer: answer.answer,
          correct: answer.correct,
        })),
      };

      this.questionsService
        .updateQuestion(question.question_code, questionData)
        .subscribe({
          next: (response) => {
            console.log('Question updated successfully:', response);
            this.snackBar.open('Otázka úspešne upravená', 'Close', {
              duration: 3000,
            });
          },
          error: (error) => {
            console.error('Error updating question:', error);
          },
        });
    } else {
      console.error('Question code is undefined.');
    }
  }

  deactivateQuestion(question: Question) {
    if (question.question_code) {
      this.questionsService.fetchAndToggleActiveStatus(question.question_code, false).subscribe({
        next: (updatedQuestion) => {
          question.active = updatedQuestion.active;
          this.activeQuestions = this.activeQuestions.filter((q) => q !== question);
          this.historicalQuestions.unshift(question);
          this.snackBar.open('Otázka úspešne deaktivovaná', 'Close', {
            duration: 3000,
          });
        },
        error: (err) => {
          console.error('Error deactivating question:', err);
        }
      });
    } else {
      console.error('Question code is undefined.');
    }
  }

  activeQuestion(question: Question) {
    if (question.question_code) {
      this.questionsService.fetchAndToggleActiveStatus(question.question_code, true).subscribe({
        next: (updatedQuestion) => {
          question.active = updatedQuestion.active;
          this.historicalQuestions = this.historicalQuestions.filter((q) => q !== question);
          this.activeQuestions.unshift(question);
          this.snackBar.open('Otázka úspešne aktivovaná', 'Close', {
            duration: 3000,
          });
        },
        error: (err) => {
          console.error('Error activating question:', err);
        }
      });
    } else {
      console.error('Question code is undefined.');
    }
  }

  toggleEditMode(question: Question) {
    question.editing = !question.editing;
    if (question.editing) {
      this.originalQuestionText = question.text;
    }
  }
}
