import { Component, Injectable, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { SubjectService } from '../services/subject.service';
import { HttpClientModule } from '@angular/common/http';
import { Subject } from '../models/subject.model'; 
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

  constructor(private subjectService: SubjectService) { }

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
}