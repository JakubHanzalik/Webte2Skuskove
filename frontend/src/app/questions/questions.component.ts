import { Component, ChangeDetectorRef } from '@angular/core';
import { FormsModule } from '@angular/forms';



@Component({
  selector: 'app-questions',
  standalone: true,
  imports: [FormsModule],  // Import FormsModule here
  templateUrl: './questions.component.html',
  styleUrls: ['./questions.component.css']
})
export class QuestionsComponent {
  activeQuestions: any[] = [{ text: 'What is your favorite subject?' }, { text: 'What is the capital of Slovakia?' }];
  historicalQuestions: any[] = [{ text: 'Old Question 1' }, { text: 'Old Question 2' }];
  newQuestionText: string = '';  // For new question input

  constructor(private cdr: ChangeDetectorRef) {}

  addNewQuestion() {
    if (this.newQuestionText && !this.activeQuestions.some(q => q.text === this.newQuestionText)) {
      const newQuestion = { text: this.newQuestionText };
      this.activeQuestions = [...this.activeQuestions, newQuestion]; // Use spread to create a new array
      console.log('New question added:', this.newQuestionText);
      console.log('Updated list of active questions:', this.activeQuestions);
      this.newQuestionText = '';
    } else {
      console.log('No new question text provided');
    }
  }
  
}
