import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { SubjectService } from '../services/subject.service';
import { Subject } from '../models/subject.model'; 
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-subjects',
  templateUrl: './subjects.component.html',
  styleUrls: ['./subjects.component.css'],
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule, MatIconModule],
  providers: [SubjectService],
})
export class SubjectComponent implements OnInit {
  subjects: Subject[] = [];
  newSubjectText: string = '';

  constructor(private subjectService: SubjectService, private router: Router) {}

  ngOnInit() {
    this.loadSubjects();
  }

  loadSubjects() {
    this.subjectService.getSubjects().subscribe({
      next: (data) => {
        this.subjects = data; 
      },
      error: (err) => console.error('Failed to load subjects:', err)
    });
  }

  goToQuestions(subjectId: number) {
    console.log('Navigating to questions with subjectId:', subjectId);  
    this.router.navigate(['/questions'], { state: { subjectValue: subjectId } });
  }
  
 
}
