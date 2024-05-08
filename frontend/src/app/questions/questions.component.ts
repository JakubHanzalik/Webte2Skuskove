import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { QuestionsService } from '../services/questions.service';
import { QuestionDTO, Question } from '../models/question.model';
import { HttpClientModule } from '@angular/common/http';

import { Subject } from 'rxjs';
import { QrcodeService } from '../services/qrcode.service';
@Component({
  selector: 'app-questions',
  templateUrl: './questions.component.html',
  styleUrls: ['./questions.component.css'],
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule],
  providers: [QuestionsService],
})
export class QuestionsComponent implements OnInit {
 

  activeQuestions: Question[] = [];
  historicalQuestions: Question[] = [];

  newQuestionText: string = '';
  originalQuestionText: string = '';
  constructor(
    private cdr: ChangeDetectorRef,
    private questionsService: QuestionsService,
    private qrCodeService: QrcodeService
  ) {}

  ngOnInit() {
    this.questionsService.getAllQuestions().subscribe({
        next: (res: QuestionDTO[]) => {
            console.log('Fetched questions:', res);  
            res.forEach((x) => {
                console.log('Processing question:', x); 
                const question: Question = {
                  active: x.active,
                  subjectId: x.subjectId,
                  editing: false,
                  text: x.text,
                  type: x.type,
                  authorId: x.authorId,
                  answers: x.answers,
                  question_code: x.question_code,
                  
                };

                console.log('Processed question to add:', question);  

                if (x.active) {
                    this.activeQuestions.push(question);
                } else {
                    this.historicalQuestions.push(question);
                }
            });
        },
        error: err => console.error('Error fetching questions:', err)
    });
}
async showQRCode(question: Question) {
  try {
    const qrCodeURL = await this.qrCodeService.generateQR(question.question_code);
    question.qrCodeURL = qrCodeURL;
  } catch (error) {
    console.error("Error generating QR code:", error);
  }
}
/*
qrCode(question: Question){
    this.qrCodeService.genetatorQR(question.question_code).then(url =>{
    console.log(url);
  });
}
*/
addNewQuestion() {
  if (this.newQuestionText.trim()) {
    const newQuestion: QuestionDTO = {
      text: this.newQuestionText,
      active: true,
      question_code: "dfvvsd",
      answers: [
        {
          id: 0,
          text: 'aaasss',
          correct: false,
        },
        {
          id: 1,
          text: 'ssssss',
          correct: true,
        },
      ],
      authorId: 2,
      subjectId: 1,
      type: 0,
    };

    this.questionsService.createQuestion(newQuestion).subscribe({
      next: () => {
        console.log('Question created successfully');
       
        this.newQuestionText = ''; 
      },
      error: (err) => {
        console.error('Error creating question:', err);
      },
    });
  
      this.cdr.detectChanges();
    }
  }

  copyQuestion(question: Question) {
    const copiedQuestion: Question = { ...question };

    //const newQuestionCode = this.generateQuestionCode();

    /*     copiedQuestion.question_code = newQuestionCode; */
    if (copiedQuestion.active) {
      this.activeQuestions.unshift(copiedQuestion);
    } else {
      this.historicalQuestions.unshift(copiedQuestion);
    }
  }
  deleteQuestion(question: Question) {
  console.log('Attempting to delete question:', question);

  if (!question.question_code) {
    console.error('Attempted to delete a question without a valid question code.');
    return;
  }

  this.questionsService.deleteQuestion(question.question_code).subscribe({
      next: () => {
          console.log('Deleted question:', question);
          this.updateQuestionsAfterDeletion(question);
      },
      error: err => console.error('Error deleting question:', err)
  });
}


updateQuestionsAfterDeletion(question: Question) {
    if (question.active) {
        this.activeQuestions = this.activeQuestions.filter(q => q.question_code !== question.question_code);
    } else {
        this.historicalQuestions = this.historicalQuestions.filter(q => q.question_code !== question.question_code);
    }
}
editQuestion(question: Question) {
  this.questionsService.getQuestionByCode(question.question_code).subscribe({
    next: (fetchedQuestion: Question) => {
      if (fetchedQuestion) {
        this.originalQuestionText = fetchedQuestion.text;
        fetchedQuestion.active = fetchedQuestion.active === true;
        question.editing = true;
      } else {
        console.error('Otázka nebyla nalezena.');
      }
    },
    error: (err) => {
      console.error('Chyba při získávání otázky:', err);
    }
  });
}
saveEditedQuestion(question: Question) {
  const questionData = {
    text: question.text,
    subjectId: question.subjectId,
    active: question.active === false, 
    answers: question.answers.map(answer => {
      return {id: answer.id, text: answer.text, correct: answer.correct};
    })
  };

  this.questionsService.updateQuestion(question.question_code, questionData).subscribe({
    next: (response) => {
      console.log('Question updated successfully:', response);
    },
    error: (error) => {
      console.error('Error updating question:', error);
    }
  });
}


  deactivateQuestion(question: Question) {
    question.active = false;
    this.activeQuestions = this.activeQuestions.filter((q) => q !== question);
    this.historicalQuestions.unshift(question);
    this.questionsService.updateQuestion(question.question_code, { active: false }).subscribe({
      next: () => {
        console.log('Question deactivated successfully');
      },
      error: (err) => {
        console.error(err);
      },
    });
  }
  
  activeQuestion(question: Question) {
    question.active = true;
    this.historicalQuestions = this.historicalQuestions.filter((q) => q !== question);
    this.activeQuestions.unshift(question);
    this.questionsService.updateQuestion(question.question_code, { active: true }).subscribe({
      next: () => {
        console.log('Question activated successfully');
      },
      error: (err) => {
        console.error(err);
      },
    });
  }
  

  toggleEditMode(question: Question) {
    question.editing = !question.editing;
    if (question.editing) {
      this.originalQuestionText = question.text;
    }
  }
}
