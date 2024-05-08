// src/app/models/question.model.ts

export interface QuestionDTO {
  text: string;
  question_code: string,
  active: boolean;
  type: number;
  subjectId: number; 
  authorId: number;
  answers: Answers[];
}

export interface Answers {
  id: number;
  text: string;
  correct: boolean;
}

export interface Question {
  qrCodeURL?: string;
  question_code: string; 
  text: string;
  active: boolean;
  type: number;
  subjectId: number;
  authorId: number;
  answers: Answers[];
  editing?: boolean;  
}

