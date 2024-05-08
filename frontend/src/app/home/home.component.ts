import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
@Component({
  selector: 'app-home',
  standalone: true,
  imports: [ CommonModule, FormsModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent implements OnInit{
  questionCode!: string;
  ngOnInit(): void {
    throw new Error('Method not implemented.');
  }
  constructor(private router: Router) {}

  submitCode(): void {
    if (this.questionCode) {
      this.router.navigate(['/questions'], { queryParams: { code: this.questionCode } });
    } else {
      console.log('Zadajte kód otázky');
    }
  }
}
