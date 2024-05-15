import { Component } from '@angular/core';
import { AuthenticationService } from '../services/authentication.service';

@Component({
  selector: 'app-tutorial',
  standalone: true,
  imports: [],
  providers : [AuthenticationService],
  templateUrl: './tutorial.component.html',
  styleUrl: './tutorial.component.css'
})
export class TutorialComponent {

  constructor( private authService: AuthenticationService){

  }
  exportDocument(): void {
    this.authService.document().subscribe({
      next: (blob) => {
        this.downloadFile(blob, "downloaded.pdf");
      },
      error: (error) => {
        console.error('Error downloading the file.', error);
      }
    });
  }

  private downloadFile(data: Blob, filename: string): void {
    const blob = new Blob([data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();

    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  }
}
