import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatIconModule } from '@angular/material/icon';
import { Router } from '@angular/router';
@Component({
  selector: 'app-home',
  standalone: true,
  imports: [ CommonModule, FormsModule, MatIconModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent{
  questionCode!: string;

  constructor(private router: Router) {}

  submitCode(): void {
    if (this.questionCode) {
      // Correct the navigate method to use an array with dynamic segment
      this.router.navigate(['/', this.questionCode]);
    } else {
      console.log('Zadajte kód otázky');
    }
  }
}
